<x-filament-widgets::widget>
    <x-filament::section heading="Náhled feedu" icon="fal-eye" collapsible>
        @if($feed && $posts->isNotEmpty())
            @php
                $showAuthor = $feed->displaySetting('show_author', true);
                $showPostedAt = $feed->displaySetting('show_posted_at', true);
                $showEngagement = $feed->displaySetting('show_engagement', true);
                $showPermalink = $feed->displaySetting('show_permalink', true);
                $contentLength = (int) $feed->displaySetting('content_length', 300);
                $paginationType = $feed->displaySetting('pagination_type', 'none');
                $perPage = (int) $feed->displaySetting('per_page', 5);
                $previewPosts = $paginationType === 'none' ? $posts : $posts->take($perPage);
                $accountName = $feed->account?->name ?? 'Neznamy ucet';
                $platformLabel = $feed->account?->platform?->label() ?? 'socialni siti';
            @endphp

            <div class="sf-preview rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 overflow-auto max-h-150">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Layout: <strong>{{ $feed->layout->label() }}</strong> · {{ $posts->count() }} příspěvků
                        @if($paginationType === 'load_more')
                            · po {{ $perPage }}
                        @endif
                    </span>
                    <span class="text-xs text-gray-400">
                        <code>&lt;x-social-feed slug="{{ $feed->slug }}" /&gt;</code>
                    </span>
                </div>

                {{-- Active settings badges --}}
                <div class="mb-3 flex flex-wrap gap-1.5">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium {{ $showAuthor ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500 line-through' }}">
                        Autor
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium {{ $showPostedAt ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500 line-through' }}">
                        Datum
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium {{ $showEngagement ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500 line-through' }}">
                        Reakce
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium {{ $showPermalink ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500 line-through' }}">
                        Odkaz
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                        {{ $contentLength }} zn.
                    </span>
                    @if($paginationType === 'load_more')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                            Načíst více ({{ $perPage }})
                        </span>
                    @endif
                </div>

                <div class="sf-preview__content">
                    @foreach($previewPosts as $post)
                        @php
                            $post = is_array($post) ? (object) $post : $post;
                            $media = is_array($post->media ?? null) ? $post->media : [];
                            $engagement = is_array($post->engagement ?? null) ? (object) $post->engagement : (object) [];
                        @endphp
                        <div class="sf-preview__post border-b border-gray-100 dark:border-gray-800 pb-4 mb-4 last:border-0 last:mb-0 last:pb-0">
                            {{-- Header (author) --}}
                            @if($showAuthor)
                            <div class="flex items-center gap-2 mb-2">
                                @if($post->author_avatar_url ?? false)
                                    <img src="{{ $post->author_avatar_url }}" alt="" class="w-8 h-8 rounded-full">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                        @svg('fal-user', ['class' => 'w-4 h-4 text-gray-400'])
                                    </div>
                                @endif
                                <div>
                                    <div class="font-medium text-sm text-gray-900 dark:text-gray-100">{{ $post->author_name ?? $accountName }}</div>
                                </div>
                            </div>
                            @endif

                            @if($showPostedAt && ($post->posted_at ?? false))
                                <div class="mb-2">
                                    <time class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($post->posted_at)->diffForHumans() }}</time>
                                </div>
                            @endif

                            {{-- Content --}}
                            @if($post->content ?? false)
                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">{!! nl2br(e(\Illuminate\Support\Str::limit($post->content, $contentLength))) !!}</p>
                            @endif

                            {{-- Media thumbnail --}}
                            @if(!empty($media))
                                <div class="flex gap-2 mb-2">
                                    @foreach(array_slice($media, 0, 3) as $item)
                                        <img src="{{ $item['url'] ?? '' }}" alt="" class="w-20 h-20 object-cover rounded" loading="lazy">
                                    @endforeach
                                    @if(count($media) > 3)
                                        <div class="w-20 h-20 rounded bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-xs text-gray-500">+{{ count($media) - 3 }}</div>
                                    @endif
                                </div>
                            @endif

                            {{-- Engagement --}}
                            @if($showEngagement)
                            <div class="flex gap-3 text-xs text-gray-500">
                                @if(($engagement->reactions ?? 0) > 0 || ($engagement->likes ?? 0) > 0)
                                    <span>👍 {{ $engagement->reactions ?? $engagement->likes ?? 0 }}</span>
                                @endif
                                @if(($engagement->comments ?? 0) > 0)
                                    <span>💬 {{ $engagement->comments }}</span>
                                @endif
                                @if(($engagement->shares ?? 0) > 0)
                                    <span>↗️ {{ $engagement->shares }}</span>
                                @endif
                            </div>
                            @endif

                            {{-- Permalink --}}
                            @if($showPermalink && ($post->permalink ?? false))
                                <a href="{{ $post->permalink }}" target="_blank" rel="noopener" class="text-xs text-primary-600 hover:underline mt-1 inline-block">
                                    Zobrazit na {{ $platformLabel }} →
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Load more indicator --}}
                @if($paginationType === 'load_more' && $posts->count() > $perPage)
                    <div class="text-center pt-3 border-t border-gray-100 dark:border-gray-800">
                        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 text-xs text-gray-400">
                            Tlačítko „Načíst více" — dalších {{ $posts->count() - $perPage }} příspěvků
                        </span>
                    </div>
                @endif
            </div>
        @elseif($feed)
            <div class="text-sm text-gray-500 dark:text-gray-400 py-8 text-center">
                @svg('fal-inbox', ['class' => 'w-12 h-12 mx-auto mb-2 text-gray-300 dark:text-gray-600'])
                <p>Zatím žádné příspěvky.</p>
                <p class="text-xs mt-1">Klikněte na „Obnovit" pro načtení dat z API.</p>
            </div>
        @else
            <div class="text-sm text-gray-500 dark:text-gray-400 py-4 text-center">
                Nejprve uložte feed pro zobrazení náhledu.
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
