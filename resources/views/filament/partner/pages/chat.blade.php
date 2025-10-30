@php
    /** @var \App\Filament\Partner\Pages\Chat $this */
    /** @var \App\Models\Thread|\Illuminate\Database\Eloquent\Collection|null $selectedThread */
    // Thay thế \App\Models\Thread bằng namespace model Thread thực tế của bạn
@endphp

<x-filament-panels::page>
    <div class="flex h-[calc(100vh-8rem)] flex-col gap-4 lg:h-[calc(100vh-12rem)] lg:flex-row">
        <!-- Threads List -->
        <div @class([
            'flex w-full flex-col overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700',
            'lg:w-1/3',
            'hidden lg:flex' => !$showThreadListOnMobile,
        ])>
            <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
                <div class="space-y-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Danh sách cuộc hội thoại</h3>
                    <div class="relative">
                        <input class="focus:border-primary-500 focus:ring-primary-500 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 dark:border-gray-600 dark:bg-gray-800 dark:text-white" type="search" wire:model.live.debounce.400ms="searchTerm"
                            placeholder="Tìm kiếm hội thoại..." aria-label="Tìm kiếm hội thoại" />
                        <div class="absolute inset-y-0 right-3 items-center text-sm text-gray-400 dark:text-gray-500" wire:loading.flex wire:target="searchTerm">
                            <x-filament::loading-indicator class="h-4 w-4" />
                        </div>
                    </div>
                </div>
            </div>

            <div id="threads-container" class="max-h-[70vh] flex-1 overflow-y-auto lg:h-full" x-data="{
                loading: false,
                init() {
                    const container = this.$el;
                    container.addEventListener('scroll', () => {
                        if (this.loading) return;

                        if (container.scrollTop + container.clientHeight >= container.scrollHeight - 100) {
                            if (this.$wire.get('hasMoreThreads')) {
                                this.loading = true;
                                this.$wire.loadMoreThreads().finally(() => {
                                    this.loading = false;
                                });
                            }
                        }
                    });
                }
            }">
                @forelse($threads as $thread)
                    <button class="{{ $selectedThreadId === $thread->id ? 'bg-primary-50 dark:bg-primary-900/20' : '' }} w-full border-b border-gray-200 px-4 py-3 text-left transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800" wire:click="openThread({{ $thread->id }})">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium text-gray-900 dark:text-white">
                                    {{ $thread->subject ?: 'Không có tiêu đề' }}
                                </p>
                                @if ($thread->other_participants->isNotEmpty())
                                    <p class="truncate text-sm text-gray-600 dark:text-gray-400">
                                        {{ $thread->other_participants->pluck('name')->join(', ') }}
                                    </p>
                                @endif
                                @if ($thread->latest_message)
                                    <p class="{{ $thread->is_unread ? 'font-extrabold text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-500' }} mt-1 truncate text-xs">
                                        {{ Str::limit($thread->latest_message->body, 50) }}
                                    </p>
                                @endif
                            </div>
                            <div class="ml-2 flex flex-col items-end gap-1">
                                @if ($thread->is_unread)
                                    <span class="bg-primary-600 inline-flex items-center justify-center rounded-full px-2 py-1 text-xs font-bold leading-none text-white">
                                        Mới
                                    </span>
                                @endif
                                @if ($thread->updated_at)
                                    <span class="text-xs text-gray-500 dark:text-gray-500">
                                        {{ $thread->updated_at->diffForHumans() }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="flex h-32 items-center justify-center text-gray-500 dark:text-gray-400">
                        <p>Chưa có cuộc hội thoại nào</p>
                    </div>
                @endforelse

                @if ($hasMoreThreads)
                    <div class="flex items-center justify-center py-4" wire:loading.delay wire:target="loadMoreThreads">
                        <x-filament::loading-indicator class="h-5 w-5" />
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Đang tải thêm...</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Messages Area -->
        <div @class([
            'flex w-full flex-1 flex-col overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700',
            'h-full',
            'lg:flex',
            'hidden lg:flex' => $showThreadListOnMobile,
        ])>
            @if ($selectedThreadId)
                <!-- Chat Header -->
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $selectedThread->subject ?: 'Không có tiêu đề' }}
                            </h3>
                            @if ($selectedThread->participants->isNotEmpty())
                                <p class="truncate text-sm text-gray-600 dark:text-gray-400">
                                    Người tham gia: {{ $selectedThread->participants->pluck('name')->filter()->join(', ') }}
                                </p>
                            @endif
                        </div>
                        <button class="focus:ring-primary-500 inline-flex items-center gap-1 rounded-lg border border-transparent px-3 py-1 text-sm font-medium text-gray-600 transition hover:bg-gray-200/70 focus:outline-none focus:ring-2 focus:ring-offset-2 lg:hidden dark:text-gray-300 dark:hover:bg-gray-700" type="button"
                            wire:click="showThreadList">
                            <x-filament::icon class="h-4 w-4" icon="heroicon-m-arrow-left" />
                            Trở lại
                        </button>
                    </div>
                </div>

                <!-- Messages -->
                <div id="messages-container" class="flex-1 space-y-4 overflow-y-auto p-4" x-data="{
                    loading: false,
                    isAtBottom: true,
                    oldScrollHeight: 0,
                    oldScrollTop: 0,
                    init() {
                        const container = this.$el;
                        const scrollToBottom = () => {
                            container.scrollTop = container.scrollHeight;
                        };

                        // Initial scroll to bottom
                        this.$nextTick(() => {
                            setTimeout(() => scrollToBottom(), 50);
                        });

                        // Scroll event for loading older messages
                        container.addEventListener('scroll', () => {
                            if (this.loading) return;

                            // Check if user scrolled to top (load more messages)
                            if (container.scrollTop <= 100) {
                                if (this.$wire.get('hasMoreMessages')) {
                                    this.loading = true;
                                    // Save current scroll position before loading
                                    this.oldScrollHeight = container.scrollHeight;
                                    this.oldScrollTop = container.scrollTop;

                                    this.$wire.loadMoreMessages()
                                        .then(() => {
                                            // Use $nextTick to ensure DOM has updated
                                            this.$nextTick(() => {
                                                const heightDifference = container.scrollHeight - this.oldScrollHeight;
                                                const targetScroll = Math.max(0, this.oldScrollTop + heightDifference);
                                                container.scrollTop = targetScroll;
                                            });
                                        })
                                        .finally(() => {
                                            this.loading = false;
                                        });
                                }
                            }

                            // Track if user is at bottom
                            this.isAtBottom = container.scrollTop + container.clientHeight >= container.scrollHeight - 50;
                        });

                        // Listen for new messages and scroll to bottom if user was at bottom
                        Livewire.hook('morph.updated', ({ el, component }) => {
                            if (el === container && this.isAtBottom && !this.loading) {
                                this.$nextTick(() => {
                                    setTimeout(() => scrollToBottom(), 50);
                                });
                            }
                        });
                    }
                }">
                    @if ($hasMoreMessages)
                        <div class="flex items-center justify-center py-2" wire:loading.delay wire:target="loadMoreMessages">
                            <x-filament::loading-indicator class="h-5 w-5" />
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Đang tải tin nhắn cũ...</span>
                        </div>
                    @endif

                    @forelse($messages as $message)
                        <div class="{{ $message['user_id'] === auth()->id() ? 'justify-end' : 'justify-start' }} flex">
                            <div class="max-w-[70%]">
                                @if ($message['user_id'] !== auth()->id())
                                    <p class="mb-1 text-xs text-gray-600 dark:text-gray-400">
                                        {{ $message['user']['name'] ?? 'Người dùng đã xóa' }}
                                    </p>
                                @endif
                                <div class="{{ $message['user_id'] === auth()->id() ? 'bg-primary-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' }} rounded-lg px-4 py-2">
                                    <p class="break-words text-sm">{{ $message['body'] }}</p>
                                </div>
                                <p class="{{ $message['user_id'] === auth()->id() ? 'text-right' : '' }} mt-1 text-xs text-gray-500 dark:text-gray-500">
                                    {{ $message['created_at']->format('H:i - d/m/Y') }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="flex h-full items-center justify-center text-gray-500 dark:text-gray-400">
                            <p>Chưa có tin nhắn nào</p>
                        </div>
                    @endforelse
                </div>

                <!-- Message Input -->
                <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                    <form class="flex flex-col gap-2 sm:flex-row" wire:submit="sendMessage">
                        <input class="focus:border-primary-500 focus:ring-primary-500 flex-1 rounded-lg border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white" type="text" wire:model="messageBody" placeholder="Nhập tin nhắn..." />
                        <button class="bg-primary-600 hover:bg-primary-700 inline-flex items-center justify-center rounded-lg px-6 py-2 text-sm font-medium text-white transition" type="submit">
                            Gửi
                        </button>
                    </form>
                </div>
            @else
                <div class="flex h-full items-center justify-center text-gray-500 dark:text-gray-400">
                    <div class="text-center">
                        <x-filament::icon class="mx-auto mb-4 h-16 w-16 text-gray-400" icon="heroicon-o-chat-bubble-left-right" />
                        <p>Chọn một cuộc hội thoại để bắt đầu</p>
                        <button class="bg-primary-600 hover:bg-primary-700 focus:ring-primary-500 mt-4 inline-flex items-center rounded-lg border border-transparent px-4 py-2 text-sm font-medium text-white transition focus:outline-none focus:ring-2 focus:ring-offset-2 lg:hidden" type="button" wire:click="showThreadList">
                            Mở danh sách hội thoại
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            const subscriptions = new Map();

            const registerThreads = (rawIds) => {
                if (!window.Echo) {
                    setTimeout(() => registerThreads(rawIds), 500);
                    return;
                }

                const threadIds = (rawIds ?? []).map((value) => Number(value)).filter((value) => !Number.isNaN(value));

                const activeIds = new Set(threadIds);

                threadIds.forEach((threadId) => {
                    if (subscriptions.has(threadId)) {
                        return;
                    }

                    const channel = window.Echo.private(`thread.${threadId}`)
                        .listen('SendMessage', (payload) => {
                            payload = Array.isArray(payload) ? payload : [payload];
                            Livewire.dispatch('chat:message-received', payload);
                        });

                    subscriptions.set(threadId, channel);
                });

                const toRemove = [];
                subscriptions.forEach((channel, threadId) => {
                    if (!activeIds.has(threadId)) {
                        window.Echo.leave(`thread.${threadId}`);
                        toRemove.push(threadId);
                    }
                });

                toRemove.forEach((threadId) => {
                    subscriptions.delete(threadId);
                });
            };

            Livewire.on('filament-partner-chat:threads-updated', ({
                threadIds
            }) => {
                registerThreads(threadIds);
            });

            registerThreads(@js(collect($threads)->pluck('id')->toArray()));
        });
    </script>
</x-filament-panels::page>
