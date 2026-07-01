<?php

namespace App\Services;

use App\Enums\ChatChannelType;
use App\Enums\ChatMemberRole;
use App\Enums\ChatMessageType;
use App\Enums\MeetingAttendeeStatus;
use App\Enums\MeetingProvider;
use App\Enums\MeetingStatus;
use App\Events\ChatMessageSent;
use App\Models\ChatChannel;
use App\Models\ChatChannelMember;
use App\Models\ChatMessage;
use App\Models\ChatReaction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ChatService
{
    public function listChannelsForUser(User $user)
    {
        return ChatChannel::query()
            ->whereHas('members', fn ($q) => $q->where('user_id', $user->id))
            ->where('is_archived', false)
            ->with(['members.user', 'creator'])
            ->orderByDesc('last_message_at')
            ->get();
    }

    public function createChannel(User $user, array $data): ChatChannel
    {
        return DB::transaction(function () use ($user, $data) {
            $type = ChatChannelType::from($data['type']);
            $channel = ChatChannel::create([
                'name' => $data['name'] ?? null,
                'type' => $type,
                'description' => $data['description'] ?? null,
                'created_by_user_id' => $user->id,
            ]);

            $memberIds = array_unique(array_merge([$user->id], $data['member_ids'] ?? []));
            if ($type === ChatChannelType::Group && count($memberIds) < 2) {
                throw new HttpException(422, 'Group requires at least 2 members');
            }

            foreach ($memberIds as $index => $memberId) {
                ChatChannelMember::create([
                    'channel_id' => $channel->id,
                    'user_id' => $memberId,
                    'role' => $index === 0 ? ChatMemberRole::Owner : ChatMemberRole::Member,
                    'joined_at' => now(),
                ]);
            }

            return $channel->load(['members.user', 'creator']);
        });
    }

    public function findOrCreateDm(User $user, string $otherUserId): ChatChannel
    {
        $existing = ChatChannel::query()
            ->where('type', ChatChannelType::Dm)
            ->whereHas('members', fn ($q) => $q->where('user_id', $user->id))
            ->whereHas('members', fn ($q) => $q->where('user_id', $otherUserId))
            ->first();

        if ($existing) {
            return $existing->load(['members.user']);
        }

        return $this->createChannel($user, [
            'type' => ChatChannelType::Dm->value,
            'member_ids' => [$otherUserId],
        ]);
    }

    public function sendMessage(ChatChannel $channel, User $user, array $data): ChatMessage
    {
        $this->ensureCanSend($channel, $user);

        $message = ChatMessage::create([
            'channel_id' => $channel->id,
            'sender_user_id' => $user->id,
            'body' => $data['body'] ?? null,
            'type' => ChatMessageType::from($data['type'] ?? 'text'),
            'file_path' => $data['file_path'] ?? null,
            'file_name' => $data['file_name'] ?? null,
            'file_size' => $data['file_size'] ?? null,
            'reply_to_id' => $data['reply_to_id'] ?? null,
            'created_at' => now(),
        ]);

        $channel->update(['last_message_at' => now()]);
        event(new ChatMessageSent($message->load(['sender', 'replyTo.sender', 'reactions'])));

        return $message;
    }

    public function getMessages(ChatChannel $channel, ?string $before = null, int $limit = 50)
    {
        $query = ChatMessage::query()
            ->where('channel_id', $channel->id)
            ->with(['sender', 'replyTo.sender', 'reactions'])
            ->orderByDesc('created_at')
            ->limit($limit);

        if ($before) {
            $beforeMessage = ChatMessage::findOrFail($before);
            $query->where('created_at', '<', $beforeMessage->created_at);
        }

        return $query->get()->reverse()->values();
    }

    public function unreadCounts(User $user): array
    {
        $memberships = ChatChannelMember::query()->where('user_id', $user->id)->get();
        $counts = [];

        foreach ($memberships as $membership) {
            $query = ChatMessage::query()->where('channel_id', $membership->channel_id);
            if ($membership->last_read_message_id) {
                $lastRead = ChatMessage::find($membership->last_read_message_id);
                if ($lastRead) {
                    $query->where('created_at', '>', $lastRead->created_at);
                }
            }
            $counts[$membership->channel_id] = $query->count();
        }

        return $counts;
    }

    public function markRead(ChatChannel $channel, User $user, string $lastReadMessageId): void
    {
        ChatChannelMember::query()
            ->where('channel_id', $channel->id)
            ->where('user_id', $user->id)
            ->update(['last_read_message_id' => $lastReadMessageId]);
    }

    public function addReaction(ChatMessage $message, User $user, string $emoji): ChatReaction
    {
        return ChatReaction::updateOrCreate(
            ['message_id' => $message->id, 'user_id' => $user->id, 'emoji' => $emoji],
            ['created_at' => now()]
        );
    }

    public function uploadFile(ChatChannel $channel, User $user, UploadedFile $file): ChatMessage
    {
        $this->ensureCanSend($channel, $user);
        $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'zip'];
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, $allowed, true)) {
            throw new HttpException(422, 'File type not allowed');
        }
        if ($file->getSize() > 25 * 1024 * 1024) {
            throw new HttpException(422, 'File exceeds 25MB limit');
        }

        $path = $file->store('chat/'.app('tenant')->id, 'local');

        return $this->sendMessage($channel, $user, [
            'type' => in_array($ext, ['jpg', 'jpeg', 'png'], true) ? 'image' : 'file',
            'body' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ]);
    }

    private function ensureCanSend(ChatChannel $channel, User $user): void
    {
        $member = ChatChannelMember::query()
            ->where('channel_id', $channel->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $member) {
            throw new HttpException(403, 'Not a channel member');
        }

        if ($channel->type === ChatChannelType::Channel
            && ! in_array($member->role, [ChatMemberRole::Owner, ChatMemberRole::Admin], true)) {
            throw new HttpException(403, 'Only admins can post in channels');
        }
    }
}
