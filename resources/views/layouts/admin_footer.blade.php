<!-- Footer -->
<footer class="bg-slate-100 dark:bg-slate-950 w-full mt-auto border-t border-slate-200 dark:border-slate-800">
<div class="flex flex-col md:flex-row justify-between items-center px-12 py-10 w-full gap-6">
<div class="flex flex-col items-center md:items-start gap-2">
<span class="text-lg font-bold text-blue-900 dark:text-blue-400 tracking-tighter">EMS</span>
<p class="text-slate-500 dark:text-slate-400 font-public-sans text-sm text-center md:text-left">
                    © <?php echo date("Y"); ?> National Irrigation Administration. <br><b>Developed by: Andrew B. Malubag.
                </p>
</div>
<div class="flex flex-wrap justify-center gap-8">
<a class="text-slate-500 dark:text-slate-400 hover:underline decoration-emerald-500 text-sm font-public-sans transition-opacity opacity-80 hover:opacity-100" href="#">Privacy Policy</a>
<a class="text-slate-500 dark:text-slate-400 hover:underline decoration-emerald-500 text-sm font-public-sans transition-opacity opacity-80 hover:opacity-100" href="#">Terms of Service</a>
<a class="text-slate-500 dark:text-slate-400 hover:underline decoration-emerald-500 text-sm font-public-sans transition-opacity opacity-80 hover:opacity-100" href="#">Contact Support</a>
{{-- <a class="text-slate-500 dark:text-slate-400 hover:underline decoration-emerald-500 text-sm font-public-sans transition-opacity opacity-80 hover:opacity-100" href="#">Fleet Guidelines</a> --}}
</div>
</div>
</footer>

<div id="global-admin-pending-transportation-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/45 px-4">
    <div class="w-full max-w-lg rounded-2xl bg-surface-container-lowest p-6 shadow-2xl border border-outline-variant/30">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h3 class="text-xl font-extrabold text-primary">Pending Transportation Requests</h3>
                <p id="global-admin-pending-transportation-description" class="text-sm text-on-surface-variant">There {{ ((int) ($adminPendingTransportationRequestCount ?? 0)) === 1 ? 'is' : 'are' }} {{ number_format((int) ($adminPendingTransportationRequestCount ?? 0)) }} request{{ ((int) ($adminPendingTransportationRequestCount ?? 0)) === 1 ? '' : 's' }} waiting for admin review.</p>
            </div>
            <button id="global-admin-pending-transportation-close" type="button" class="inline-flex items-center justify-center rounded-lg border border-outline-variant px-3 py-2 text-xs font-bold uppercase tracking-wider text-on-surface-variant hover:bg-surface-container-low">
                Later
            </button>
        </div>

        <div class="rounded-xl border border-primary/20 bg-primary-fixed/30 p-4">
            <p class="text-sm text-on-surface-variant">Open Transportation Request to review and update pending transportation requests.</p>
            <a id="global-admin-pending-transportation-go" href="{{ route('admin.transportation-request') }}" class="mt-3 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-primary-container">
                <span class="material-symbols-outlined text-sm">description</span>
                Go to Transportation Request
            </a>
        </div>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('global-admin-pending-transportation-modal');
        const closeButton = document.getElementById('global-admin-pending-transportation-close');
        const goButton = document.getElementById('global-admin-pending-transportation-go');
        const description = document.getElementById('global-admin-pending-transportation-description');
        const userId = @json((int) ($adminPendingTransportationRequestUserId ?? 0));
        const endpoint = @json(route('admin.notifications.pending-transportation-requests'));
        const storageKey = `nia_ems_admin_pending_transportation_seen_signature_user_${userId}`;
        let pendingTransportationCount = @json((int) ($adminPendingTransportationRequestCount ?? 0));
        let pendingTransportationSignature = @json((string) ($adminPendingTransportationRequestSignature ?? ''));
        let pendingTransportationLatestId = extractLatestPendingId(pendingTransportationSignature);

        if (!modal || !closeButton || !goButton || !description || userId <= 0) {
            return;
        }

        function updateDescription(count) {
            const normalizedCount = Number.isFinite(Number(count))
                ? Math.max(0, Math.trunc(Number(count)))
                : 0;
            const verb = normalizedCount === 1 ? 'is' : 'are';
            const noun = normalizedCount === 1 ? 'request' : 'requests';

            description.textContent = `There ${verb} ${normalizedCount.toLocaleString('en-US')} ${noun} waiting for admin review.`;
        }

        function extractLatestPendingId(signature) {
            const raw = String(signature || '').trim();
            if (raw === '') {
                return 0;
            }

            const parts = raw.split('|');
            if (parts.length >= 2) {
                const parsedFromSignature = Number(parts[1]);
                if (Number.isFinite(parsedFromSignature) && parsedFromSignature > 0) {
                    return Math.trunc(parsedFromSignature);
                }
            }

            const parsedDirect = Number(raw);
            if (Number.isFinite(parsedDirect) && parsedDirect > 0) {
                return Math.trunc(parsedDirect);
            }

            return 0;
        }

        function readSeenPendingId() {
            try {
                const storedValue = window.localStorage.getItem(storageKey) || '';
                return extractLatestPendingId(storedValue);
            } catch (error) {
                return 0;
            }
        }

        function persistSeenPendingId() {
            const currentSeenId = readSeenPendingId();
            const nextSeenId = Math.max(currentSeenId, pendingTransportationLatestId);

            try {
                window.localStorage.setItem(storageKey, String(nextSeenId));
            } catch (error) {
                // Ignore storage failures.
            }
        }

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal(markAsSeen = true) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            if (!markAsSeen || pendingTransportationLatestId <= 0) {
                return;
            }

            persistSeenPendingId();
        }

        function shouldOpenModal() {
            if (pendingTransportationCount <= 0 || pendingTransportationLatestId <= 0) {
                return false;
            }

            const seenPendingId = readSeenPendingId();

            return pendingTransportationLatestId > seenPendingId;
        }

        function applyStateAndToggleModal() {
            updateDescription(pendingTransportationCount);

            if (shouldOpenModal()) {
                openModal();
                return;
            }

            closeModal(false);
        }

        async function refreshPendingTransportationState() {
            if (endpoint === '') {
                return;
            }

            try {
                const response = await fetch(endpoint, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    cache: 'no-store',
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                const nextCount = Number(payload.pendingCount ?? 0);
                const normalizedCount = Number.isFinite(nextCount)
                    ? Math.max(0, Math.trunc(nextCount))
                    : 0;
                const nextSignature = String(payload.pendingSignature ?? '');
                const nextLatestId = extractLatestPendingId(nextSignature);

                const hasChanged = normalizedCount !== pendingTransportationCount
                    || nextLatestId !== pendingTransportationLatestId;

                pendingTransportationCount = normalizedCount;
                pendingTransportationSignature = nextSignature;
                pendingTransportationLatestId = nextLatestId;

                if (hasChanged) {
                    applyStateAndToggleModal();
                }
            } catch (error) {
                // Ignore polling failures.
            }
        }

        applyStateAndToggleModal();

        closeButton.addEventListener('click', closeModal);
        goButton.addEventListener('click', function () {
            closeModal();
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        const pollIntervalMs = 10000;
        window.setInterval(refreshPendingTransportationState, pollIntervalMs);

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                refreshPendingTransportationState();
            }
        });
    })();
