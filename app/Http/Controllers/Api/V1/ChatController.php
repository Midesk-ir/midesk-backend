<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ChatChannel;
use App\Models\ChatChannelMember;
use App\Models\ChatMessage;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(private ChatService $chatService) {}

    public function channels(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->chatService->listChannelsForUser($request->user())]);
    }

    public function storeChannel(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => 'required|in:group,channel',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'member_ids' => 'array',
            'member_ids.*' => 'uuid',
        ]);

        $channel = $this->chatService->createChannel($request->user(), $data);

        return response()->json($channel, 201);
    }

    public function dm(Request $request): JsonResponse
    {
        $data = $request->validate(['user_id' => 'required|uuid']);
        $channel = $this->chatService->findOrCreateDm($request->user(), $data['user_id']);

        return response()->json($channel);
    }

    public function showChannel(ChatChannel $channel): JsonResponse
    {
        return response()->json($channel->load(['members.user', 'creator']));
    }

    public function updateChannel(Request $request, ChatChannel $channel): JsonResponse
    {
        $channel->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'avatar_url' => 'nullable|string',
        ]));

        return response()->json($channel->fresh());
    }

    public function archiveChannel(ChatChannel $channel): JsonResponse
    {
        $channel->update(['is_archived' => true]);

        return response()->json($channel);
    }

    public function members(ChatChannel $channel): JsonResponse
    {
        return response()->json(['data' => $channel->members()->with('user')->get()]);
    }

    public function addMember(Request $request, ChatChannel $channel): JsonResponse
    {
        $data = $request->validate(['user_id' => 'required|uuid']);
        $member = ChatChannelMember::create([
            'channel_id' => $channel->id,
            'user_id' => $data['user_id'],
            'role' => 'member',
            'joined_at' => now(),
        ]);

        return response()->json($member->load('user'), 201);
    }

    public function removeMember(ChatChannel $channel, string $userId): JsonResponse
    {
        ChatChannelMember::query()->where('channel_id', $channel->id)->where('user_id', $userId)->delete();

        return response()->json(['message' => 'Member removed']);
    }

    public function mute(Request $request, ChatChannel $channel): JsonResponse
    {
        $data = $request->validate(['until' => 'required|date']);
        ChatChannelMember::query()
            ->where('channel_id', $channel->id)
            ->where('user_id', $request->user()->id)
            ->update(['muted_until' => $data['until']]);

        return response()->json(['message' => 'Channel muted']);
    }

    public function messages(Request $request, ChatChannel $channel): JsonResponse
    {
        $messages = $this->chatService->getMessages(
            $channel,
            $request->query('before'),
            (int) $request->query('limit', 50)
        );

        return response()->json(['data' => $messages]);
    }

    public function sendMessage(Request $request, ChatChannel $channel): JsonResponse
    {
        $data = $request->validate([
            'body' => 'nullable|string',
            'type' => 'in:text,file,image,system',
            'reply_to_id' => 'nullable|uuid',
        ]);

        $message = $this->chatService->sendMessage($channel, $request->user(), $data);

        return response()->json($message->load(['sender', 'replyTo.sender', 'reactions']), 201);
    }

    public function updateMessage(Request $request, ChatMessage $message): JsonResponse
    {
        if ($message->sender_user_id !== $request->user()->id) {
            abort(403);
        }
        if ($message->created_at->lt(now()->subMinutes(15))) {
            abort(422, 'Edit window expired');
        }

        $message->update([
            'body' => $request->validate(['body' => 'required|string'])['body'],
            'edited_at' => now(),
        ]);

        return response()->json($message);
    }

    public function deleteMessage(Request $request, ChatMessage $message): JsonResponse
    {
        if ($message->sender_user_id !== $request->user()->id) {
            abort(403);
        }
        $message->update(['deleted_at' => now(), 'body' => null]);

        return response()->json(['message' => 'Message deleted']);
    }

    public function react(Request $request, ChatMessage $message): JsonResponse
    {
        $data = $request->validate(['emoji' => 'required|string|max:10']);
        $reaction = $this->chatService->addReaction($message, $request->user(), $data['emoji']);

        return response()->json($reaction, 201);
    }

    public function markRead(Request $request, ChatChannel $channel): JsonResponse
    {
        $data = $request->validate(['last_read_message_id' => 'required|uuid']);
        $this->chatService->markRead($channel, $request->user(), $data['last_read_message_id']);

        return response()->json(['message' => 'Marked as read']);
    }

    public function uploadFile(Request $request, ChatMessage $message): JsonResponse
    {
        $request->validate(['file' => 'required|file|max:25600']);
        $channel = $message->channel;
        $uploaded = $this->chatService->uploadFile($channel, $request->user(), $request->file('file'));

        return response()->json($uploaded, 201);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->chatService->unreadCounts($request->user())]);
    }
}
