<?php

declare(strict_types=1);

namespace MiPress\SocialFeeds\Filament\Resources;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use MiPress\SocialFeeds\Enums\SocialPlatform;
use MiPress\SocialFeeds\Filament\Resources\SocialAccountResource\Pages\EditSocialAccount;
use MiPress\SocialFeeds\Filament\Resources\SocialAccountResource\Pages\ListSocialAccounts;
use MiPress\SocialFeeds\Models\SocialAccount;
use MiPress\SocialFeeds\Services\SocialFeedManager;

class SocialAccountResource extends Resource
{
    protected static ?string $model = SocialAccount::class;

    protected static string|\BackedEnum|null $navigationIcon = 'fal-share-nodes';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return __('social-feeds::admin.resources.social_account.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('social-feeds::admin.resources.social_account.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('social-feeds::admin.resources.social_account.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('social-feeds::admin.resources.social_account.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('social-feeds::admin.resources.social_account.sections.account_information'))
                ->schema([
                    Forms\Components\Select::make('platform')
                        ->label(__('social-feeds::admin.resources.social_account.fields.platform'))
                        ->options(
                            collect(SocialPlatform::enabled())
                                ->mapWithKeys(fn (SocialPlatform $p) => [$p->value => $p->label()])
                        )
                        ->required()
                        ->disabled(fn (?SocialAccount $record) => $record !== null),

                    Forms\Components\TextInput::make('name')
                        ->label(__('social-feeds::admin.resources.social_account.fields.name'))
                        ->required()
                        ->disabled(),

                    Forms\Components\TextInput::make('username')
                        ->label(__('social-feeds::admin.resources.social_account.fields.username'))
                        ->disabled(),

                    Forms\Components\TextInput::make('platform_account_id')
                        ->label(__('social-feeds::admin.resources.social_account.fields.platform_account_id'))
                        ->disabled(),
                ]),

            Section::make(__('social-feeds::admin.resources.social_account.sections.token_status'))
                ->schema([
                    TextEntry::make('token_status')
                        ->label(__('social-feeds::admin.resources.social_account.fields.status'))
                        ->state(function (?SocialAccount $record) {
                            if (! $record) {
                                return __('social-feeds::admin.resources.social_account.states.not_verified');
                            }
                            if ($record->isTokenExpired()) {
                                return __('social-feeds::admin.resources.social_account.states.token_expired');
                            }
                            if ($record->isTokenExpiringSoon()) {
                                return __('social-feeds::admin.resources.social_account.states.token_expiring');
                            }

                            return __('social-feeds::admin.resources.social_account.states.token_valid');
                        }),

                    TextEntry::make('token_expires_at')
                        ->label(__('social-feeds::admin.resources.social_account.fields.expires_at'))
                        ->state(fn (?SocialAccount $record) => $record?->token_expires_at?->format('d.m.Y H:i') ?? __('social-feeds::admin.resources.social_account.states.no_expiration')),

                    TextEntry::make('last_verified_at')
                        ->label(__('social-feeds::admin.resources.social_account.fields.last_verified'))
                        ->state(fn (?SocialAccount $record) => $record?->last_verified_at?->diffForHumans() ?? __('social-feeds::admin.resources.social_account.states.not_verified')),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('social-feeds::admin.resources.social_account.table.columns.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('platform')
                    ->label(__('social-feeds::admin.resources.social_account.table.columns.platform'))
                    ->badge()
                    ->formatStateUsing(fn (SocialPlatform $state) => $state->label())
                    ->color(fn (SocialPlatform $state) => match ($state) {
                        SocialPlatform::Facebook => 'info',
                        SocialPlatform::Instagram => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('feeds_count')
                    ->label(__('social-feeds::admin.resources.social_account.table.columns.feeds_count'))
                    ->counts('feeds')
                    ->sortable(),

                Tables\Columns\IconColumn::make('token_valid')
                    ->label(__('social-feeds::admin.resources.social_account.table.columns.token'))
                    ->state(fn (SocialAccount $record) => ! $record->isTokenExpired())
                    ->boolean(),

                Tables\Columns\TextColumn::make('last_verified_at')
                    ->label(__('social-feeds::admin.resources.social_account.table.columns.verified'))
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('verify')
                        ->label(__('social-feeds::admin.resources.social_account.actions.verify.label'))
                        ->icon('fal-shield-check')
                        ->action(function (SocialAccount $record) {
                            $manager = app(SocialFeedManager::class);
                            $provider = $manager->resolve($record->platform);
                            $valid = $provider->validateToken($record);

                            if ($valid) {
                                Notification::make()
                                    ->title(__('social-feeds::admin.resources.social_account.actions.verify.success_title'))
                                    ->body(__('social-feeds::admin.resources.social_account.actions.verify.success_body', ['name' => $record->name]))
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title(__('social-feeds::admin.resources.social_account.actions.verify.danger_title'))
                                    ->body(__('social-feeds::admin.resources.social_account.actions.verify.danger_body', ['name' => $record->name]))
                                    ->danger()
                                    ->send();
                            }
                        }),
                    EditAction::make(),
                    DeleteAction::make()
                        ->label(__('social-feeds::admin.resources.social_account.actions.disconnect')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSocialAccounts::route('/'),
            'edit' => EditSocialAccount::route('/{record}/edit'),
        ];
    }
}