</script>

<script>
if (typeof window.emsLiveRefresh !== 'function') {
    window.__emsHasCustomLiveRefresh = false;

    window.emsLiveRefresh = function (refreshCallback, options = {}) {
        if (typeof refreshCallback !== 'function') {
            return {
                refreshNow: function () {},
                stop: function () {},
            };
        }

        const registerRefresh = options.register !== false;

        if (registerRefresh) {
            window.__emsHasCustomLiveRefresh = true;
        }

        const intervalMs = Math.max(1000, Number(options.intervalMs ?? 5000));
        const pauseOnInput = options.pauseOnInput !== false;
        const runImmediately = options.runImmediately === true;
        const shouldPause = typeof options.shouldPause === 'function'
            ? options.shouldPause
            : function () {
                return false;
            };

        let timerId = null;
        let isRefreshing = false;

        function isUserInteracting() {
            if (!pauseOnInput) {
                return false;
            }

            const active = document.activeElement;
            if (!active) {
                return false;
            }

            const tag = String(active.tagName || '').toLowerCase();
            const interactiveTags = ['input', 'textarea', 'select', 'button'];

            return active.isContentEditable || interactiveTags.includes(tag);
        }

        async function executeRefresh() {
            if (isRefreshing || document.hidden || isUserInteracting() || shouldPause()) {
                return;
            }

            isRefreshing = true;

            try {
                await refreshCallback();
            } catch (error) {
                console.error('Live refresh failed.', error);
            } finally {
                isRefreshing = false;
            }
        }

        timerId = window.setInterval(executeRefresh, intervalMs);

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                executeRefresh();
            }
        });

        if (runImmediately) {
            executeRefresh();
        }

        return {
            refreshNow: executeRefresh,
            stop: function () {
                if (timerId !== null) {
                    window.clearInterval(timerId);
                    timerId = null;
                }
            },
        };
    };

    window.emsRefreshCurrentPageTables = async function () {
        const currentBodies = Array.from(document.querySelectorAll('table tbody'));
        if (currentBodies.length === 0) {
            return;
        }

        const url = new URL(window.location.href);
        url.searchParams.set('_live_refresh', String(Date.now()));

        const response = await fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            cache: 'no-store',
        });

        if (!response.ok) {
            return;
        }

        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const nextBodies = Array.from(doc.querySelectorAll('table tbody'));

        if (nextBodies.length !== currentBodies.length) {
            return;
        }

        currentBodies.forEach(function (tbody, index) {
            const nextBody = nextBodies[index];
            if (!nextBody) {
                return;
            }

            tbody.innerHTML = nextBody.innerHTML;
        });
    };

    document.addEventListener('DOMContentLoaded', function () {
        if (window.__emsGenericTableRefreshInitialized) {
            return;
        }

        window.__emsGenericTableRefreshInitialized = true;

        window.setTimeout(function () {
            if (window.__emsHasCustomLiveRefresh) {
                return;
            }

            if (document.querySelectorAll('table tbody').length === 0) {
                return;
            }

            window.emsLiveRefresh(function () {
                return window.emsRefreshCurrentPageTables();
            }, {
                intervalMs: 5000,
                register: false,
            });
        }, 0);
    });
}
</script>

