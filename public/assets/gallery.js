(function () {
    if (typeof window === 'undefined') {
        return;
    }
    var React = window.React;
    var ReactDOM = window.ReactDOM;
    if (!React || !ReactDOM) {
        return;
    }
    var rootElement = document.getElementById('react-gallery-root');
    if (!rootElement) {
        return;
    }

    var useState = React.useState;
    var useMemo = React.useMemo;
    var useEffect = React.useEffect;
    var useRef = React.useRef;
    var useCallback = React.useCallback;

    function Lightbox(props) {
        var item = props.item || {};
        var onClose = props.onClose || function () {};
        var onPrev = props.onPrev;
        var onNext = props.onNext;
        var preview = (item.urls && (item.urls.original || item.urls.medium || item.urls.thumb)) || '';

        return React.createElement(
            'div',
            {
                className: 'react-gallery__lightbox',
                role: 'dialog',
                'aria-modal': 'true',
                'aria-label': item.alt || 'Galeriebild',
                onClick: onClose,
            },
            React.createElement('div', { className: 'react-gallery__lightbox-panel', onClick: function (event) { event.stopPropagation(); } },
                React.createElement('button', {
                    type: 'button',
                    className: 'react-gallery__lightbox-close',
                    onClick: onClose,
                    'aria-label': 'Schließen',
                }, '×'),
                preview ? React.createElement('img', {
                    src: preview,
                    alt: item.alt || 'Galeriebild',
                    className: 'react-gallery__lightbox-image',
                    loading: 'eager',
                }) : null,
                item.alt ? React.createElement('p', { className: 'react-gallery__lightbox-caption' }, item.alt) : null,
                item.urls && item.urls.original ? React.createElement('a', {
                    href: item.urls.original,
                    target: '_blank',
                    rel: 'noopener noreferrer',
                    className: 'react-gallery__lightbox-download',
                }, 'Original öffnen') : null,
                React.createElement('div', { className: 'react-gallery__lightbox-actions' },
                    onPrev ? React.createElement('button', {
                        type: 'button',
                        className: 'react-gallery__lightbox-nav is-prev',
                        onClick: function (event) { event.stopPropagation(); onPrev(); },
                        'aria-label': 'Vorheriges Bild',
                    }, '‹') : null,
                    onNext ? React.createElement('button', {
                        type: 'button',
                        className: 'react-gallery__lightbox-nav is-next',
                        onClick: function (event) { event.stopPropagation(); onNext(); },
                        'aria-label': 'Nächstes Bild',
                    }, '›') : null
                )
            )
        );
    }

    function GalleryApp(props) {
        var baseUrl = props.baseUrl || '';
        var collections = Array.isArray(props.collections) ? props.collections : [];
        var initialItems = Array.isArray(props.items) ? props.items : [];
        var initialSlug = props.selectedSlug || 'all';
        var initialPage = typeof props.page === 'number' ? props.page : 1;
        var initialHasMore = Boolean(props.hasMore);
        var meta = props.meta || {};

        var filters = useMemo(function () {
            return [{ slug: 'all', name: 'Alle Aufnahmen', description: null }].concat(collections);
        }, [collections]);

        var _useState = useState(initialSlug || 'all');
        var selectedSlug = _useState[0];
        var setSelectedSlug = _useState[1];

        var _useState2 = useState(initialItems);
        var items = _useState2[0];
        var setItems = _useState2[1];

        var _useState3 = useState(initialPage);
        var page = _useState3[0];
        var setPage = _useState3[1];

        var _useState4 = useState(initialHasMore);
        var hasMore = _useState4[0];
        var setHasMore = _useState4[1];

        var _useState5 = useState(false);
        var loading = _useState5[0];
        var setLoading = _useState5[1];

        var _useState6 = useState(null);
        var lightboxIndex = _useState6[0];
        var setLightboxIndex = _useState6[1];

        var _useState7 = useState(null);
        var errorMessage = _useState7[0];
        var setErrorMessage = _useState7[1];

        var initialLoadRef = useRef(true);

        var activeFilter = useMemo(function () {
            for (var i = 0; i < filters.length; i += 1) {
                if (filters[i].slug === selectedSlug) {
                    return filters[i];
                }
            }
            return filters[0];
        }, [filters, selectedSlug]);

        var fetchMedia = useCallback(function (nextPage, append) {
            setLoading(true);
            setErrorMessage(null);
            var endpoint = new URL(baseUrl, window.location.origin);
            endpoint.searchParams.set('page', String(nextPage));
            endpoint.searchParams.set('format', 'json');
            if (selectedSlug && selectedSlug !== 'all') {
                endpoint.searchParams.set('collection', selectedSlug);
            } else {
                endpoint.searchParams.delete('collection');
            }
            return fetch(endpoint.toString(), { credentials: 'same-origin' })
                .then(function (response) { return response.json().then(function (json) { return { ok: response.ok, json: json }; }); })
                .then(function (payload) {
                    if (!payload.ok || !payload.json || !Array.isArray(payload.json.items)) {
                        throw new Error(payload.json && payload.json.message ? payload.json.message : 'Daten konnten nicht geladen werden.');
                    }
                    setItems(function (prev) {
                        return append ? prev.concat(payload.json.items) : payload.json.items;
                    });
                    setPage(payload.json.page || nextPage);
                    setHasMore(Boolean(payload.json.hasMore));
                    setErrorMessage(null);
                })
                .catch(function (error) {
                    console.error(error);
                    setErrorMessage(error && error.message ? error.message : 'Die Galerie konnte nicht aktualisiert werden.');
                })
                .finally(function () {
                    setLoading(false);
                });
        }, [baseUrl, selectedSlug]);

        useEffect(function () {
            if (initialLoadRef.current) {
                initialLoadRef.current = false;
                return;
            }
            fetchMedia(1, false);
        }, [selectedSlug, fetchMedia]);

        useEffect(function () {
            var url = new URL(window.location.href);
            if (selectedSlug && selectedSlug !== 'all') {
                url.searchParams.set('collection', selectedSlug);
            } else {
                url.searchParams.delete('collection');
            }
            url.searchParams.delete('page');
            window.history.replaceState({}, '', url.toString());
        }, [selectedSlug]);

        useEffect(function () {
            if (lightboxIndex !== null) {
                var handler = function handler(event) {
                    if (event.key === 'Escape') {
                        setLightboxIndex(null);
                    } else if (event.key === 'ArrowRight') {
                        setLightboxIndex(function (prev) {
                            if (prev === null) {
                                return prev;
                            }
                            return Math.min(items.length - 1, prev + 1);
                        });
                    } else if (event.key === 'ArrowLeft') {
                        setLightboxIndex(function (prev) {
                            if (prev === null) {
                                return prev;
                            }
                            return Math.max(0, prev - 1);
                        });
                    }
                };
                document.body.classList.add('overflow-hidden');
                document.addEventListener('keydown', handler);
                return function () {
                    document.body.classList.remove('overflow-hidden');
                    document.removeEventListener('keydown', handler);
                };
            }
            return undefined;
        }, [lightboxIndex, items.length]);

        function handleSelect(slug) {
            if (slug === selectedSlug) {
                return;
            }
            setSelectedSlug(slug);
            setLightboxIndex(null);
        }

        function handleLoadMore() {
            if (loading || !hasMore) {
                return;
            }
            fetchMedia(page + 1, true);
        }

        function openLightbox(index) {
            setLightboxIndex(index);
        }

        function closeLightbox() {
            setLightboxIndex(null);
        }

        function showPrev() {
            setLightboxIndex(function (prev) {
                if (prev === null) {
                    return prev;
                }
                return Math.max(0, prev - 1);
            });
        }

        function showNext() {
            setLightboxIndex(function (prev) {
                if (prev === null) {
                    return prev;
                }
                return Math.min(items.length - 1, prev + 1);
            });
        }

        var lightboxItem = lightboxIndex !== null ? items[lightboxIndex] : null;

        return React.createElement(
            'div',
            { className: 'react-gallery__container' },
            React.createElement('header', { className: 'react-gallery__header' },
                React.createElement('div', null,
                    React.createElement('h1', { className: 'react-gallery__title' }, meta.title || 'Galerie'),
                    React.createElement('p', { className: 'react-gallery__subtitle' }, meta.subtitle || '')
                ),
                activeFilter && activeFilter.description ? React.createElement('p', { className: 'react-gallery__active-description' }, activeFilter.description) : null
            ),
            errorMessage ? React.createElement('div', { className: 'react-gallery__error', role: 'alert', 'aria-live': 'assertive' }, errorMessage) : null,
            React.createElement('div', { className: 'react-gallery__filters', role: 'tablist', 'aria-label': 'Galerie-Kategorien' },
                filters.map(function (filter) {
                    var isActive = filter.slug === selectedSlug;
                    return React.createElement('button', {
                        key: filter.slug,
                        type: 'button',
                        className: 'react-gallery__chip' + (isActive ? ' is-active' : ''),
                        onClick: function () { handleSelect(filter.slug); },
                        role: 'tab',
                        'aria-selected': isActive,
                    }, filter.name);
                })
            ),
            React.createElement('div', { className: 'react-gallery__grid' + (loading ? ' is-loading' : '') },
                items.map(function (item, index) {
                    var thumb = (item.urls && (item.urls.medium || item.urls.thumb || item.urls.original)) || '';
                    if (!thumb) {
                        return null;
                    }
                    return React.createElement('article', {
                        key: item.id || thumb + '-' + index,
                        className: 'react-gallery__card',
                        style: { animationDelay: (index * 50) + 'ms' },
                    },
                    React.createElement('button', {
                        type: 'button',
                        className: 'react-gallery__media',
                        onClick: function () { openLightbox(index); },
                    },
                        React.createElement('img', {
                            src: thumb,
                            alt: item.alt || 'Galeriebild',
                            loading: index < 6 ? 'eager' : 'lazy',
                        }),
                        React.createElement('span', { className: 'react-gallery__caption' }, item.alt || 'Ohne Beschreibung')
                    ));
                })
            ),
            hasMore ? React.createElement('div', { className: 'react-gallery__more' },
                React.createElement('button', {
                    type: 'button',
                    className: 'btn',
                    onClick: handleLoadMore,
                    disabled: loading,
                }, loading ? 'Lädt…' : 'Mehr laden')
            ) : null,
            loading ? React.createElement('div', { className: 'react-gallery__loading-indicator' }, 'Wird geladen…') : null,
            lightboxItem ? React.createElement(Lightbox, {
                item: lightboxItem,
                onClose: closeLightbox,
                onPrev: lightboxIndex > 0 ? showPrev : null,
                onNext: lightboxIndex < items.length - 1 ? showNext : null,
            }) : null
        );
    }

    var appData = window.__GALLERY_DATA__ || {};
    if (ReactDOM.createRoot) {
        var root = ReactDOM.createRoot(rootElement);
        root.render(React.createElement(GalleryApp, appData));
    } else {
        ReactDOM.render(React.createElement(GalleryApp, appData), rootElement);
    }
    try {
        delete window.__GALLERY_DATA__;
    } catch (error) {
        window.__GALLERY_DATA__ = undefined;
    }
})();
