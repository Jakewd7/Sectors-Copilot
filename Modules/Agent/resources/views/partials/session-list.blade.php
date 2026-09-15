            <div class="flex-1 overflow-y-auto p-3 space-y-1">
                {{-- Alpine-owned list: pin/rename re-order and re-label in place,
                     no page reload. Sorted pinned-first, newest first (same rule
                     as the controller) via the sessionListOrdered getter. --}}
                <template x-for="session in sessionListOrdered" :key="session.id">
                    <div x-data="sessionCard" @keydown.escape.window="closeMenu()">
                        <div class="flex items-center gap-2 p-2.5 rounded-lg cursor-pointer transition hover:bg-layer-hover border border-transparent"
                            :class="activeSessionId === session.id ? 'bg-layer-hover border-layer-line' : ''"
                            @click="switchSession(session.id)">

                            {{-- Pinned indicator (a state, not a button — the menu owns the actions) --}}
                            <svg x-show="session.is_pinned" class="size-3.5 shrink-0 text-primary" fill="currentColor"
                                 viewBox="0 0 24 24" title="Pinned">
                                <path d="M16 3a1 1 0 011 1v1h1a1 1 0 110 2h-.28l-.9 5.4 1.9 1.9a1 1 0 01.28.7V16a1 1 0 01-1 1h-4v4a1 1 0 11-2 0v-4H7a1 1 0 01-1-1v-1a1 1 0 01.3-.7l1.9-1.9-.9-5.4H7a1 1 0 110-2h1V4a1 1 0 011-1h7z"/>
                            </svg>

                            <span class="truncate text-sm text-foreground flex-1" x-text="session.title"></span>

                            {{-- Session actions menu --}}
                            <button type="button" @click.stop="toggleMenu($el)"
                                class="shrink-0 size-6 rounded-md inline-flex items-center justify-center text-muted-foreground-1 hover:text-foreground hover:bg-surface-4 transition"
                                :class="menuOpen ? 'bg-surface-4 text-foreground' : ''"
                                aria-label="Session options" :aria-expanded="menuOpen ? 'true' : 'false'">
                                <svg class="size-4" fill="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="5" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="12" cy="19" r="1.6"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Dropdown menu (fixed positioning so the sidebar's overflow cannot clip it) --}}
                        <div x-show="menuOpen" x-cloak x-transition.opacity.duration.100ms
                             @click.outside="closeMenu()"
                             class="fixed z-50 w-44 rounded-lg border border-layer-line bg-card shadow-lg p-1.5"
                             :style="menuStyle" role="menu">
                            <button type="button" role="menuitem"
                                    @click="closeMenu(); togglePin(session)"
                                    class="w-full flex items-center gap-x-2.5 rounded-md px-2.5 py-2 text-sm text-foreground hover:bg-layer-hover transition text-left">
                                <svg class="size-4 shrink-0 text-muted-foreground-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                          d="M16 3a1 1 0 011 1v1h1a1 1 0 110 2h-.28l-.9 5.4 1.9 1.9a1 1 0 01.28.7V16a1 1 0 01-1 1h-4v4a1 1 0 11-2 0v-4H7a1 1 0 01-1-1v-1a1 1 0 01.3-.7l1.9-1.9-.9-5.4H7a1 1 0 110-2h1V4a1 1 0 011-1h7z"/>
                                </svg>
                                <span x-text="session.is_pinned ? 'Unpin chat' : 'Pin chat'"></span>
                            </button>

                            <button type="button" role="menuitem"
                                    @click="closeMenu(); startRename(session)"
                                    class="w-full flex items-center gap-x-2.5 rounded-md px-2.5 py-2 text-sm text-foreground hover:bg-layer-hover transition text-left">
                                <svg class="size-4 shrink-0 text-muted-foreground-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Rename
                            </button>

                            <div class="my-1 border-t border-layer-line"></div>

                            <button type="button" role="menuitem"
                                    @click="closeMenu(); askDelete(session)"
                                    class="w-full flex items-center gap-x-2.5 rounded-md px-2.5 py-2 text-sm text-danger hover:bg-danger/10 transition text-left">
                                <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </template>

                {{-- Empty state (e.g. after deleting the last session) --}}
                <p x-show="sessionList.length === 0" x-cloak
                   class="px-2.5 py-6 text-center text-xs text-muted-foreground-1">
                    No research sessions yet — start one above.
                </p>
            </div>