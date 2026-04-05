@if($posts->isNotEmpty())
@php
    $showPageWidget = $feed->displaySetting('show_page_widget', true);
    $account = $feed->account;
    $followerCount = $account?->follower_count;
    $paginationType = $feed->displaySetting('pagination_type', 'none');
    $perPage = (int) $feed->displaySetting('per_page', 5);
    $totalPosts = $posts->count();
    $hasPagination = $paginationType === 'load_more' && $totalPosts > $perPage;
@endphp

<div class="sf-feed sf-feed--{{ $feed->layout->value }}"
     data-feed-id="{{ $feed->id }}"
     data-platform="{{ $feed->account->platform->value }}"
     @if($hasPagination)
     x-data="{ shown: {{ $perPage }}, total: {{ $totalPosts }}, perPage: {{ $perPage }} }"
     @endif
>
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
        'perPage' => $perPage,
        'hasPagination' => $hasPagination,
    ])

    @if($hasPagination)
    <div class="sf-feed__load-more text-center mt-6" x-show="shown < total" x-cloak>
        <button
            @click="shown = Math.min(shown + perPage, total)"
            class="inline-flex items-center gap-2 px-6 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
        >
            Načíst více
            <span class="text-xs text-gray-400" x-text="`(${Math.min(shown, total)}/${total})`"></span>
        </button>
    </div>
    @endif
</div>
@endif
