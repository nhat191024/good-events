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
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: 'today'
        },
        footerToolbar: {
            left: '',
            center: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
            right: ''
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
        },
        eventClick: function (info) {
            console.log('Event clicked:', {
                title: info.event.title,
                start: info.event.start,
                end: info.event.end,
                allDay: info.event.allDay,
                raw_date: info.event.extendedProps.raw_date,
                raw_start_time: info.event.extendedProps.raw_start_time,
                raw_end_time: info.event.extendedProps.raw_end_time
            });
            showEventModal(info.event);
        }
    });

    // Modal functions
    function showEventModal(event) {
        const modal = document.getElementById('eventModal');
        const props = event.extendedProps;

        // Populate modal with event data
        document.getElementById('modalEventTitle').textContent = event.title;
        document.getElementById('modalEventCode').textContent = props.code || __('no_info');
        document.getElementById('modalEventClient').textContent = props.client || __('no_info');
        document.getElementById('modalEventCategory').textContent = props.category || __('not_classified');
        document.getElementById('modalEventAddress').textContent = props.address || __('no_address');
        document.getElementById('modalEventPhone').textContent = props.phone || __('no_phone');
        document.getElementById('modalEventTotal').textContent = props.total ? new Intl.NumberFormat(currentLocale === 'vi' ? 'vi-VN' : 'en-US', {
            style: 'currency',
            currency: 'VND'
        }).format(props.total) : __('not_determined');

        // Format time display
        const startTime = event.start ? event.start.toLocaleTimeString(currentLocale === 'vi' ? 'vi-VN' : 'en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        }) : '';
        const endTime = event.end ? event.end.toLocaleTimeString(currentLocale === 'vi' ? 'vi-VN' : 'en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        }) : '';
        const eventDate = event.start ? event.start.toLocaleDateString(currentLocale === 'vi' ? 'vi-VN' : 'en-US') : '';

        document.getElementById('modalEventTime').textContent = `${eventDate} ${startTime} - ${endTime}`;
        document.getElementById('modalEventStatus').textContent = props.status || __('not_determined');
        document.getElementById('modalEventNote').textContent = props.note || __('no_note');

        // Set status badge color
        const statusBadge = document.getElementById('modalEventStatus');
        statusBadge.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusBadgeClass(props.status_raw)}`;

        // Show modal
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideEventModal() {
        const modal = document.getElementById('eventModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function getStatusBadgeClass(status) {
        switch (status) {
            case 'pending':
                return 'text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
            case 'confirmed':
                return 'text-blue-800 dark:bg-blue-900 dark:text-blue-300';
            case 'completed':
                return 'text-green-800 dark:bg-green-900 dark:text-green-300';
            case 'cancelled':
                return 'text-red-800 dark:bg-red-900 dark:text-red-300';
            default:
                return 'text-gray-800 dark:bg-gray-900 dark:text-gray-300';
        }
    }

    // Event listeners for modal
    document.addEventListener('click', function (e) {
        if (e.target.id === 'eventModal' || e.target.classList.contains('modal-close')) {
            hideEventModal();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            hideEventModal();
        }
    });

    calendar.render();
});
