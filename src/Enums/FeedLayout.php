<?php

declare(strict_types=1);

namespace MiPress\SocialFeeds\Enums;

enum FeedLayout: string
{
    case List = 'list';
    case Grid = 'grid';
    case Masonry = 'masonry';
    case Carousel = 'carousel';

    public function label(): string
    {
        return match ($this) {
            self::List => __('social-feeds::admin.enums.feed_layout.list'),
            self::Grid => __('social-feeds::admin.enums.feed_layout.grid'),
            self::Masonry => __('social-feeds::admin.enums.feed_layout.masonry'),
            self::Carousel => __('social-feeds::admin.enums.feed_layout.carousel'),
        };
    }
}
