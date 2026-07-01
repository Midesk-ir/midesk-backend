<?php

namespace App\Services;

use App\Models\CalendarEvent;
use App\Models\Holiday;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Collection;

class CalendarService
{
    public function createEvent(User $user, array $data): CalendarEvent
    {
        return CalendarEvent::create([
            'tenant_id' => app('tenant')->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'all_day' => $data['all_day'] ?? false,
            'color' => $data['color'] ?? null,
            'recurrence_rule' => $data['recurrence_rule'] ?? null,
            'created_by_user_id' => $user->id,
        ]);
    }

    public function updateEvent(CalendarEvent $event, array $data): CalendarEvent
    {
        $event->update($data);

        return $event->fresh();
    }

    public function deleteEvent(CalendarEvent $event): void
    {
        $event->delete();
    }

    /** @return Collection<int, CalendarEvent> */
    public function listEvents(array $filters = []): Collection
    {
        $query = CalendarEvent::query()->with('createdBy');

        if (! empty($filters['from'])) {
            $query->where('end_at', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('start_at', '<=', $filters['to']);
        }

        return $query->orderBy('start_at')->get();
    }

    public function createHoliday(array $data): Holiday
    {
        return Holiday::create($data);
    }

    public function updateHoliday(Holiday $holiday, array $data): Holiday
    {
        $holiday->update($data);

        return $holiday->fresh();
    }

    public function deleteHoliday(Holiday $holiday): void
    {
        $holiday->delete();
    }

    /** @return Collection<int, Holiday> */
    public function listHolidays(): Collection
    {
        return Holiday::query()->orderBy('date')->get();
    }

    /** @return Collection<int, Holiday> */
    public function upcomingHolidays(int $limit = 5): Collection
    {
        $today = now()->toDateString();

        return Holiday::query()
            ->where(function ($q) use ($today) {
                $q->where('date', '>=', $today)
                    ->orWhere('recurring_yearly', true);
            })
            ->orderBy('date')
            ->limit($limit)
            ->get();
    }

    public function createShift(array $data): Shift
    {
        if (! empty($data['is_default'])) {
            Shift::query()->update(['is_default' => false]);
        }

        return Shift::create($data);
    }

    public function updateShift(Shift $shift, array $data): Shift
    {
        if (! empty($data['is_default'])) {
            Shift::query()->where('id', '!=', $shift->id)->update(['is_default' => false]);
        }

        $shift->update($data);

        return $shift->fresh();
    }

    public function deleteShift(Shift $shift): void
    {
        $shift->delete();
    }

    /** @return Collection<int, Shift> */
    public function listShifts(): Collection
    {
        return Shift::query()->orderBy('name')->get();
    }
}
