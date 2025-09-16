<x-filament-panels::page>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="calendar-container h-screen">
        <div id="calendar" class="h-full w-full"></div>
    </div>

    @vite('resources/js/calendar.js')

    @push('styles')
        <style>
            .calendar-container {
                width: 100%;
                height: 100vh;
                margin: 0;
                padding: 0;
            }

            #calendar {
                margin: 0;
                padding: 1rem;
            }

            .fc {
                height: 100% !important;
            }

            .fc-view-harness {
                height: 100% !important;
            }

            .fc-toolbar-title {
                font-size: 1.5rem !important;
                font-weight: 600 !important;
            }

            .fc-button-primary {
                background-color: rgb(245 158 11) !important;
                border-color: rgb(245 158 11) !important;
            }

            .fc-button-primary:hover {
                background-color: rgb(217 119 6) !important;
                border-color: rgb(217 119 6) !important;
            }

            .fc-event {
                background-color: rgb(245 158 11) !important;
                border-color: rgb(245 158 11) !important;
            }

            .fc-event:hover {
                background-color: rgb(217 119 6) !important;
                border-color: rgb(217 119 6) !important;
            }
        </style>
    @endpush
</x-filament-panels::page>
