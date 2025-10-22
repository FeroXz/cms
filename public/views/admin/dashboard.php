<?php
    $quickLinks = [];
    $quickLinks[] = [
        'label' => 'Übersicht',
        'href' => BASE_URL . '/index.php?route=admin/dashboard',
        'active' => $currentRoute === 'admin/dashboard',
        'description' => 'Alle Kennzahlen im Blick behalten',
        'icon' => 'Gauge',
    ];
    $quickLinks[] = [
        'label' => 'Tiere',
        'href' => BASE_URL . '/index.php?route=admin/animals',
        'active' => $currentRoute === 'admin/animals',
        'description' => 'Bestand, Stammdaten & Pflege',
        'icon' => 'Paw',
    ];

    if (is_authorized('can_manage_animals')) {
        $quickLinks[] = [
            'label' => 'Zuchtplanung',
            'href' => BASE_URL . '/index.php?route=admin/breeding',
            'active' => $currentRoute === 'admin/breeding',
            'description' => 'Paare & Brutsaisons organisieren',
            'icon' => 'Spark',
        ];
    }

    $quickLinks[] = [
        'label' => 'Tierabgabe',
        'href' => BASE_URL . '/index.php?route=admin/adoption',
        'active' => $currentRoute === 'admin/adoption',
        'description' => 'Inserate veröffentlichen & pflegen',
        'icon' => 'Repeat',
    ];

    $quickLinks[] = [
        'label' => 'Anfragen',
        'href' => BASE_URL . '/index.php?route=admin/inquiries',
        'active' => $currentRoute === 'admin/inquiries',
        'description' => 'Kommunikation bündeln & beantworten',
        'icon' => 'Inbox',
    ];

    if (is_authorized('can_manage_settings')) {
        $quickLinks = array_merge($quickLinks, [
            [
                'label' => 'Seiten',
                'href' => BASE_URL . '/index.php?route=admin/pages',
                'active' => $currentRoute === 'admin/pages',
                'description' => 'Inhalte verwalten & strukturieren',
                'icon' => 'Notebook',
            ],
            [
                'label' => 'Neuigkeiten',
                'href' => BASE_URL . '/index.php?route=admin/news',
                'active' => $currentRoute === 'admin/news',
                'description' => 'Update-Beiträge verfassen',
                'icon' => 'Megaphone',
            ],
            [
                'label' => 'Pflegeleitfaden',
                'href' => BASE_URL . '/index.php?route=admin/care',
                'active' => $currentRoute === 'admin/care',
                'description' => 'Wissensartikel kuratieren',
                'icon' => 'Leaf',
            ],
            [
                'label' => 'Genetik',
                'href' => BASE_URL . '/index.php?route=admin/genetics',
                'active' => $currentRoute === 'admin/genetics',
                'description' => 'Gene & Kombis dokumentieren',
                'icon' => 'Helix',
            ],
            [
                'label' => 'Galerie',
                'href' => BASE_URL . '/index.php?route=admin/gallery',
                'active' => $currentRoute === 'admin/gallery',
                'description' => 'Bilder kuratieren & sortieren',
                'icon' => 'Gallery',
            ],
            [
                'label' => 'Einstellungen',
                'href' => BASE_URL . '/index.php?route=admin/settings',
                'active' => $currentRoute === 'admin/settings',
                'description' => 'Systemweite Parameter justieren',
                'icon' => 'Sliders',
            ],
            [
                'label' => 'Texte',
                'href' => BASE_URL . '/index.php?route=admin/content',
                'active' => $currentRoute === 'admin/content',
                'description' => 'Texte zentral pflegen',
                'icon' => 'Feather',
            ],
        ]);
    }

    $currentUser = current_user();
    if (isset($currentUser['role']) && $currentUser['role'] === 'admin') {
        $quickLinks[] = [
            'label' => 'Benutzer',
            'href' => BASE_URL . '/index.php?route=admin/users',
            'active' => $currentRoute === 'admin/users',
            'description' => 'Zugänge & Rollen verwalten',
            'icon' => 'Users',
        ];
    }

    $metricCards = [
        [
            'label' => 'Aktive Tiere',
            'value' => count($animals),
            'delta' => '+3 diese Woche',
            'accentFrom' => '#34d39933',
            'accentTo' => '#05966933',
        ],
        [
            'label' => 'Abgabe-Inserate',
            'value' => count($listings),
            'delta' => '',
            'accentFrom' => '#22d3ee33',
            'accentTo' => '#0ea5e933',
        ],
        [
            'label' => 'Neue Anfragen',
            'value' => count($inquiries),
            'delta' => '',
            'accentFrom' => '#fbbf2433',
            'accentTo' => '#f9731633',
        ],
        [
            'label' => 'Pflegeartikel',
            'value' => count($careArticles),
            'delta' => '',
            'accentFrom' => '#c084fc33',
            'accentTo' => '#a855f733',
        ],
    ];

    $latestInquiries = [];
    foreach (array_slice($inquiries, 0, 5) as $inquiry) {
        $messagePreview = $inquiry['message'] ?? '';
        if (!empty($messagePreview)) {
            if (function_exists('mb_strimwidth')) {
                $messagePreview = mb_strimwidth($messagePreview, 0, 70, '…', 'UTF-8');
            } elseif (strlen($messagePreview) > 70) {
                $messagePreview = substr($messagePreview, 0, 67) . '…';
            }
        }

        $latestInquiries[] = [
            'sender' => $inquiry['sender_name'] ?? 'Unbekannt',
            'listing' => $inquiry['listing_title'] ?? 'Allgemeine Anfrage',
            'email' => $inquiry['sender_email'] ?? '',
            'message' => $messagePreview,
            'date' => !empty($inquiry['created_at']) ? date('d.m.Y', strtotime($inquiry['created_at'])) : '',
        ];
    }

    $projectOverview = [
        [
            'code' => 'CMS',
            'label' => 'Seiten',
            'value' => count($pages),
            'tone' => 'emerald',
        ],
        [
            'code' => 'NZ',
            'label' => 'Neuigkeiten',
            'value' => count($newsPosts),
            'tone' => 'sky',
        ],
        [
            'code' => 'GP',
            'label' => 'Genetik-Arten',
            'value' => isset($geneticSpecies) ? count($geneticSpecies) : 0,
            'tone' => 'violet',
        ],
        [
            'code' => 'GE',
            'label' => 'Gene & Referenzen',
            'value' => isset($geneticGenes) ? count($geneticGenes) : 0,
            'tone' => 'rose',
        ],
    ];

    $nextSteps = [
        ['label' => 'Galerie mit frischen Uploads bespielen, um die Startseite abwechslungsreich zu halten.', 'tone' => 'emerald'],
        ['label' => 'Neue Morph-Informationen prüfen und bei Bedarf ergänzen.', 'tone' => 'sky'],
        ['label' => 'Offene Adoptionen nachtelefonieren und Status im System anpassen.', 'tone' => 'rose'],
    ];

    $dashboardData = [
        'hero' => [
            'tag' => 'Mission Control',
            'title' => 'Verwaltungszentrale Neu Gedacht',
            'subtitle' => 'Das neue Interface verbindet ein reaktives Layout mit Tailwind-Styling. Alle Module bleiben erhalten, aber die Orchestrierung wird von React gesteuert.',
            'actions' => [
                [
                    'label' => 'Tierverwaltung öffnen',
                    'href' => BASE_URL . '/index.php?route=admin/animals',
                    'style' => 'emerald',
                ],
                [
                    'label' => 'Neue News verfassen',
                    'href' => BASE_URL . '/index.php?route=admin/news&create=1',
                    'style' => 'amber',
                ],
            ],
        ],
        'quickLinks' => $quickLinks,
        'metrics' => $metricCards,
        'status' => [
            'title' => 'Systemstatus',
            'message' => 'Alle Module laufen stabil. Adoptionen und Anfragen bleiben weiterhin priorisiert.',
        ],
        'inquiries' => $latestInquiries,
        'hasInquiries' => !empty($latestInquiries),
        'projectOverview' => $projectOverview,
        'nextSteps' => $nextSteps,
    ];
