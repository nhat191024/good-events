<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\PartnerBill;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function events(): JsonResponse
    {
        $user = Auth::user();

        $bills = PartnerBill::where('partner_id', $user->id)
            ->with(['client', 'category'])
            ->get();

        $events = $bills->map(function ($bill) {
            $baseDate = $bill->date;

            if ($bill->start_time) {
                $startDateTime = $baseDate->copy()
                    ->setHour($bill->start_time->hour)
                    ->setMinute($bill->start_time->minute)
                    ->setSecond($bill->start_time->second);
            } else {
                $startDateTime = $baseDate->copy()->startOfDay();
            }

            if ($bill->end_time) {
                $endDateTime = $baseDate->copy()
                    ->setHour($bill->end_time->hour)
                    ->setMinute($bill->end_time->minute)
                    ->setSecond($bill->end_time->second);
            } else {
                $endDateTime = $startDateTime->copy()->addHour();
            }

            return [
                'id' => $bill->id,
                'title' => "#{$bill->code} - {$bill->client?->name}",
                'start' => $startDateTime->format('Y-m-d\TH:i:s'),
                'end' => $endDateTime->format('Y-m-d\TH:i:s'),
                'backgroundColor' => $this->getEventColor($bill->status),
                'borderColor' => $this->getEventColor($bill->status),
                'extendedProps' => [
                    'code' => $bill->code,
                    'client' => $bill->client?->name,
                    'category' => $bill->category?->name,
                    'address' => $bill->address,
                    'phone' => $bill->phone,
                    'total' => $bill->final_total,
                    'status' => $this->getStatusLabel($bill->status),
                    'status_raw' => $bill->status->value,
                    'note' => $bill->note,
                    'raw_date' => $bill->date->toDateString(),
                    'raw_start_time' => $bill->start_time?->toTimeString(),
                    'raw_end_time' => $bill->end_time?->toTimeString(),
                ],
            ];
        });

        return response()->json($events);
    }

    public function locale(): JsonResponse
    {
        $locale = app()->getLocale();
        $translations = __('partner/calendar');

        return response()->json([
            'locale' => $locale,
            'translations' => $translations,
        ]);
    }

    private function getEventColor($status): string
    {
        return match ($status->value) {
            'pending' => '#f59e0b',
            'completed' => '#10b981',
            'confirmed' => '#FFC000',
            'cancelled' => '#ef4444',
            default => '#6b7280',
        };
    }

    private function getStatusLabel($status): string
    {
        return match ($status->value) {
            'pending' => __('partner/calendar.status_pending'),
            'completed' => __('partner/calendar.status_completed'),
            'confirmed' => __('partner/calendar.status_confirmed'),
            'cancelled' => __('partner/calendar.status_cancelled'),
            'expired' => __('partner/calendar.status_expired'),
            default => __('partner/calendar.status_unknown'),
        };
    }
}
