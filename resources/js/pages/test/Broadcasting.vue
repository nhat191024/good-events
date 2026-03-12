<script setup lang="ts">
import type { AppPageProps } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const page = usePage<AppPageProps>();
const auth = computed(() => page.props.auth);

// ──────────────────────────────────────────────
// Pusher / Echo state
// ──────────────────────────────────────────────
type ConnectionState = 'disconnected' | 'connecting' | 'connected' | 'failed';
const connectionState = ref<ConnectionState>('disconnected');
const pusherSocketId = ref<string | null>(null);

// ──────────────────────────────────────────────
// Event log
// ──────────────────────────────────────────────
interface LogEntry {
    id: number;
    time: string;
    level: 'info' | 'success' | 'error' | 'warning';
    channel: string;
    event: string;
    payload: unknown;
}
let logSeq = 0;
const logs = ref<LogEntry[]>([]);

function addLog(level: LogEntry['level'], channel: string, event: string, payload: unknown = null) {
    logs.value.unshift({
        id: ++logSeq,
        time: new Date().toLocaleTimeString('vi-VN', { hour12: false }),
        level,
        channel,
        event,
        payload,
    });
}

// ──────────────────────────────────────────────
// Available events
// ──────────────────────────────────────────────
type ChannelType = 'private' | 'public' | 'presence';

interface EventDefinition {
    id: string;
    label: string;
    description: string;
    channelType: ChannelType;
    channelTemplate: string; // e.g. "category.{categoryId}"
    eventName: string;
    params: { key: string; label: string; placeholder: string }[];
    requiresAuth: boolean;
}

const EVENT_DEFINITIONS: EventDefinition[] = [
    {
        id: 'new_partner_bill',
        label: 'NewPartnerBillCreated',
        description: 'Phát sinh khi một đơn hàng mới được tạo cho partner service category.',
        channelType: 'private',
        channelTemplate: 'category.{categoryId}',
        eventName: 'NewPartnerBillCreated',
        params: [{ key: 'categoryId', label: 'Category ID', placeholder: 'ví dụ: 1' }],
        requiresAuth: true,
    },
    {
        id: 'send_message',
        label: 'SendMessage',
        description: 'Phát sinh khi có tin nhắn mới trong một thread.',
        channelType: 'private',
        channelTemplate: 'thread.{threadId}',
        eventName: 'App\\Events\\SendMessage',
        params: [{ key: 'threadId', label: 'Thread ID', placeholder: 'ví dụ: 1' }],
        requiresAuth: true,
    },
];

// ──────────────────────────────────────────────
// Subscription state
// ──────────────────────────────────────────────
interface ActiveSub {
    definitionId: string;
    channelName: string;
    channelType: ChannelType;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    echoChannel: any;
}

const activeSubs = ref<ActiveSub[]>([]);
const selectedDefinitionId = ref<string>(EVENT_DEFINITIONS[0].id);
const paramValues = ref<Record<string, string>>({});

const selectedDefinition = computed(() => EVENT_DEFINITIONS.find((d) => d.id === selectedDefinitionId.value) ?? EVENT_DEFINITIONS[0]);

function resolveChannelName(def: EventDefinition): string {
    let name = def.channelTemplate;
    for (const p of def.params) {
        name = name.replace(`{${p.key}}`, paramValues.value[p.key] ?? '');
    }
    return name;
}

function onDefinitionChange() {
    paramValues.value = {};
}

// ──────────────────────────────────────────────
// Echo helpers
// ──────────────────────────────────────────────
// eslint-disable-next-line @typescript-eslint/no-explicit-any
function getEcho(): any | null {
    return (window as any).Echo ?? null;
}

function subscribe() {
    const echo = getEcho();
    if (!echo) {
        addLog('error', '-', 'subscribe', 'Echo chưa được khởi tạo. Kiểm tra VITE_PUSHER_* env.');
        return;
    }

    const def = selectedDefinition.value;
    const channelName = resolveChannelName(def);

    if (!channelName || def.params.some((p) => !paramValues.value[p.key]?.trim())) {
        addLog('warning', '-', 'subscribe', 'Vui lòng điền đầy đủ các tham số trước khi subscribe.');
        return;
    }

    const alreadySubscribed = activeSubs.value.some((s) => s.channelName === channelName);
    if (alreadySubscribed) {
        addLog('warning', channelName, 'subscribe', 'Channel này đã được subscribe rồi.');
        return;
    }

    addLog('info', channelName, 'subscribing...', null);

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    let echoChannel: any;

    if (def.channelType === 'private') {
        echoChannel = echo.private(channelName);
    } else if (def.channelType === 'presence') {
        echoChannel = echo.join(channelName);
    } else {
        echoChannel = echo.channel(channelName);
    }

    echoChannel
        .subscribed(() => {
            addLog('success', channelName, 'subscribed', null);
        })
        .error((err: unknown) => {
            addLog('error', channelName, 'subscription-error', err);
        })
        .listen(`.${def.eventName}`, (data: unknown) => {
            addLog('success', channelName, def.eventName, data);
        });

    activeSubs.value.push({
        definitionId: def.id,
        channelName,
        channelType: def.channelType,
        echoChannel,
    });
}

