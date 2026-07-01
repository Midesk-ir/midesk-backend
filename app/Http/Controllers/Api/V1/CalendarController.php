<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Calendar\StoreCalendarEventRequest;
use App\Http\Requests\Calendar\StoreHolidayRequest;
use App\Http\Requests\Calendar\StoreShiftRequest;
use App\Http\Requests\Calendar\UpdateCalendarEventRequest;
use App\Http\Requests\Calendar\UpdateHolidayRequest;
use App\Http\Requests\Calendar\UpdateShiftRequest;
use App\Http\Resources\CalendarEventResource;
use App\Http\Resources\HolidayResource;
use App\Http\Resources\ShiftResource;
use App\Models\CalendarEvent;
use App\Models\Holiday;
use App\Models\Shift;
use App\Services\CalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __construct(
        private readonly CalendarService $calendarService,
    ) {}

    public function events(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CalendarEvent::class);

        $events = $this->calendarService->listEvents([
            'from' => $request->query('from'),
            'to' => $request->query('to'),
        ]);

        return response()->json(['data' => CalendarEventResource::collection($events)]);
    }

    public function storeEvent(StoreCalendarEventRequest $request): JsonResponse
    {
        $this->authorize('create', CalendarEvent::class);

        $event = $this->calendarService->createEvent($request->user(), $request->validated());

        return response()->json(new CalendarEventResource($event->load('createdBy')), 201);
    }

    public function updateEvent(UpdateCalendarEventRequest $request, CalendarEvent $event): JsonResponse
    {
        $this->authorize('update', $event);

        $event = $this->calendarService->updateEvent($event, $request->validated());

        return response()->json(new CalendarEventResource($event->load('createdBy')));
    }

    public function destroyEvent(CalendarEvent $event): JsonResponse
    {
        $this->authorize('delete', $event);

        $this->calendarService->deleteEvent($event);

        return response()->json(null, 204);
    }

    public function holidays(): JsonResponse
    {
        $this->authorize('viewAny', Holiday::class);

        return response()->json([
            'data' => HolidayResource::collection($this->calendarService->listHolidays()),
        ]);
    }

    public function storeHoliday(StoreHolidayRequest $request): JsonResponse
    {
        $this->authorize('create', Holiday::class);

        $holiday = $this->calendarService->createHoliday([
            'tenant_id' => app('tenant')->id,
            ...$request->validated(),
        ]);

        return response()->json(new HolidayResource($holiday), 201);
    }

    public function updateHoliday(UpdateHolidayRequest $request, Holiday $holiday): JsonResponse
    {
        $this->authorize('update', $holiday);

        $holiday = $this->calendarService->updateHoliday($holiday, $request->validated());

        return response()->json(new HolidayResource($holiday));
    }

    public function destroyHoliday(Holiday $holiday): JsonResponse
    {
        $this->authorize('delete', $holiday);

        $this->calendarService->deleteHoliday($holiday);

        return response()->json(null, 204);
    }

    public function shifts(): JsonResponse
    {
        $this->authorize('viewAny', Shift::class);

        return response()->json([
            'data' => ShiftResource::collection($this->calendarService->listShifts()),
        ]);
    }

    public function storeShift(StoreShiftRequest $request): JsonResponse
    {
        $this->authorize('create', Shift::class);

        $shift = $this->calendarService->createShift([
            'tenant_id' => app('tenant')->id,
            ...$request->validated(),
        ]);

        return response()->json(new ShiftResource($shift), 201);
    }

    public function updateShift(UpdateShiftRequest $request, Shift $shift): JsonResponse
    {
        $this->authorize('update', $shift);

        $shift = $this->calendarService->updateShift($shift, $request->validated());

        return response()->json(new ShiftResource($shift));
    }

    public function destroyShift(Shift $shift): JsonResponse
    {
        $this->authorize('delete', $shift);

        $this->calendarService->deleteShift($shift);

        return response()->json(null, 204);
    }
}
