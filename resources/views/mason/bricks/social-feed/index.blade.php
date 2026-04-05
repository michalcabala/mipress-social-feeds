<section class="mx-auto w-full max-w-6xl px-4 sm:px-6">
    <div class="space-y-6">
        @if(filled($heading))
            <div class="max-w-3xl">
                <h2 class="text-3xl font-semibold text-slate-900 dark:text-white" style="font-family: 'Space Grotesk', sans-serif;">{{ $heading }}</h2>
            </div>
        @endif

        @if($posts->isNotEmpty())
            @php
                $showPageWidget = $feed->displaySetting('show_page_widget', true);
                $account = $feed->account;
                $followerCount = $account?->follower_count;
            @endphp

            <div class="sf-feed sf-feed--{{ $feed->layout->value }} rounded-3xl border border-slate-200 bg-white/90 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/85"
                 data-feed-id="{{ $feed->id }}"
                 data-platform="{{ $feed->account?->platform->value }}">

                @if($showPageWidget && $account)
                    <div class="mb-4 rounded-2xl border border-slate-200 bg-white/90 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/80">
                        <div class="flex items-start gap-3">
                            @if($account->avatar_url)
                                <img src="{{ $account->avatar_url }}" alt="{{ $account->name }}" class="h-12 w-12 rounded-xl object-cover">
                            @else
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-200 text-slate-500 dark:bg-slate-700 dark:text-slate-300">
                                    <x-heroicon-s-user class="h-5 w-5" />
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-base font-semibold text-slate-900 dark:text-slate-100">{{ $account->name }}</p>

                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                        {{ $account->platform->label() }}
                                    </span>

                                    @if($followerCount !== null)
                                        <span>{{ number_format($followerCount, 0, ',', ' ') }} sledujících</span>
                                    @endif

                                    @if(data_get($account->meta, 'link'))
                                        <a href="{{ data_get($account->meta, 'link') }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline dark:text-blue-300">Navštívit stránku</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @include("social-feeds::components.layouts.{$feed->layout->value}", [
                    'posts' => $posts,
                    'feed' => $feed,
                ])
            </div>
        @else
            <p class="rounded-2xl border border-dashed border-slate-300 bg-white/85 px-4 py-6 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-300">Žádné příspěvky k zobrazení.</p>
        @endif
    </div>
</section>
