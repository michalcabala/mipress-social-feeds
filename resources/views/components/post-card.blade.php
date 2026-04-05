@php
    $post = is_array($post) ? (object) $post : $post;
    $media = is_array($post->media ?? null) ? $post->media : [];
    $engagement = is_array($post->engagement ?? null) ? (object) $post->engagement : (object) [];
    $platform = $feed->account->platform;
    $contentLength = $feed->displaySetting('content_length', 300);
    $isAlbum = (($post->post_type ?? '') === 'album') || count($media) > 1;
@endphp

<article class="sf-post sf-post--{{ $post->post_type ?? 'text' }}">
    {{-- Header (název stránky / autor) --}}
    @if($feed->displaySetting('show_author') && ($post->author_name ?? false))
    <div class="sf-post__header flex items-center gap-3 mb-2">
        @if($post->author_avatar_url ?? false)
            <img src="{{ $post->author_avatar_url }}" alt="" class="w-10 h-10 rounded-full">
        @endif
        <div>
            <div class="font-semibold text-sm">{{ $post->author_name }}</div>
            @if($post->posted_at ?? false)
                <time class="text-xs text-gray-500" datetime="{{ $post->posted_at }}">
                    {{ \Carbon\Carbon::parse($post->posted_at)->diffForHumans() }}
                </time>
            @endif
        </div>
    </div>
    @endif

    {{-- Obsah --}}
    @if($post->content ?? false)
    <div class="sf-post__content mb-3">
        <p>{!! nl2br(e(Str::limit($post->content, $contentLength))) !!}</p>
    </div>
    @endif

    {{-- Média --}}
    @if(! empty($media))
    <div class="sf-post__media mb-3">
        @if($isAlbum)
            <div class="grid grid-cols-2 gap-2">
                @foreach(array_slice($media, 0, 4) as $index => $item)
                    @php
                        $itemType = $item['type'] ?? 'image';
                        $itemUrl = $item['url'] ?? null;
                        $thumbnailUrl = $item['thumbnail_url'] ?? $itemUrl;
                        $isLastVisibleItem = $index === 3 && count($media) > 4;
                    @endphp

                    @if($thumbnailUrl)
                        <div class="relative overflow-hidden rounded bg-gray-100">
                            @if($itemType === 'video')
                                <video src="{{ $itemUrl }}" poster="{{ $thumbnailUrl }}" controls class="h-36 w-full object-cover"></video>
                            @else
                                <img src="{{ $thumbnailUrl }}" alt="" class="h-36 w-full object-cover" loading="lazy">
                            @endif

                            @if($isLastVisibleItem)
                                <div class="absolute inset-0 flex items-center justify-center bg-black/50 text-lg font-semibold text-white">
                                    +{{ count($media) - 4 }}
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            @foreach(array_slice($media, 0, 4) as $item)
                @if(($item['type'] ?? '') === 'video')
                    <video src="{{ $item['url'] }}" poster="{{ $item['thumbnail_url'] ?? '' }}" controls class="w-full rounded"></video>
                @else
                    <img src="{{ $item['url'] }}" alt="" class="w-full rounded" loading="lazy">
                @endif
            @endforeach
        @endif
    </div>
    @endif

    {{-- Engagement (reakce, komentáře, sdílení) --}}
    @if($feed->displaySetting('show_engagement'))
    <div class="sf-post__engagement flex gap-4 text-sm text-gray-500">
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

    {{-- Odkaz na originál --}}
    @if($feed->displaySetting('show_permalink') && ($post->permalink ?? false))
    <a href="{{ $post->permalink }}" target="_blank" rel="noopener"
       class="sf-post__link text-sm mt-2 inline-block hover:underline"
       style="color: {{ $platform->color() }}">
        Zobrazit na {{ $platform->label() }} →
    </a>
    @endif
</article>
