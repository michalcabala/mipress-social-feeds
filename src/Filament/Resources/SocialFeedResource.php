<?php

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

    protected static string|\UnitEnum|null $navigationGroup = 'Sociální sítě';

    protected static ?string $navigationLabel = 'Feedy';

    protected static ?string $modelLabel = 'Feed';

    protected static ?string $pluralModelLabel = 'Feedy';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Základní nastavení')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Název feedu')
                        ->placeholder('např. FB feed v patičce')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->placeholder('automaticky z názvu')
                        ->unique(ignoreRecord: true)
                        ->helperText('Použije se v Blade komponentě: <x-social-feed slug="..." />'),

                    Forms\Components\Select::make('social_account_id')
                        ->label('Propojený účet')
                        ->options(
                            SocialAccount::all()->mapWithKeys(fn (SocialAccount $a) => [
                                $a->id => "{$a->platform->label()}: {$a->name}",
                            ])
                        )
                        ->required()
                        ->searchable(),

                    Forms\Components\Select::make('feed_type')
                        ->label('Typ feedu')
                        ->options([
                            'timeline' => 'Časová osa (výchozí)',
                            'feed' => 'Feed (vč. příspěvků ostatních)',
                            'visitor_posts' => 'Příspěvky návštěvníků',
                        ])
                        ->default('timeline'),
                ])->columns(2),

            Section::make('Zobrazení')
                ->schema([
                    Forms\Components\Select::make('layout')
                        ->label('Layout')
                        ->options(
                            collect(FeedLayout::cases())
                                ->mapWithKeys(fn (FeedLayout $l) => [$l->value => $l->label()])
                        )
                        ->default('list')
                        ->required()
                        ->live(),

                    Forms\Components\TextInput::make('posts_count')
                        ->label('Celkový počet příspěvků')
                        ->numeric()
                        ->default(10)
                        ->minValue(1)
                        ->maxValue(100)
                        ->helperText('Kolik příspěvků se stáhne z API / uloží do DB'),

                    Forms\Components\TextInput::make('cache_ttl')
                        ->label('Cache TTL (sekundy)')
                        ->numeric()
                        ->default(fn () => config('social-feeds.cache.default_ttl', 3600))
                        ->helperText('3600 = 1 hodina, 86400 = 1 den'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktivní')
                        ->default(true),
                ])->columns(2),

            Section::make('Nastavení zobrazení')
                ->description('Ovládá, co se zobrazí u jednotlivých příspěvků a jak se stránkují.')
                ->schema([
                    Forms\Components\Toggle::make('settings.show_author')
                        ->label('Zobrazit název stránky / autora')
                        ->helperText('Název a avatar autora u každého příspěvku')
                        ->default(true),

                    Forms\Components\Toggle::make('settings.show_posted_at')
                        ->label('Zobrazit datum příspěvku')
                        ->helperText('Relativní čas publikace (např. před 2 hodinami)')
                        ->default(true),

                    Forms\Components\Toggle::make('settings.show_page_widget')
                        ->label('Zobrazit kartičku stránky nad feedem')
                        ->helperText('Název stránky, logo/avatar a počet sledujících')
                        ->default(true),

                    Forms\Components\Toggle::make('settings.show_engagement')
                        ->label('Zobrazit reakce a komentáře')
                        ->helperText('Počet reakcí, komentářů a sdílení')
                        ->default(true),

                    Forms\Components\Toggle::make('settings.show_permalink')
                        ->label('Zobrazit odkaz na originál')
                        ->helperText('Odkaz „Zobrazit na Facebooku →"')
                        ->default(true),

                    Forms\Components\TextInput::make('settings.content_length')
                        ->label('Max. délka textu příspěvku')
                        ->numeric()
                        ->default(300)
                        ->minValue(50)
                        ->maxValue(2000)
                        ->helperText('Počet znaků, poté se ořízne'),

                    Forms\Components\TextInput::make('settings.per_page')
                        ->label('Příspěvků na stránku')
                        ->numeric()
                        ->default(5)
                        ->minValue(1)
                        ->maxValue(50)
                        ->helperText('Kolik příspěvků se zobrazí najednou')
                        ->visible(fn (Get $get): bool => ($get('settings.pagination_type') ?? 'none') !== 'none'),

                    Forms\Components\Select::make('settings.pagination_type')
                        ->label('Stránkování')
                        ->options([
                            'none' => 'Žádné — zobrazit vše',
                            'load_more' => 'Tlačítko „Načíst více"',
                        ])
                        ->default('none')
                        ->live(),

                    Forms\Components\TextInput::make('settings.columns')
                        ->label('Počet sloupců')
                        ->numeric()
                        ->default(3)
                        ->minValue(2)
                        ->maxValue(6)
                        ->visible(fn (Get $get): bool => in_array($get('layout'), ['grid', 'masonry'])),
                ])->columns(2),

            Section::make('Filtrování příspěvků')
                ->description('Automaticky skryje příspěvky, které nesplňují podmínky.')
                ->schema([
                    Forms\Components\Toggle::make('filter_settings.hide_unavailable')
                        ->label('Skrýt nedostupné příspěvky')
                        ->helperText('Příspěvky bez textu i bez médií a příspěvky s attachmentem typu „Obsah teď není dostupný"')
                        ->default(true),

                    Forms\Components\TextInput::make('filter_settings.min_engagement')
                        ->label('Minimální počet interakcí')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Skryje příspěvky s menším součtem reakcí + komentářů + sdílení'),

                    Forms\Components\Select::make('filter_settings.exclude_types')
                        ->label('Vyloučit typy příspěvků')
                        ->multiple()
                        ->options([
                            'status' => 'Stavové zprávy',
                            'link' => 'Odkazy',
                            'photo' => 'Fotky',
                            'video' => 'Videa',
                            'event' => 'Události',
                            'offer' => 'Nabídky',
                        ])
                        ->helperText('Vybrané typy se nezobrazí'),
                ])->columns(2)
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Název')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('account.name')
                    ->label('Účet')
                    ->description(fn (SocialFeed $record) => $record->account?->platform->label()),

                Tables\Columns\TextColumn::make('layout')
                    ->label('Layout')
                    ->formatStateUsing(fn (FeedLayout $state) => $state->label())
                    ->badge(),

                Tables\Columns\TextColumn::make('posts_count')
                    ->label('Příspěvků')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktivní')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Aktualizováno')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('refresh')
                        ->label('Obnovit')
                        ->icon('fal-arrows-rotate')
                        ->action(function (SocialFeed $record) {
                            RefreshFeedJob::dispatch($record->id);
                            Notification::make()
                                ->title('Obnovení feedu bylo zařazeno do fronty')
                                ->body('Feed "'.$record->name.'" se obnoví na pozadí.')
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
                        ->label('Obnovit vybrané')
                        ->icon('fal-arrows-rotate')
                        ->action(function (Collection $records) {
                            $records->each(fn (SocialFeed $feed) => RefreshFeedJob::dispatch($feed->id));
                            Notification::make()
                                ->title('Obnovení vybraných feedů bylo zařazeno do fronty')
                                ->body('Na pozadí bude obnoveno '.$records->count().' vybraných feedů.')
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
