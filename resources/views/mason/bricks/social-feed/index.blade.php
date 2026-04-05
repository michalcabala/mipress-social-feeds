<section class="mx-auto w-full max-w-6xl px-4 sm:px-6">
    <div class="space-y-6">
        @if(filled($heading))
            <div class="max-w-3xl">
                <h2 class="text-3xl font-semibold text-slate-900 dark:text-white" style="font-family: 'Space Grotesk', sans-serif;">{{ $heading }}</h2>
            </div>
        @endif

        @if($posts->isNotEmpty())
            <div class="sf-feed sf-feed--{{ $feed->layout->value }} rounded-3xl border border-slate-200 bg-white/90 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/85"
                 data-feed-id="{{ $feed->id }}"
                 data-platform="{{ $feed->account?->platform->value }}">

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