?>
<?php include __DIR__ . '/../partials/header.php'; ?>
<section class="relative mx-auto w-full max-w-7xl px-4 pb-24 pt-10 sm:px-6 lg:px-12">
    <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
        <div class="absolute left-[-10%] top-[-20%] h-80 w-80 rounded-full bg-gradient-to-br from-emerald-400/20 via-cyan-400/20 to-transparent blur-3xl"></div>
        <div class="absolute right-[-15%] top-[35%] h-96 w-96 rounded-full bg-gradient-to-br from-purple-500/30 via-rose-400/30 to-transparent blur-[120px]"></div>
        <div class="absolute inset-0 bg-slate-950/85"></div>
    </div>
    <div id="dashboard-react-root" class="space-y-12"></div>
    <noscript>
        <div class="mx-auto max-w-3xl rounded-3xl border border-amber-300/40 bg-amber-100/20 p-6 text-center text-amber-100">
            Diese Seite benötigt JavaScript, um das neue React-Dashboard anzuzeigen. Bitte aktiviere JavaScript oder aktualisiere deinen Browser.
        </div>
    </noscript>
</section>
<script id="dashboard-data" type="application/json"><?= json_encode($dashboardData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
<script type="text/babel">
    const iconMap = {
        Gauge: (
            <svg className="h-6 w-6 text-emerald-200" fill="none" stroke="currentColor" strokeWidth="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path strokeLinecap="round" strokeLinejoin="round" d="M12 3a9 9 0 100 18 9 9 0 000-18zm0 0v9l4 2" />
            </svg>
        ),
        Paw: (
            <svg className="h-6 w-6 text-emerald-200" fill="none" stroke="currentColor" strokeWidth="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path strokeLinecap="round" strokeLinejoin="round" d="M11 21c-1.657 0-3-1.79-3-4s1.343-4 3-4 3 1.79 3 4-1.343 4-3 4zm-5-7c-1.105 0-2-.895-2-2s.895-2 2-2 2 .895 2 2-.895 2-2 2zm10 0c-1.105 0-2-.895-2-2s.895-2 2-2 2 .895 2 2-.895 2-2 2zm-9-5c-1.105 0-2-.895-2-2s.895-2 2-2 2 .895 2 2-.895 2-2 2zm8 0c-1.105 0-2-.895-2-2s.895-2 2-2 2 .895 2 2-.895 2-2 2z" />
            </svg>
        ),
        Spark: (
            <svg className="h-6 w-6 text-emerald-200" fill="none" stroke="currentColor" strokeWidth="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path strokeLinecap="round" strokeLinejoin="round" d="M12 3l1.9 5.9H20l-4.7 3.4L16.2 18 12 14.9 7.8 18l.9-5.7L4 8.9h6.1z" />
            </svg>
        ),
        Repeat: (
            <svg className="h-6 w-6 text-emerald-200" fill="none" stroke="currentColor" strokeWidth="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path strokeLinecap="round" strokeLinejoin="round" d="M4 4h13a3 3 0 013 3v3m0 10H7a3 3 0 01-3-3v-3m16 6l-3-3m3 3l-3 3m-13-6l3-3m-3 3l3 3" />
            </svg>
        ),
        Inbox: (
            <svg className="h-6 w-6 text-emerald-200" fill="none" stroke="currentColor" strokeWidth="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path strokeLinecap="round" strokeLinejoin="round" d="M3 7a2 2 0 012-2h14a2 2 0 012 2v7a2 2 0 01-2 2h-4l-3 4-3-4H5a2 2 0 01-2-2z" />
            </svg>
        ),
        Notebook: (
            <svg className="h-6 w-6 text-emerald-200" fill="none" stroke="currentColor" strokeWidth="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path strokeLinecap="round" strokeLinejoin="round" d="M5 4h11a2 2 0 012 2v14H7a2 2 0 01-2-2V4zm11 4H9m0 4h7" />
            </svg>
        ),
        Megaphone: (
            <svg className="h-6 w-6 text-emerald-200" fill="none" stroke="currentColor" strokeWidth="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path strokeLinecap="round" strokeLinejoin="round" d="M3 11l18-7v10l-18-7v10a2 2 0 002 2h3" />
            </svg>
        ),
        Leaf: (
            <svg className="h-6 w-6 text-emerald-200" fill="none" stroke="currentColor" strokeWidth="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path strokeLinecap="round" strokeLinejoin="round" d="M5 21c4.418 0 13-2.239 13-10 0-3.866-3.134-7-7-7-5 0-8 4-8 9 0 4.418-2 6-2 6s2-1 4-1z" />
            </svg>
        ),
        Helix: (
            <svg className="h-6 w-6 text-emerald-200" fill="none" stroke="currentColor" strokeWidth="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path strokeLinecap="round" strokeLinejoin="round" d="M7 4c3 2 7 2 10 0m0 16c-3-2-7-2-10 0M7 4v4c0 3 2 5 5 5s5 2 5 5v4" />
            </svg>
        ),
        Gallery: (
            <svg className="h-6 w-6 text-emerald-200" fill="none" stroke="currentColor" strokeWidth="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path strokeLinecap="round" strokeLinejoin="round" d="M4 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H6l-4 4V5z" />
            </svg>
        ),
        Sliders: (
            <svg className="h-6 w-6 text-emerald-200" fill="none" stroke="currentColor" strokeWidth="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path strokeLinecap="round" strokeLinejoin="round" d="M4 21v-7m0 0a2 2 0 100-4m0 4h6m10 7v-9m0 0a2 2 0 10-4 0m4 0h-4m-6 9v-5m0 0a2 2 0 10-4 0m4 0H4" />
            </svg>
        ),
        Feather: (
            <svg className="h-6 w-6 text-emerald-200" fill="none" stroke="currentColor" strokeWidth="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path strokeLinecap="round" strokeLinejoin="round" d="M20 8.586l-9.172 9.172a4 4 0 01-5.656 0l-.707-.707a4 4 0 010-5.657L14.636 2.05a4 4 0 015.656 0l.707.707a4 4 0 010 5.657L8.464 20.95" />
            </svg>
        ),
        Users: (
            <svg className="h-6 w-6 text-emerald-200" fill="none" stroke="currentColor" strokeWidth="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path strokeLinecap="round" strokeLinejoin="round" d="M17 21v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2m9-10a4 4 0 100-8 4 4 0 000 8zm7-4a4 4 0 11-8 0 4 4 0 018 0zm4 14v-2a4 4 0 00-3-3.87" />
            </svg>
        ),
    };

    const gradientTokens = {
        emerald: 'from-emerald-400/40 via-emerald-400/20 to-transparent',
        amber: 'from-amber-400/40 via-amber-400/20 to-transparent',
    };

    const toneMap = {
        emerald: 'bg-emerald-400/15 text-emerald-100',
        sky: 'bg-sky-400/15 text-sky-100',
        violet: 'bg-violet-400/15 text-violet-100',
        rose: 'bg-rose-400/15 text-rose-100',
    };

    const NextStepDot = ({ tone }) => {
        const toneToColor = {
            emerald: 'bg-emerald-400',
            sky: 'bg-sky-400',
            rose: 'bg-rose-400',
        };
        return <span className={`mt-1 h-2.5 w-2.5 rounded-full ${toneToColor[tone] ?? 'bg-emerald-400'}`}></span>;
    };

    const MetricCard = ({ metric }) => (
        <article className="overflow-hidden rounded-3xl border border-white/10 bg-slate-950/50">
            <div className="p-[1px]">
                <div className="flex items-start justify-between gap-3 rounded-[22px] bg-slate-950/70 px-5 py-4">
                    <div>
                        <p className="text-xs uppercase tracking-[0.35em] text-slate-400">{metric.label}</p>
                        {metric.delta && <p className="mt-1 text-xs text-emerald-200/80">{metric.delta}</p>}
                    </div>
                    <span className="text-3xl font-semibold text-slate-50">{metric.value}</span>
                </div>
            </div>
        </article>
    );

    const QuickLink = ({ link }) => (
        <a
            href={link.href}
            className={`group relative flex flex-col gap-3 overflow-hidden rounded-3xl border ${link.active ? 'border-emerald-300/70 bg-emerald-400/20 text-emerald-100 shadow-[0_20px_60px_-30px_rgba(16,185,129,0.7)]' : 'border-white/10 bg-slate-900/50 text-slate-200 hover:border-emerald-200/70 hover:bg-emerald-400/10 hover:text-emerald-100 transition-colors duration-200'}`}
        >
            <div className="absolute inset-0 bg-gradient-to-br from-emerald-400/0 via-emerald-400/0 to-emerald-400/0 transition duration-300 group-hover:from-emerald-400/10 group-hover:via-emerald-400/0 group-hover:to-transparent"></div>
            <div className="relative flex items-center justify-between px-5 pt-5">
                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-400/10">
                    {iconMap[link.icon] ?? iconMap.Gauge}
                </div>
                <span className={`rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em] ${link.active ? 'bg-white/20 text-white' : 'bg-emerald-400/10 text-emerald-100'}`}>
                    {link.label}
                </span>
            </div>
            <div className="relative px-5 pb-5">
                <p className="text-sm text-slate-300 group-hover:text-emerald-100/90">{link.description}</p>
            </div>
        </a>
    );

    const InquiryItem = ({ inquiry }) => (
        <article className="rounded-3xl border border-white/10 bg-slate-950/70 px-5 py-4 transition hover:border-emerald-300/60">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p className="text-sm font-semibold text-slate-100">{inquiry.sender}</p>
                    <p className="text-xs text-slate-400">zu {inquiry.listing}</p>
                </div>
                {inquiry.date && (
                    <span className="rounded-full bg-emerald-400/10 px-3 py-1 text-xs font-medium text-emerald-100">{inquiry.date}</span>
                )}
            </div>
            <div className="mt-3 flex flex-wrap items-center gap-3 text-xs text-slate-300">
                {inquiry.email && (
                    <a
                        href={`mailto:${inquiry.email}`}
                        className="inline-flex items-center gap-1 rounded-full border border-slate-500/40 px-3 py-1 transition hover:border-emerald-300 hover:text-emerald-100"
                    >
                        E-Mail senden <span aria-hidden="true">→</span>
                    </a>
                )}
                {inquiry.message && (
                    <span className="inline-flex items-center gap-2 rounded-full bg-slate-800/80 px-3 py-1 text-slate-100/90">
                        Hinweis: {inquiry.message}
                    </span>
                )}
            </div>
        </article>
    );

    const ProjectMetric = ({ item }) => (
        <div className="flex items-center justify-between gap-3">
            <dt className={`flex items-center gap-3 text-slate-300`}>
                <span className={`inline-flex h-9 w-9 items-center justify-center rounded-full ${toneMap[item.tone] ?? toneMap.emerald}`}>
                    {item.code}
                </span>
                {item.label}
            </dt>
            <dd className="text-lg font-semibold text-slate-50">{item.value}</dd>
        </div>
    );

    const DashboardApp = (props) => {
        const data = props;

        return (
            <div className="space-y-12 text-slate-100">
                <div className="grid gap-8 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
                    <header className="flex flex-col justify-between rounded-[34px] border border-white/10 bg-slate-900/70 p-10 shadow-[0_35px_90px_-45px_rgba(8,47,73,0.9)] backdrop-blur">
                        <div className="space-y-6">
                            <span className="inline-flex items-center gap-2 rounded-full bg-emerald-400/10 px-5 py-1.5 text-xs font-semibold uppercase tracking-[0.4em] text-emerald-200">
                                {data.hero.tag}
                            </span>
                            <h1 className="text-4xl font-semibold text-white sm:text-5xl">{data.hero.title}</h1>
                            <p className="max-w-2xl text-base leading-relaxed text-slate-300">{data.hero.subtitle}</p>
                        </div>
                        <div className="mt-10 grid gap-4 sm:grid-cols-2">
                            {data.hero.actions.map((action) => (
                                <a
                                    key={action.href}
                                    href={action.href}
                                    className={`group flex items-center justify-between rounded-3xl border px-5 py-4 text-sm font-semibold transition ${
                                        action.style === 'emerald'
                                            ? 'border-emerald-400/40 bg-gradient-to-r from-emerald-500/30 via-emerald-500/10 to-transparent text-emerald-100 hover:border-emerald-200 hover:from-emerald-400/40'
                                            : 'border-amber-400/40 bg-gradient-to-r from-amber-500/30 via-amber-500/10 to-transparent text-amber-100 hover:border-amber-200 hover:from-amber-400/40'
                                    }`}
                                >
                                    {action.label}
                                    <svg className="h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" strokeWidth="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            ))}
                        </div>
                    </header>
                    <aside className="flex flex-col justify-between gap-6 rounded-[34px] border border-white/10 bg-slate-900/80 p-8 shadow-[0_30px_80px_-40px_rgba(15,23,42,0.9)] backdrop-blur">
                        <div className="grid gap-4">
                            {data.metrics.map((metric) => (
                                <MetricCard key={metric.label} metric={metric} />
                            ))}
                        </div>
                        <div className="rounded-3xl border border-white/10 bg-slate-950/60 p-6 text-xs text-slate-300">
                            <p className="font-semibold uppercase tracking-[0.3em] text-slate-400">{data.status.title}</p>
                            <p className="mt-3 leading-relaxed">{data.status.message}</p>
                        </div>
                    </aside>
                </div>

                <section className="rounded-[34px] border border-white/10 bg-slate-900/70 p-10 shadow-[0_35px_90px_-45px_rgba(8,47,73,0.9)] backdrop-blur">
                    <div className="flex flex-wrap items-center justify-between gap-6">
                        <div>
                            <h2 className="text-2xl font-semibold text-white">Schnellzugriffe</h2>
                            <p className="text-sm text-slate-400">Alle administrativen Module bleiben nur einen Klick entfernt.</p>
                        </div>
                        <div className="flex items-center gap-2 rounded-full border border-emerald-400/40 bg-emerald-400/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-emerald-100">
                            React gesteuert
                        </div>
                    </div>
                    <div className="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {data.quickLinks.map((link) => (
                            <QuickLink key={link.href} link={link} />
                        ))}
                    </div>
                </section>

                <div className="grid gap-8 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
                    <section className="rounded-[34px] border border-white/10 bg-slate-900/75 p-8 shadow-[0_35px_90px_-45px_rgba(8,47,73,1)] backdrop-blur">
                        <div className="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <h2 className="text-2xl font-semibold text-white">Letzte Anfragen</h2>
                                <p className="text-sm text-slate-400">Kommunikation für laufende Adoptionen bündeln.</p>
                            </div>
                            <a
                                href={data.quickLinks.find((item) => item.label === 'Anfragen')?.href ?? '#'}
                                className="inline-flex items-center gap-2 rounded-full border border-slate-500/40 bg-transparent px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-200 transition hover:border-emerald-300 hover:text-emerald-100"
                            >
                                Alle anzeigen
                            </a>
                        </div>
                        <div className="mt-6 space-y-4">
                            {data.hasInquiries ? (
                                data.inquiries.map((inquiry, index) => <InquiryItem key={`${inquiry.email}-${index}`} inquiry={inquiry} />)
                            ) : (
                                <p className="rounded-3xl border border-dashed border-slate-600/50 px-4 py-6 text-sm text-slate-300">
                                    Noch keine neuen Anfragen eingegangen.
                                </p>
                            )}
                        </div>
                    </section>

                    <section className="space-y-6">
                        <article className="rounded-[34px] border border-white/10 bg-slate-900/75 p-8 shadow-[0_30px_80px_-40px_rgba(15,23,42,1)] backdrop-blur">
                            <div className="flex items-center justify-between gap-3">
                                <h2 className="text-xl font-semibold text-white">Projektüberblick</h2>
                                <span className="text-xs uppercase tracking-[0.3em] text-slate-400">Live aktualisiert</span>
                            </div>
                            <dl className="mt-6 space-y-5 text-sm text-slate-200">
                                {data.projectOverview.map((item) => (
                                    <ProjectMetric key={item.code} item={item} />
                                ))}
                            </dl>
                        </article>

                        <article className="rounded-[34px] border border-dashed border-slate-600/60 bg-slate-900/60 p-8 text-sm text-slate-300 shadow-[0_25px_70px_-40px_rgba(15,23,42,1)] backdrop-blur">
                            <h3 className="text-base font-semibold text-slate-100">Nächste Schritte</h3>
                            <ul className="mt-4 space-y-4">
                                {data.nextSteps.map((step, index) => (
                                    <li key={index} className="flex items-start gap-3">
                                        <NextStepDot tone={step.tone} />
                                        <span>{step.label}</span>
                                    </li>
                                ))}
                            </ul>
                        </article>
                    </section>
                </div>
            </div>
        );
    };

    const container = document.getElementById('dashboard-react-root');
    if (container) {
        const dataElement = document.getElementById('dashboard-data');
        try {
            const parsed = JSON.parse(dataElement.textContent || '{}');
            const root = ReactDOM.createRoot(container);
            root.render(<DashboardApp {...parsed} />);
        } catch (error) {
            console.error('Dashboard konnte nicht initialisiert werden', error);
        }
    }
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