<script>
    (function () {
        const shouldAutoClearOnSuccess = @json(
            collect((array) session()->get('_flash.old', []))
                ->filter(function ($key) {
                    return is_string($key);
                })
                ->contains(function ($key) {
                    return str_contains(strtolower($key), 'success');
                })
        );

        function isPreserved(el) {
            try {
                return el && el.hasAttribute && el.hasAttribute('data-preserve') && String(el.getAttribute('data-preserve')) !== 'false';
            } catch (e) {
                return false;
            }
        }

        function isClearableInput(el) {
            if (!el) {
                return false;
            }

            if (el.disabled || el.readOnly) {
                return false;
            }

            const tag = String(el.tagName || '').toLowerCase();

            if (tag === 'textarea' || tag === 'select') {
                return true;
            }

            if (tag !== 'input') {
                return false;
            }

            const type = String(el.type || '').toLowerCase();
            const skippedTypes = ['hidden', 'submit', 'button', 'reset', 'image'];

            return !skippedTypes.includes(type);
        }

        function clearFormInputs(root) {
            try {
                if (!root) {
                    return;
                }

                const container = (root instanceof HTMLElement)
                    ? root
                    : (document.getElementById(String(root || '')) || null);

                if (!container) {
                    return;
                }

                const elements = Array.from(container.querySelectorAll('input,textarea,select'));

                elements.forEach(function (el) {
                    if (isPreserved(el) || !isClearableInput(el)) {
                        return;
                    }

                    const type = String(el.type || '').toLowerCase();

                    if (type === 'checkbox' || type === 'radio') {
                        el.checked = false;
                        return;
                    }

                    if (el.tagName && String(el.tagName).toLowerCase() === 'select') {
                        if (el.multiple) {
                            Array.from(el.options || []).forEach(function (option) {
                                option.selected = false;
                            });
                        } else {
                            try { el.selectedIndex = 0; } catch (e) { /* ignore */ }
                        }
                        return;
                    }

                    if (type === 'file') {
                        try { el.value = ''; } catch (e) { /* ignore file clear */ }
                        const preview = el.getAttribute && el.getAttribute('data-preview-target');
                        if (preview) {
                            const previewEl = document.getElementById(preview);
                            if (previewEl) {
                                previewEl.innerHTML = '';
                            }
                        }
                        return;
                    }

                    try { el.value = ''; } catch (e) { /* ignore */ }
                });
            } catch (error) {
                // swallow errors - helper must be safe
            }
        }

        window.emsClearFormInputs = window.emsClearFormInputs || clearFormInputs;

        window.emsDispatchClearOnSuccess = window.emsDispatchClearOnSuccess || function (form) {
            try {
                if (!form) {
                    return;
                }

                const target = (form instanceof Event) ? (form.target || null) : form;
                const el = (target && target instanceof HTMLElement)
                    ? target
                    : (document.getElementById(String(target || '')) || null);

                if (!el) {
                    return;
                }

                clearFormInputs(el);
            } catch (e) {
                // ignore
            }
        };

        function clearFormsAfterSuccessfulSubmit() {
            if (!shouldAutoClearOnSuccess) {
                return;
            }

            document.querySelectorAll('form').forEach(function (form) {
                const method = String(form.getAttribute('method') || 'get').toLowerCase();
                const action = String(form.getAttribute('action') || '').toLowerCase();

                if (method === 'get') {
                    return;
                }

                if (action.includes('/logout')) {
                    return;
                }

                if (String(form.getAttribute('data-no-clear-on-success') || '').toLowerCase() === 'true') {
                    return;
                }

                clearFormInputs(form);
            });
        }

        document.addEventListener('ems:form-submitted-success', function (event) {
            try {
                const form = event && event.detail && event.detail.form;
                if (!form) {
                    return;
                }

                clearFormInputs(form);
            } catch (e) {
                // ignore
            }
        }, false);

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', clearFormsAfterSuccessfulSubmit);
        } else {
            clearFormsAfterSuccessfulSubmit();
        }
    })();
</script>

@include('layouts.partials.date_picker_enhancer')