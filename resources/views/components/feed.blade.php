@if($posts->isNotEmpty())
@php
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
