<?php

namespace App\Filament\Partner\Pages;

use App\Models\PartnerBill;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CalendarPage extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';

    protected string $view = 'filament.partner.pages.calendar-page';

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('partner/calendar.navigation_label');
    }

    public function getTitle(): string
    {
        return __('partner/calendar.page_title');
    }

    /**
     * Get events for the calendar
     */
    public function getEvents(): JsonResponse
    {
        $user = Auth::user();

        // Get partner bills where current user is the partner
        $bills = PartnerBill::where('partner_id', $user->id)
            ->with(['client', 'category'])
            ->get();

        $events = $bills->map(function ($bill) {
            // Base date from the bill
            $baseDate = $bill->date;

            // Calculate start datetime
            if ($bill->start_time) {
                // Combine date with start_time
                $startDateTime = $baseDate->copy()
                    ->setHour($bill->start_time->hour)
                    ->setMinute($bill->start_time->minute)
                    ->setSecond($bill->start_time->second);
            } else {
                // Use just the date (all day event)
                $startDateTime = $baseDate->copy()->startOfDay();
            }

            // Calculate end datetime
            if ($bill->end_time) {
                // Combine date with end_time
                $endDateTime = $baseDate->copy()
                    ->setHour($bill->end_time->hour)
                    ->setMinute($bill->end_time->minute)
                    ->setSecond($bill->end_time->second);
            } else {
                // Default to 1 hour after start or end of day
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
                ]
            ];
        });

        return response()->json($events);
    }

    /**
     * Get current locale and translations for frontend
     */
    public function getLocaleData(): JsonResponse
    {
        $locale = app()->getLocale();
        $translations = __('partner/calendar');

        return response()->json([
            'locale' => $locale,
            'translations' => $translations
        ]);
    }

    /**
     * Get color based on bill status
     */
    private function getEventColor($status): string
    {
        return match ($status->value) {
            'pending' => '#f59e0b', // amber
            'paid' => '#10b981',    // emerald
            'cancelled' => '#ef4444', // red
            default => '#6b7280'    // gray
        };
    }

    /**
     * Get Vietnamese label for status
     */
    private function getStatusLabel($status): string
    {
        return match ($status->value) {
            'pending' => __('partner/calendar.status_pending'),
            'paid' => __('partner/calendar.status_paid'),
            'cancelled' => __('partner/calendar.status_cancelled'),
            default => __('partner/calendar.status_unknown')
        };
    }
}
