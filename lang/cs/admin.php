<?php

declare(strict_types=1);

return [
    'crud' => [
        'created' => 'Záznam byl vytvořen',
        'saved' => 'Změny byly uloženy',
    ],
    'enums' => [
        'feed_layout' => [
            'list' => 'Seznam',
            'grid' => 'Mřížka',
            'masonry' => 'Masonry',
            'carousel' => 'Karusel',
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
            'navigation_group' => 'Sociální sítě',
            'navigation_label' => 'Propojené účty',
            'model_label' => 'Propojený účet',
            'plural_model_label' => 'Propojené účty',
            'sections' => [
                'account_information' => 'Informace o účtu',
                'token_status' => 'Stav tokenu',
            ],
            'fields' => [
                'platform' => 'Platforma',
                'name' => 'Název účtu / stránky',
                'username' => 'Uživatelské jméno',
                'platform_account_id' => 'ID na platformě',
                'status' => 'Stav',
                'expires_at' => 'Vyprší',
                'last_verified' => 'Poslední ověření',
            ],
            'states' => [
                'token_expired' => 'Token vypršel',
                'token_expiring' => 'Token brzy vyprší',
                'token_valid' => 'Token platný',
                'no_expiration' => 'Bez expirace',
                'not_verified' => 'Neověřeno',
            ],
            'table' => [
                'columns' => [
                    'name' => 'Název',
                    'platform' => 'Platforma',
                    'feeds_count' => 'Feedů',
                    'token' => 'Token',
                    'verified' => 'Ověřeno',
                ],
            ],
            'actions' => [
                'verify' => [
                    'label' => 'Ověřit token',
                    'success_title' => 'Token je platný',
                    'success_body' => 'Přístupový token účtu ":name" je stále platný.',
                    'danger_title' => 'Token je neplatný',
                    'danger_body' => 'Účet ":name" je potřeba znovu připojit, protože token už není platný.',
                ],
                'disconnect' => 'Odpojit',
                'connect_label' => 'Připojit :platform',
            ],
        ],
        'social_feed' => [
            'navigation_group' => 'Sociální sítě',
            'navigation_label' => 'Feedy',
            'model_label' => 'Feed',
            'plural_model_label' => 'Feedy',
            'sections' => [
                'basic_settings' => 'Základní nastavení',
                'display' => 'Zobrazení',
                'display_settings' => 'Nastavení zobrazení',
                'filters' => 'Filtrování příspěvků',
            ],
            'descriptions' => [
                'display_settings' => 'Ovládá, co se zobrazí u jednotlivých příspěvků a jak se stránkují.',
                'filters' => 'Automaticky skryje příspěvky, které nesplňují podmínky.',
            ],
            'fields' => [
                'name' => 'Název feedu',
                'slug' => 'Slug',
                'social_account' => 'Propojený účet',
                'feed_type' => 'Typ feedu',
                'layout' => 'Layout',
                'posts_count' => 'Celkový počet příspěvků',
                'cache_ttl' => 'Cache TTL (sekundy)',
                'is_active' => 'Aktivní',
                'show_author' => 'Zobrazit název stránky / autora',
                'show_posted_at' => 'Zobrazit datum příspěvku',
                'show_page_widget' => 'Zobrazit kartičku stránky nad feedem',
                'show_engagement' => 'Zobrazit reakce a komentáře',
                'show_permalink' => 'Zobrazit odkaz na originál',
                'content_length' => 'Max. délka textu příspěvku',
                'per_page' => 'Příspěvků na stránku',
                'pagination_type' => 'Stránkování',
                'columns' => 'Počet sloupců',
                'hide_unavailable' => 'Skrýt nedostupné příspěvky',
                'min_engagement' => 'Minimální počet interakcí',
                'exclude_types' => 'Vyloučit typy příspěvků',
            ],
            'placeholders' => [
                'name' => 'např. FB feed v patičce',
                'slug' => 'automaticky z názvu',
            ],
            'help' => [
                'slug' => 'Použije se v Blade komponentě: <x-social-feed slug="..." />',
                'posts_count' => 'Kolik příspěvků se stáhne z API / uloží do DB',
                'cache_ttl' => '3600 = 1 hodina, 86400 = 1 den',
                'show_author' => 'Název a avatar autora u každého příspěvku',
                'show_posted_at' => 'Relativní čas publikace (např. před 2 hodinami)',
                'show_page_widget' => 'Název stránky, logo/avatar a počet sledujících',
                'show_engagement' => 'Počet reakcí, komentářů a sdílení',
                'show_permalink' => 'Odkaz "Zobrazit na Facebooku"',
                'content_length' => 'Počet znaků, poté se ořízne',
                'per_page' => 'Kolik příspěvků se zobrazí najednou',
                'hide_unavailable' => 'Příspěvky bez textu i bez médií a příspěvky s attachmentem typu "Obsah teď není dostupný"',
                'min_engagement' => 'Skryje příspěvky s menším součtem reakcí + komentářů + sdílení',
                'exclude_types' => 'Vybrané typy se nezobrazí',
            ],
            'options' => [
                'feed_type' => [
                    'timeline' => 'Časová osa (výchozí)',
                    'feed' => 'Feed (vč. příspěvků ostatních)',
                    'visitor_posts' => 'Příspěvky návštěvníků',
                ],
                'pagination_type' => [
                    'none' => 'Žádné - zobrazit vše',
                    'load_more' => 'Tlačítko "Načíst více"',
                ],
                'exclude_types' => [
                    'status' => 'Stavové zprávy',
                    'link' => 'Odkazy',
                    'photo' => 'Fotky',
                    'video' => 'Videa',
                    'event' => 'Události',
                    'offer' => 'Nabídky',
                ],
            ],
            'table' => [
                'columns' => [
                    'name' => 'Název',
                    'account' => 'Účet',
                    'layout' => 'Layout',
                    'posts_count' => 'Příspěvků',
                    'is_active' => 'Aktivní',
                    'updated_at' => 'Aktualizováno',
                ],
            ],
            'actions' => [
                'refresh' => [
                    'label' => 'Obnovit',
                    'queued_title' => 'Obnovení feedu bylo zařazeno do fronty',
                    'queued_body' => 'Feed ":name" se obnoví na pozadí.',
                ],
                'refresh_selected' => [
                    'label' => 'Obnovit vybrané',
                    'queued_title' => 'Obnovení vybraných feedů bylo zařazeno do fronty',
                    'queued_body' => 'Na pozadí bude obnoveno :count vybraných feedů.',
                ],
                'refresh_now' => [
                    'label' => 'Obnovit feed',
                    'success_title' => 'Feed byl obnoven',
                    'success_body' => 'Feed ":name" byl úspěšně synchronizován.',
                ],
            ],
        ],
    ],
    'pages' => [
        'select_facebook_pages' => [
            'title' => 'Výběr Facebook stránek',
            'section' => 'Vyberte stránky k propojení',
            'description' => 'Vyberte jednu nebo více Facebook stránek, které chcete připojit k miPress.',
            'actions' => [
                'connect' => [
                    'label' => 'Připojit vybrané stránky',
                    'modal_heading' => 'Připojit vybrané Facebook stránky?',
                    'modal_description_empty' => 'Vyberte alespoň jednu Facebook stránku, kterou chcete propojit.',
                    'modal_description_count' => 'Bude propojeno :count vybraných Facebook stránek.',
                ],
                'cancel' => 'Zrušit',
            ],
            'notifications' => [
                'no_pages' => [
                    'title' => 'Žádné stránky k výběru',
                    'body' => 'Nejprve připojte Facebook účet a načtěte stránky, které chcete propojit.',
                ],
                'none_selected' => [
                    'title' => 'Nevybrali jste žádné stránky',
                    'body' => 'Před propojením označte alespoň jednu Facebook stránku.',
                ],
                'connected' => [
                    'success_title_one' => 'Úspěšně propojena :count stránka',
                    'success_title_other' => 'Úspěšně propojeno :count stránek',
                    'success_body' => 'Vybrané Facebook stránky byly přidány mezi sociální účty.',
                ],
            ],
            'labels' => [
                'already_connected_suffix' => '(již propojeno)',
            ],
        ],
    ],
];