function unsubscribe(sub: ActiveSub) {
    const echo = getEcho();
    if (echo) {
        if (sub.channelType === 'private') {
            echo.leave(sub.channelName);
        } else if (sub.channelType === 'presence') {
            echo.leave(sub.channelName);
        } else {
            echo.leaveChannel(sub.channelName);
        }
    }
    activeSubs.value = activeSubs.value.filter((s) => s.channelName !== sub.channelName);
    addLog('info', sub.channelName, 'unsubscribed', null);
}

function unsubscribeAll() {
    for (const sub of [...activeSubs.value]) {
        unsubscribe(sub);
    }
}

function clearLogs() {
    logs.value = [];
}

// ──────────────────────────────────────────────
// Pusher connection monitoring
// ──────────────────────────────────────────────
// eslint-disable-next-line @typescript-eslint/no-explicit-any
let pusherInstance: any = null;

function bindPusherEvents() {
    const echo = getEcho();
    if (!echo?.connector?.pusher) return;

    pusherInstance = echo.connector.pusher;

    const conn = pusherInstance.connection;

    const setState = (s: ConnectionState) => {
        connectionState.value = s;
        addLog('info', 'pusher', `connection:${s}`, null);
    };

    conn.bind('connecting', () => setState('connecting'));
    conn.bind('connected', () => {
        setState('connected');
        pusherSocketId.value = conn.socket_id ?? null;
    });
    conn.bind('disconnected', () => setState('disconnected'));
    conn.bind('failed', () => setState('failed'));
    conn.bind('unavailable', () => setState('failed'));

    // Reflect current state immediately
    const current = conn.state as ConnectionState;
    connectionState.value = current === 'connected' ? 'connected' : (current ?? 'disconnected');
    if (connectionState.value === 'connected') {
        pusherSocketId.value = conn.socket_id ?? null;
    }
}

onMounted(() => {
    bindPusherEvents();
    addLog('info', 'system', 'page-mounted', { userId: auth.value?.user?.id ?? null });
});

onUnmounted(() => {
    unsubscribeAll();
});

// ──────────────────────────────────────────────
// UI helpers
// ──────────────────────────────────────────────
const connectionBadgeClass = computed(() => {
    return {
        connected: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
        connecting: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
        disconnected: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
        failed: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
    }[connectionState.value];
});

const levelClass = (level: LogEntry['level']) =>
    ({
        info: 'text-blue-600 dark:text-blue-400',
        success: 'text-green-600 dark:text-green-400',
        error: 'text-red-600 dark:text-red-400',
        warning: 'text-yellow-600 dark:text-yellow-400',
    })[level];

function prettyJson(val: unknown): string {
    if (val === null || val === undefined) return '';
    return JSON.stringify(val, null, 2);
}
</script>

