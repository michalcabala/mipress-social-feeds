<?php

declare(strict_types=1);

return [
    'crud' => [
        'created' => 'Record created',
        'saved' => 'Changes saved',
    ],
    'enums' => [
        'feed_layout' => [
            'list' => 'List',
            'grid' => 'Grid',
            'masonry' => 'Masonry',
            'carousel' => 'Carousel',
        ],
        'social_platform' => [
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'x' => 'X (Twitter)',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok',
        ],
    ],
    'resources' => [
        'social_account' => [
            'navigation_group' => 'Social feeds',
            'navigation_label' => 'Connected accounts',
            'model_label' => 'Connected account',
            'plural_model_label' => 'Connected accounts',
            'sections' => [
                'account_information' => 'Account information',
                'token_status' => 'Token status',
            ],
            'fields' => [
                'platform' => 'Platform',
                'name' => 'Account / page name',
                'username' => 'Username',
                'platform_account_id' => 'Platform ID',
                'status' => 'Status',
                'expires_at' => 'Expires',
                'last_verified' => 'Last verification',
            ],
            'states' => [
                'token_expired' => 'Token expired',
                'token_expiring' => 'Token expires soon',
                'token_valid' => 'Token valid',
                'no_expiration' => 'No expiration',
                'not_verified' => 'Not verified',
            ],
            'table' => [
                'columns' => [
                    'name' => 'Name',
                    'platform' => 'Platform',
                    'feeds_count' => 'Feeds',
                    'token' => 'Token',
                    'verified' => 'Verified',
                ],
            ],
            'actions' => [
                'verify' => [
                    'label' => 'Verify token',
                    'success_title' => 'Token is valid',
                    'success_body' => 'The access token for ":name" is still valid.',
                    'danger_title' => 'Token is invalid',
                    'danger_body' => 'The account ":name" must be reconnected because the token is no longer valid.',
                ],
                'disconnect' => 'Disconnect',
            ],
        ],
        'social_feed' => [
            'navigation_group' => 'Social feeds',
            'navigation_label' => 'Feeds',
            'model_label' => 'Feed',
            'plural_model_label' => 'Feeds',
            'sections' => [
                'basic_settings' => 'Basic settings',
                'display' => 'Display',
                'display_settings' => 'Display settings',
                'filters' => 'Post filtering',
            ],
            'descriptions' => [
                'display_settings' => 'Controls what is displayed for each post and how pagination behaves.',
                'filters' => 'Automatically hides posts that do not meet the conditions.',
            ],
            'fields' => [
                'name' => 'Feed name',
                'slug' => 'Slug',
                'social_account' => 'Connected account',
                'feed_type' => 'Feed type',
                'layout' => 'Layout',
                'posts_count' => 'Total number of posts',
                'cache_ttl' => 'Cache TTL (seconds)',
                'is_active' => 'Active',
                'show_author' => 'Show page / author name',
                'show_posted_at' => 'Show post date',
                'show_page_widget' => 'Show page card above feed',
                'show_engagement' => 'Show reactions and comments',
                'show_permalink' => 'Show original link',
                'content_length' => 'Max post text length',
                'per_page' => 'Posts per page',
                'pagination_type' => 'Pagination',
                'columns' => 'Column count',
                'hide_unavailable' => 'Hide unavailable posts',
                'min_engagement' => 'Minimum engagement count',
                'exclude_types' => 'Exclude post types',
            ],
            'placeholders' => [
                'name' => 'for example footer FB feed',
                'slug' => 'generated from name',
            ],
            'help' => [
                'slug' => 'Used in the Blade component: <x-social-feed slug="..." />',
                'posts_count' => 'How many posts are fetched from the API / stored in the database',
                'cache_ttl' => '3600 = 1 hour, 86400 = 1 day',
                'show_author' => 'Author name and avatar for each post',
                'show_posted_at' => 'Relative publish time (for example 2 hours ago)',
                'show_page_widget' => 'Page name, logo/avatar and follower count',
                'show_engagement' => 'Reaction, comment and share counts',
                'show_permalink' => 'Link to the original post on Facebook',
                'content_length' => 'Number of characters before the text is truncated',
                'per_page' => 'How many posts are shown at once',
                'hide_unavailable' => 'Posts without text and media, and posts whose attachment says the content is unavailable',
                'min_engagement' => 'Hides posts with a lower sum of reactions + comments + shares',
                'exclude_types' => 'Selected types will be hidden',
            ],
            'options' => [
                'feed_type' => [
                    'timeline' => 'Timeline (default)',
                    'feed' => 'Feed (including posts by others)',
                    'visitor_posts' => 'Visitor posts',
                ],
                'pagination_type' => [
                    'none' => 'None - show all',
                    'load_more' => '"Load more" button',
                ],
                'exclude_types' => [
                    'status' => 'Status posts',
                    'link' => 'Links',
                    'photo' => 'Photos',
                    'video' => 'Videos',
                    'event' => 'Events',
                    'offer' => 'Offers',
                ],
            ],
            'table' => [
                'columns' => [
                    'name' => 'Name',
                    'account' => 'Account',
                    'layout' => 'Layout',
                    'posts_count' => 'Posts',
                    'is_active' => 'Active',
                    'updated_at' => 'Updated',
                ],
            ],
            'actions' => [
                'refresh' => [
                    'label' => 'Refresh',
                    'queued_title' => 'Feed refresh queued',
                    'queued_body' => 'Feed ":name" will be refreshed in the background.',
                ],
                'refresh_selected' => [
                    'label' => 'Refresh selected',
                    'queued_title' => 'Selected feeds queued for refresh',
                    'queued_body' => ':count selected feeds will be refreshed in the background.',
                ],
                'refresh_now' => [
                    'label' => 'Refresh feed',
                    'success_title' => 'Feed refreshed',
                    'success_body' => 'Feed ":name" was synchronized successfully.',
                ],
            ],
        ],
    ],
    'pages' => [
        'select_facebook_pages' => [
            'title' => 'Select Facebook pages',
            'section' => 'Choose pages to connect',
            'description' => 'Select one or more Facebook pages you want to connect to miPress.',
            'actions' => [
                'connect' => [
                    'label' => 'Connect selected pages',
                    'modal_heading' => 'Connect selected Facebook pages?',
                    'modal_description_empty' => 'Choose at least one Facebook page to connect.',
                    'modal_description_count' => ':count selected Facebook pages will be connected.',
                ],
                'cancel' => 'Cancel',
            ],
            'notifications' => [
                'no_pages' => [
                    'title' => 'No pages available',
                    'body' => 'First connect a Facebook account and load the pages you want to connect.',
                ],
                'none_selected' => [
                    'title' => 'No pages selected',
                    'body' => 'Select at least one Facebook page before connecting.',
                ],
                'connected' => [
                    'success_title_one' => 'Successfully connected :count page',
                    'success_title_other' => 'Successfully connected :count pages',
                    'success_body' => 'The selected Facebook pages were added to social accounts.',
                ],
            ],
            'labels' => [
                'already_connected_suffix' => '(already connected)',
            ],
        ],
    ],
];
