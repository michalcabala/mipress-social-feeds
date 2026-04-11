<?php

declare(strict_types=1);

namespace MiPress\SocialFeeds\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use MiPress\SocialFeeds\Enums\SocialPlatform;
use MiPress\SocialFeeds\Models\SocialAccount;
use MiPress\SocialFeeds\Services\SocialFeedManager;

class SelectFacebookPages extends Page
{
    protected string $view = 'social-feeds::filament.pages.select-facebook-pages';

    protected static string|\BackedEnum|null $navigationIcon = 'fal-circle-check';

    protected static ?string $title = 'Výběr Facebook stránek';

    protected static ?string $slug = 'select-facebook-pages';

    protected static bool $shouldRegisterNavigation = false;

    /** @var array<string> */
    public array $selectedPages = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermissionTo('social_account.create') === true;
    }

    public function mount(): void
    {
        $pages = $this->getCachedPages();

        if (empty($pages)) {
            Notification::make()
                ->title('Žádné stránky k výběru')
                ->body('Nejprve připojte Facebook účet.')
                ->warning()
                ->send();

            $this->redirect(
                route('filament.admin.resources.social-accounts.index')
            );
        }
    }

    public function content(Schema $schema): Schema
    {
        $pages = $this->getCachedPages();
        $options = collect($pages['pages'] ?? [])->mapWithKeys(fn (array $page) => [
            $page['id'] => $this->formatPageLabel($page),
        ])->all();

        return $schema->components([
            Section::make('Vyberte stránky k propojení')
                ->description('Vyberte jednu nebo více Facebook stránek, které chcete připojit k miPress.')
                ->icon('fal-circle-check')
                ->schema([
                    CheckboxList::make('selectedPages')
                        ->label('')
                        ->options($options)
                        ->columns(1)
                        ->bulkToggleable(),
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('connect')
                ->label('Připojit vybrané stránky')
                ->icon('fal-link')
                ->action(fn () => $this->connectSelected())
                ->requiresConfirmation()
                ->modalHeading('Potvrzení připojení')
                ->modalDescription('Opravdu chcete připojit vybrané Facebook stránky?')
                ->color('primary'),

            Action::make('cancel')
                ->label('Zrušit')
                ->icon('fal-xmark')
                ->url(route('filament.admin.resources.social-accounts.index'))
                ->color('gray'),
        ];
    }

    public function connectSelected(): void
    {
        if (empty($this->selectedPages)) {
            Notification::make()
                ->title('Nevybrali jste žádné stránky')
                ->warning()
                ->send();

            return;
        }

        $cached = $this->getCachedPages();
        $pages = collect($cached['pages'] ?? []);
        $userId = $cached['connected_by'] ?? auth()->id();

        $count = 0;
        foreach ($this->selectedPages as $pageId) {
            $page = $pages->firstWhere('id', $pageId);

            if (! $page) {
                continue;
            }

            $account = SocialAccount::updateOrCreate(
                [
                    'platform' => SocialPlatform::Facebook,
                    'platform_account_id' => $page['id'],
                ],
                [
                    'name' => $page['name'],
                    'username' => $page['category'] ?? null,
                    'access_token' => Crypt::encryptString($page['access_token']),
                    'refresh_token' => null,
                    'token_expires_at' => null,
                    'avatar_url' => $page['picture']['data']['url'] ?? null,
                    'meta' => [
                        'page_id' => $page['id'],
                        'category' => $page['category'] ?? null,
                        'link' => $page['link'] ?? null,
                        'fan_count' => $page['fan_count'] ?? null,
                        'user_id' => $cached['user_id'] ?? null,
                        'user_name' => $cached['user_name'] ?? null,
                    ],
                    'connected_by' => $userId,
                ]
            );

            try {
                $provider = app(SocialFeedManager::class)->resolve(SocialPlatform::Facebook);
                $profile = $provider->fetchProfile($account);

                if (! empty($profile)) {
                    $meta = is_array($account->meta) ? $account->meta : [];
                    $account->update([
                        'name' => $profile['name'] ?? $account->name,
                        'avatar_url' => data_get($profile, 'picture.data.url', $account->avatar_url),
                        'meta' => [
                            ...$meta,
                            'fan_count' => $profile['fan_count'] ?? data_get($meta, 'fan_count'),
                            'about' => $profile['about'] ?? data_get($meta, 'about'),
                            'link' => $profile['link'] ?? data_get($meta, 'link'),
                            'cover_url' => data_get($profile, 'cover.source', data_get($meta, 'cover_url')),
                        ],
                    ]);
                }
            } catch (\Throwable) {
                // The page connection should succeed even if profile enrichment fails.
            }
            $count++;
        }

        // Clear the cached pages data
        Cache::forget($this->getCacheKey());

        Notification::make()
            ->title("Úspěšně propojeno {$count} ".($count === 1 ? 'stránka' : 'stránek'))
            ->success()
            ->send();

        $this->redirect(
            route('filament.admin.resources.social-accounts.index')
        );
    }

    private function getCachedPages(): array
    {
        return Cache::get($this->getCacheKey(), []);
    }

    private function getCacheKey(): string
    {
        return 'social-feeds:facebook-pages:'.auth()->id();
    }

    private function formatPageLabel(array $page): string
    {
        $label = $page['name'];

        if (! empty($page['category'])) {
            $label .= " ({$page['category']})";
        }

        $existing = SocialAccount::where('platform', SocialPlatform::Facebook)
            ->where('platform_account_id', $page['id'])
            ->exists();

        if ($existing) {
            $label .= ' ✓ již propojeno';
        }

        return $label;
    }
}
