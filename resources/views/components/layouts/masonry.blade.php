@php
    $columns = $feed->displaySetting('columns', 3);
@endphp
<div class="sf-masonry columns-{{ $columns }} gap-4">
    @foreach($posts as $index => $post)
        <div class="break-inside-avoid mb-4" @if($hasPagination ?? false) x-show="{{ $index }} < shown" x-transition @endif>
            @include('social-feeds::components.post-card', ['post' => $post, 'feed' => $feed])
        </div>
    @endforeach
</div>
