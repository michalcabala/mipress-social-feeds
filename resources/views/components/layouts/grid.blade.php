@php
    $columns = $feed->displaySetting('columns', 3);
@endphp
<div class="sf-grid grid gap-4" style="grid-template-columns: repeat({{ $columns }}, 1fr);">
    @foreach($posts as $index => $post)
        <div @if($hasPagination ?? false) x-show="{{ $index }} < shown" x-transition @endif>
            @include('social-feeds::components.post-card', ['post' => $post, 'feed' => $feed])
        </div>
    @endforeach
</div>
