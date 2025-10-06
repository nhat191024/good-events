<x-filament-panels::page>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="calendar-container h-screen">
        <div id="calendar" class="h-full w-full"></div>
    </div>

    <!-- Event Detail Modal -->
    <div id="eventModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
            <!-- Modal panel -->
            <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle dark:bg-gray-800">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 dark:bg-gray-800">
                    <!-- Modal header -->
                    <div class="mb-4 flex items-center justify-between">
                        <h3 id="modal-title" class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                            {{ __('partner/calendar.event_details_title') }}
                        </h3>
                        <button class="modal-close text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" type="button">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal content -->
                    <div class="space-y-4">
                        <!-- Event Title -->
                        <div>
                            <h4 id="modalEventTitle" class="mb-4 text-xl font-semibold text-gray-900 dark:text-white"></h4>
                        </div>

                        <!-- Event Details Grid -->
                        <div class="grid grid-cols-1 gap-4">
                            <!-- Order Code -->
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('partner/calendar.event_code') }}</span>
                                <span id="modalEventCode" class="text-sm text-gray-900 dark:text-white"></span>
                            </div>

                            <!-- Client -->
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('partner/calendar.event_client') }}</span>
                                <span id="modalEventClient" class="text-sm text-gray-900 dark:text-white"></span>
                            </div>

                            <!-- Category -->
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('partner/calendar.event_category') }}</span>
                                <span id="modalEventCategory" class="text-sm text-gray-900 dark:text-white"></span>
                            </div>

                            <!-- Address -->
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('partner/calendar.event_address') }}</span>
                                <span id="modalEventAddress" class="text-sm text-gray-900 dark:text-white"></span>
                            </div>

                            <!-- Phone -->
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('partner/calendar.event_phone') }}</span>
                                <span id="modalEventPhone" class="text-sm text-gray-900 dark:text-white"></span>
                            </div>

                            <!-- Total Amount -->
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('partner/calendar.event_total') }}</span>
                                <span id="modalEventTotal" class="text-sm font-semibold text-gray-900 dark:text-white"></span>
                            </div>

                            <!-- Time -->
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('partner/calendar.event_time') }}</span>
                                <span id="modalEventTime" class="text-sm text-gray-900 dark:text-white"></span>
                            </div>

                            <!-- Status -->
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('partner/calendar.event_status') }}</span>
                                <span id="modalEventStatus" class="text-sm"></span>
                            </div>

                            <!-- Note -->
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('partner/calendar.event_note') }}</span>
                                <span id="modalEventNote" class="text-sm text-gray-900 dark:text-white"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 dark:bg-gray-700">
                    <button class="modal-close inline-flex w-full justify-center rounded-md border border-transparent bg-amber-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm" type="button">
                        {{ __('partner/calendar.close_button') ?? 'Đóng' }}
                    </button>
                </div>
            </div>
        </div>
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

            /* Event cursor pointer */
            .fc-event {
                cursor: pointer !important;
            }

            /* Modal styles */
            #eventModal {
                backdrop-filter: blur(4px);
            }

            #eventModal .bg-gray-500 {
                animation: fadeIn 0.3s ease-out;
            }

            #eventModal .inline-block {
                animation: slideIn 0.3s ease-out;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px) scale(0.95);
                }

                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            /* Modal responsive adjustments */
            @media (max-width: 640px) {
                #eventModal .sm\\:max-w-lg {
                    max-width: 95%;
                    margin: 1rem;
                }
            }

            /* Status badge hover effect */
            #modalEventStatus {
                transition: all 0.2s ease-in-out;
            }

            /* Close button hover effect */
            .modal-close {
                transition: all 0.2s ease-in-out;
            }

            .modal-close:hover {
                transform: scale(1.05);
            }

            /* Mobile Responsive Styles */
            @media (max-width: 768px) {
                #calendar {
                    padding: 0.5rem;
                }

                /* Header toolbar adjustments */
                .fc-toolbar-title {
                    font-size: 1.2rem !important;
                    margin: 0.5rem 0 !important;
                }

                .fc-toolbar {
                    flex-direction: column !important;
                    gap: 0.5rem !important;
                }

                .fc-toolbar-chunk {
                    display: flex !important;
                    justify-content: center !important;
                    align-items: center !important;
                }

                /* Button improvements for touch */
                .fc-button {
                    min-height: 44px !important;
                    min-width: 44px !important;
                    font-size: 0.9rem !important;
                    padding: 0.5rem 0.75rem !important;
                    margin: 0.125rem !important;
                }

                /* Footer toolbar (view buttons) */
                .fc-footer-toolbar {
                    margin-top: 0.5rem !important;
                    padding: 0.5rem !important;
                }

                .fc-footer-toolbar .fc-toolbar-chunk {
                    flex-wrap: wrap !important;
                }

                /* Event text size */
                .fc-event-title {
                    font-size: 0.8rem !important;
                }

                .fc-event-time {
                    font-size: 0.75rem !important;
                }

                /* Day cell padding */
                .fc-daygrid-day-number {
                    font-size: 0.9rem !important;
                    padding: 0.25rem !important;
                }

                /* List view improvements */
                .fc-list-event-title {
                    font-size: 0.9rem !important;
                }

                .fc-list-event-time {
                    font-size: 0.8rem !important;
                }
            }

            /* Extra small screens */
            @media (max-width: 480px) {
                .fc-toolbar-title {
                    font-size: 1rem !important;
                }

                .fc-button {
                    font-size: 0.8rem !important;
                    padding: 0.4rem 0.6rem !important;
                }

                /* Hide less important buttons on very small screens */
                .fc-timeGridDay-button {
                    display: none !important;
                }

                /* Stack footer toolbar buttons vertically if needed */
                .fc-footer-toolbar .fc-toolbar-chunk {
                    flex-direction: column !important;
                    gap: 0.25rem !important;
                }
            }

            /* Tablet landscape improvements */
            @media (min-width: 769px) and (max-width: 1024px) {
                .fc-toolbar-title {
                    font-size: 1.4rem !important;
                }

                .fc-button {
                    min-height: 40px !important;
                    font-size: 0.95rem !important;
                }
            }

            /* Touch device specific improvements */
            @media (hover: none) and (pointer: coarse) {
                .fc-button {
                    min-height: 48px !important;
                    min-width: 48px !important;
                }

                .fc-event {
                    min-height: 24px !important;
                }
            }
        </style>
    @endpush
</x-filament-panels::page>