<template>
    <Head title="Broadcasting Test" />

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4 md:p-6">
        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Broadcasting Test</h1>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Kiểm tra Pusher events theo thời gian thực</p>
            </div>

            <!-- Auth info -->
            <div class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                    />
                </svg>
                <span v-if="auth?.user" class="font-medium text-gray-700 dark:text-gray-300">
                    {{ auth.user.name }}
                    <span class="font-normal text-gray-400">(ID: {{ auth.user.id }})</span>
                </span>
                <span v-else class="font-medium text-red-500">Chưa đăng nhập</span>
            </div>
        </div>

        <!-- Connection status bar -->
        <div class="flex flex-wrap items-center gap-4 rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Pusher:</span>
                <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize', connectionBadgeClass]">
                    {{ connectionState }}
                </span>
            </div>
            <div v-if="pusherSocketId" class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Socket ID:</span>
                <code class="rounded bg-gray-100 px-2 py-0.5 font-mono text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    {{ pusherSocketId }}
                </code>
            </div>
            <div class="ml-auto flex items-center gap-2">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Active subs:</span>
                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                    {{ activeSubs.length }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Left: Subscribe panel -->
            <div class="flex h-fit flex-col gap-5 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-200">Subscribe Event</h2>

                <!-- Event select -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-medium tracking-wide text-gray-600 uppercase dark:text-gray-400"> Chọn Event </label>
                    <select
                        v-model="selectedDefinitionId"
                        @change="onDefinitionChange"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    >
                        <option v-for="def in EVENT_DEFINITIONS" :key="def.id" :value="def.id">
                            {{ def.label }}
                        </option>
                    </select>

                    <!-- Description -->
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        {{ selectedDefinition.description }}
                    </p>
                </div>

                <!-- Channel info -->
                <div class="flex flex-col gap-1 rounded-lg bg-gray-50 px-3 py-2.5 text-xs dark:bg-gray-800">
                    <div class="flex gap-2">
                        <span class="w-24 shrink-0 font-medium text-gray-500 dark:text-gray-400">Channel:</span>
                        <code class="font-mono text-blue-600 dark:text-blue-400">
                            {{ selectedDefinition.channelType }}-{{ resolveChannelName(selectedDefinition) }}
                        </code>
                    </div>
                    <div class="flex gap-2">
                        <span class="w-24 shrink-0 font-medium text-gray-500 dark:text-gray-400">Event name:</span>
                        <code class="font-mono text-purple-600 dark:text-purple-400">.{{ selectedDefinition.eventName }}</code>
                    </div>
                    <div class="flex gap-2">
                        <span class="w-24 shrink-0 font-medium text-gray-500 dark:text-gray-400">Auth:</span>
                        <span :class="selectedDefinition.requiresAuth ? 'text-orange-500' : 'text-gray-500 dark:text-gray-400'">
                            {{ selectedDefinition.requiresAuth ? 'Yêu cầu đăng nhập' : 'Không cần' }}
                        </span>
                    </div>
                </div>

                <!-- Params -->
                <div v-if="selectedDefinition.params.length" class="flex flex-col gap-3">
                    <label class="text-xs font-medium tracking-wide text-gray-600 uppercase dark:text-gray-400"> Tham số kênh </label>
                    <div v-for="param in selectedDefinition.params" :key="param.key" class="flex flex-col gap-1">
                        <label :for="'param-' + param.key" class="text-sm text-gray-700 dark:text-gray-300">
                            {{ param.label }}
                        </label>
                        <input
                            :id="'param-' + param.key"
                            v-model="paramValues[param.key]"
                            type="text"
                            :placeholder="param.placeholder"
                            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                        />
                    </div>
                </div>

                <!-- Auth alert -->
                <div
                    v-if="selectedDefinition.requiresAuth && !auth?.user"
                    class="rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-xs text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400"
                >
                    Event này yêu cầu xác thực. Vui lòng đăng nhập trước khi subscribe.
                </div>

                <button
                    @click="subscribe"
                    :disabled="selectedDefinition.requiresAuth && !auth?.user"
                    class="w-full rounded-lg bg-blue-600 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Subscribe
                </button>
            </div>

            <!-- Right: Active subscriptions -->
            <div class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-200">Active Subscriptions</h2>
                    <button
                        v-if="activeSubs.length"
                        @click="unsubscribeAll"
                        class="text-xs font-medium text-red-500 transition-colors hover:text-red-700 dark:hover:text-red-400"
                    >
                        Hủy tất cả
                    </button>
                </div>

                <div v-if="activeSubs.length === 0" class="py-4 text-center text-sm text-gray-400 italic dark:text-gray-600">
                    Chưa có subscription nào
                </div>

                <ul v-else class="flex flex-col gap-2">
                    <li
                        v-for="sub in activeSubs"
                        :key="sub.channelName"
                        class="flex items-center justify-between gap-2 rounded-lg bg-gray-50 px-3 py-2.5 dark:bg-gray-800"
                    >
                        <div class="flex min-w-0 flex-col gap-0.5">
                            <span class="truncate font-mono text-xs font-semibold text-blue-600 dark:text-blue-400">
                                {{ sub.channelType }}-{{ sub.channelName }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ EVENT_DEFINITIONS.find((d) => d.id === sub.definitionId)?.eventName }}
                            </span>
                        </div>
                        <button
                            @click="unsubscribe(sub)"
                            class="shrink-0 text-xs font-medium text-red-500 transition-colors hover:text-red-700 dark:hover:text-red-400"
                        >
                            Hủy
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Event log -->
        <div class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-200">
                    Event Log
                    <span class="ml-1.5 text-xs font-normal text-gray-400">({{ logs.length }} entries)</span>
                </h2>
                <button
                    v-if="logs.length"
                    @click="clearLogs"
                    class="text-xs font-medium text-gray-400 transition-colors hover:text-gray-600 dark:hover:text-gray-300"
                >
                    Clear
                </button>
            </div>

            <div
                class="max-h-96 overflow-y-auto rounded-lg border border-gray-800 bg-gray-950 font-mono text-xs dark:border-gray-800 dark:bg-gray-950"
            >
                <div v-if="logs.length === 0" class="px-4 py-6 text-center text-gray-600 italic">Chưa có sự kiện nào...</div>

                <div
                    v-for="log in logs"
                    :key="log.id"
                    class="flex gap-3 border-b border-gray-800/50 px-4 py-2 transition-colors last:border-0 hover:bg-gray-900/50"
                >
                    <!-- Time -->
                    <span class="shrink-0 pt-0.5 text-gray-600">{{ log.time }}</span>

                    <!-- Level -->
                    <span :class="['w-16 shrink-0 pt-0.5 font-bold', levelClass(log.level)]"> [{{ log.level.toUpperCase() }}] </span>

                    <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                        <div class="flex flex-wrap gap-2">
                            <span class="text-yellow-400">{{ log.channel }}</span>
                            <span class="text-gray-500">→</span>
                            <span class="text-green-400">{{ log.event }}</span>
                        </div>
                        <pre
                            v-if="log.payload !== null && log.payload !== undefined"
                            class="mt-0.5 text-[11px] leading-relaxed break-all whitespace-pre-wrap text-gray-300"
                            >{{ prettyJson(log.payload) }}</pre
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
