import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';

document.addEventListener('DOMContentLoaded', async function () {
    const calendarEl = document.getElementById('calendar');

    if (!calendarEl) return;

    // Load locale data from backend
    let localeData = {};
    let currentLocale = 'en';

    try {
        const response = await fetch('/api/calendar/locale', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        });

        if (response.ok) {
            const data = await response.json();
            localeData = data.translations;
            currentLocale = data.locale;
        }
    } catch (error) {
        console.error('Error loading locale data:', error);
        // Fallback to English
        localeData = {
            btn_today: 'Today',
            btn_month: 'Month',
            btn_week: 'Week',
            btn_day: 'Day',
            btn_list: 'List',
            loading_events: 'Loading calendar events...',
            events_loaded: 'Calendar events loaded successfully',
            loading_error: 'Unable to load calendar data. Please try again.',
            event_details_title: '📋 Order Information',
            event_code: 'Order Code',
            event_client: 'Client',
            event_category: 'Category',
            event_address: 'Address',
            event_phone: 'Phone',
            event_total: 'Total Amount',
            event_time: 'Time',
            event_status: 'Status',
            event_note: 'Note',
            no_info: 'No information',
            not_classified: 'Not classified',
            no_address: 'No address',
            no_phone: 'No phone number',
            not_determined: 'Not determined',
            no_note: 'No note'
        };
    }

    // Helper function to get translation
    function __(key) {
        return localeData[key] || key;
    }

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
        initialView: 'dayGridMonth',
        height: '100vh',
        timeZone: 'local',
        displayEventTime: true,
        eventDisplay: 'block',
        locale: currentLocale === 'vi' ? 'vi' : 'en',
        firstDayOfWeek: 1,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today: __('btn_today'),
            month: __('btn_month'),
            week: __('btn_week'),
            day: __('btn_day'),
            list: __('btn_list')
        },
        dayHeaderFormat: {
            weekday: 'short'
        },
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        editable: false,
        selectable: false,
        selectMirror: true,
        events: {
            url: '/api/calendar/events',
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            failure: function (error) {
                console.error('Error loading calendar events:', error);
                alert(__('loading_error'));
            },
            success: function (data) {
                console.log('Calendar events data:', data);
                if (data.length > 0) {
                    console.log('First event details:', {
                        title: data[0].title,
                        start: data[0].start,
                        end: data[0].end,
                        raw_data: data[0].extendedProps
                    });
                }
            }
        },
        eventDidMount: function (info) {
            const props = info.event.extendedProps;
            const startTime = info.event.start ? info.event.start.toLocaleTimeString(currentLocale === 'vi' ? 'vi-VN' : 'en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            }) : '';
            const endTime = info.event.end ? info.event.end.toLocaleTimeString(currentLocale === 'vi' ? 'vi-VN' : 'en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            }) : '';

            info.el.title = `${props.code} - ${props.client}
${__('event_time')}: ${startTime} - ${endTime}
${__('event_address')}: ${props.address}
${__('event_status')}: ${props.status}`;

            // Debug: Log event timing when mounted
            console.log('Event mounted:', {
                title: info.event.title,
                start: info.event.start,
                end: info.event.end,
                allDay: info.event.allDay,
                raw_date: props.raw_date,
                raw_start_time: props.raw_start_time,
                raw_end_time: props.raw_end_time
            });
        },
        loading: function (isLoading) {
            if (isLoading) {
                console.log(__('loading_events'));
            } else {
                console.log(__('events_loaded'));
            }
        }
    });

    calendar.render();
});
