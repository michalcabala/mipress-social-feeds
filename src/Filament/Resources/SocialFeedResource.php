<?php

declare(strict_types=1);

namespace MiPress\SocialFeeds\Filament\Resources;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use MiPress\SocialFeeds\Enums\FeedLayout;
use MiPress\SocialFeeds\Filament\Resources\SocialFeedResource\Pages\CreateSocialFeed;
use MiPress\SocialFeeds\Filament\Resources\SocialFeedResource\Pages\EditSocialFeed;
use MiPress\SocialFeeds\Filament\Resources\SocialFeedResource\Pages\ListSocialFeeds;
use MiPress\SocialFeeds\Jobs\RefreshFeedJob;
use MiPress\SocialFeeds\Models\SocialAccount;
use MiPress\SocialFeeds\Models\SocialFeed;

class SocialFeedResource extends Resource
{
    protected static ?string $model = SocialFeed::class;

    protected static string|\BackedEnum|null $navigationIcon = 'fal-rss';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return __('social-feeds::admin.resources.social_feed.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('social-feeds::admin.resources.social_feed.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('social-feeds::admin.resources.social_feed.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('social-feeds::admin.resources.social_feed.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('social-feeds::admin.resources.social_feed.sections.basic_settings'))
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.name'))
                        ->placeholder(__('social-feeds::admin.resources.social_feed.placeholders.name'))
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('slug')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.slug'))
                        ->placeholder(__('social-feeds::admin.resources.social_feed.placeholders.slug'))
                        ->unique(ignoreRecord: true)
                        ->helperText(__('social-feeds::admin.resources.social_feed.help.slug')),

                    Forms\Components\Select::make('social_account_id')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.social_account'))
                        ->options(
                            SocialAccount::all()->mapWithKeys(fn (SocialAccount $a) => [
                                $a->id => "{$a->platform->label()}: {$a->name}",
                            ])
                        )
                        ->required()
                        ->searchable(),

                    Forms\Components\Select::make('feed_type')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.feed_type'))
                        ->options([
                            'timeline' => __('social-feeds::admin.resources.social_feed.options.feed_type.timeline'),
                            'feed' => __('social-feeds::admin.resources.social_feed.options.feed_type.feed'),
                            'visitor_posts' => __('social-feeds::admin.resources.social_feed.options.feed_type.visitor_posts'),
                        ])
                        ->default('timeline'),
                ])->columns(2),

            Section::make(__('social-feeds::admin.resources.social_feed.sections.display'))
                ->schema([
                    Forms\Components\Select::make('layout')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.layout'))
                        ->options(
                            collect(FeedLayout::cases())
                                ->mapWithKeys(fn (FeedLayout $l) => [$l->value => $l->label()])
                        )
                        ->default('list')
                        ->required()
                        ->live(),

                    Forms\Components\TextInput::make('posts_count')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.posts_count'))
                        ->numeric()
                        ->default(10)
                        ->minValue(1)
                        ->maxValue(100)
                        ->helperText(__('social-feeds::admin.resources.social_feed.help.posts_count')),

                    Forms\Components\TextInput::make('cache_ttl')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.cache_ttl'))
                        ->numeric()
                        ->default(fn () => config('social-feeds.cache.default_ttl', 3600))
                        ->helperText(__('social-feeds::admin.resources.social_feed.help.cache_ttl')),

                    Forms\Components\Toggle::make('is_active')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.is_active'))
                        ->default(true),
                ])->columns(2),

            Section::make(__('social-feeds::admin.resources.social_feed.sections.display_settings'))
                ->description(__('social-feeds::admin.resources.social_feed.descriptions.display_settings'))
                ->schema([
                    Forms\Components\Toggle::make('settings.show_author')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.show_author'))
                        ->helperText(__('social-feeds::admin.resources.social_feed.help.show_author'))
                        ->default(true),

                    Forms\Components\Toggle::make('settings.show_posted_at')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.show_posted_at'))
                        ->helperText(__('social-feeds::admin.resources.social_feed.help.show_posted_at'))
                        ->default(true),

                    Forms\Components\Toggle::make('settings.show_page_widget')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.show_page_widget'))
                        ->helperText(__('social-feeds::admin.resources.social_feed.help.show_page_widget'))
                        ->default(true),

                    Forms\Components\Toggle::make('settings.show_engagement')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.show_engagement'))
                        ->helperText(__('social-feeds::admin.resources.social_feed.help.show_engagement'))
                        ->default(true),

                    Forms\Components\Toggle::make('settings.show_permalink')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.show_permalink'))
                        ->helperText(__('social-feeds::admin.resources.social_feed.help.show_permalink'))
                        ->default(true),

                    Forms\Components\TextInput::make('settings.content_length')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.content_length'))
                        ->numeric()
                        ->default(300)
                        ->minValue(50)
                        ->maxValue(2000)
                        ->helperText(__('social-feeds::admin.resources.social_feed.help.content_length')),

                    Forms\Components\TextInput::make('settings.per_page')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.per_page'))
                        ->numeric()
                        ->default(5)
                        ->minValue(1)
                        ->maxValue(50)
                        ->helperText(__('social-feeds::admin.resources.social_feed.help.per_page'))
                        ->visible(fn (Get $get): bool => ($get('settings.pagination_type') ?? 'none') !== 'none'),

                    Forms\Components\Select::make('settings.pagination_type')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.pagination_type'))
                        ->options([
                            'none' => __('social-feeds::admin.resources.social_feed.options.pagination_type.none'),
                            'load_more' => __('social-feeds::admin.resources.social_feed.options.pagination_type.load_more'),
                        ])
                        ->default('none')
                        ->live(),

                    Forms\Components\TextInput::make('settings.columns')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.columns'))
                        ->numeric()
                        ->default(3)
                        ->minValue(2)
                        ->maxValue(6)
                        ->visible(fn (Get $get): bool => in_array($get('layout'), ['grid', 'masonry'])),
                ])->columns(2),

            Section::make(__('social-feeds::admin.resources.social_feed.sections.filters'))
                ->description(__('social-feeds::admin.resources.social_feed.descriptions.filters'))
                ->schema([
                    Forms\Components\Toggle::make('filter_settings.hide_unavailable')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.hide_unavailable'))
                        ->helperText(__('social-feeds::admin.resources.social_feed.help.hide_unavailable'))
                        ->default(true),

                    Forms\Components\TextInput::make('filter_settings.min_engagement')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.min_engagement'))
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText(__('social-feeds::admin.resources.social_feed.help.min_engagement')),

                    Forms\Components\Select::make('filter_settings.exclude_types')
                        ->label(__('social-feeds::admin.resources.social_feed.fields.exclude_types'))
                        ->multiple()
                        ->options([
                            'status' => __('social-feeds::admin.resources.social_feed.options.exclude_types.status'),
                            'link' => __('social-feeds::admin.resources.social_feed.options.exclude_types.link'),
                            'photo' => __('social-feeds::admin.resources.social_feed.options.exclude_types.photo'),
                            'video' => __('social-feeds::admin.resources.social_feed.options.exclude_types.video'),
                            'event' => __('social-feeds::admin.resources.social_feed.options.exclude_types.event'),
                            'offer' => __('social-feeds::admin.resources.social_feed.options.exclude_types.offer'),
                        ])
                        ->helperText(__('social-feeds::admin.resources.social_feed.help.exclude_types')),
                ])->columns(2)
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('social-feeds::admin.resources.social_feed.table.columns.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('account.name')
                    ->label(__('social-feeds::admin.resources.social_feed.table.columns.account'))
                    ->description(fn (SocialFeed $record) => $record->account?->platform->label()),

                Tables\Columns\TextColumn::make('layout')
                    ->label(__('social-feeds::admin.resources.social_feed.table.columns.layout'))
                    ->formatStateUsing(fn (FeedLayout $state) => $state->label())
                    ->badge(),

                Tables\Columns\TextColumn::make('posts_count')
                    ->label(__('social-feeds::admin.resources.social_feed.table.columns.posts_count'))
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('social-feeds::admin.resources.social_feed.table.columns.is_active'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('social-feeds::admin.resources.social_feed.table.columns.updated_at'))
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('refresh')
                        ->label(__('social-feeds::admin.resources.social_feed.actions.refresh.label'))
                        ->icon('fal-arrows-rotate')
                        ->action(function (SocialFeed $record) {
                            RefreshFeedJob::dispatch($record->id);
                            Notification::make()
                                ->title(__('social-feeds::admin.resources.social_feed.actions.refresh.queued_title'))
                                ->body(__('social-feeds::admin.resources.social_feed.actions.refresh.queued_body', ['name' => $record->name]))
                                ->success()
                                ->send();
                        }),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('refresh_all')
                        ->label(__('social-feeds::admin.resources.social_feed.actions.refresh_selected.label'))
                        ->icon('fal-arrows-rotate')
                        ->action(function (Collection $records) {
                            $records->each(fn (SocialFeed $feed) => RefreshFeedJob::dispatch($feed->id));
                            Notification::make()
                                ->title(__('social-feeds::admin.resources.social_feed.actions.refresh_selected.queued_title'))
                                ->body(__('social-feeds::admin.resources.social_feed.actions.refresh_selected.queued_body', ['count' => $records->count()]))
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSocialFeeds::route('/'),
            'create' => CreateSocialFeed::route('/create'),
            'edit' => EditSocialFeed::route('/{record}/edit'),
        ];
    }
}
