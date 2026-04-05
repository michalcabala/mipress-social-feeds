<div class="sf-list space-y-4">
    @foreach($posts as $index => $post)
        <div @if($hasPagination ?? false) x-show="{{ $index }} < shown" x-transition @endif>
            @include('social-feeds::components.post-card', ['post' => $post, 'feed' => $feed])
        </div>
    @endforeach
</div>
