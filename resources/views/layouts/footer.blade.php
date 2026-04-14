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

@php
    $initialReturnedRequestRows = ($returnedRequestMessages ?? collect())
        ->map(function ($request) {
            $attachments = collect(is_array($request->attachments) ? $request->attachments : [])
                ->values()
                ->map(function ($attachment, $index) use ($request) {
                    return [
                        'fileName' => trim((string) (is_array($attachment) ? ($attachment['file_name'] ?? '') : '')) ?: 'Attachment',
                        'url' => route('request-form.attachment.view', [
                            'transportationRequest' => $request->id,
                            'index' => $index,
                        ]),
                    ];
                })
                ->values()
                ->all();

            return [
                'id' => (int) ($request->id ?? 0),
                'formId' => (string) ($request->form_id ?? 'N/A'),
                'rejectionReason' => (string) ($request->rejection_reason ?? ''),
                'attachments' => $attachments,
            ];
        })
        ->sortByDesc('id')
        ->values()
        ->all();
@endphp

<div id="global-returned-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/45 px-4">
    <div class="w-full max-w-2xl rounded-2xl bg-surface-container-lowest p-6 shadow-2xl border border-outline-variant/30">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h3 class="text-xl font-extrabold text-primary">Returned Requests</h3>
                <p class="text-sm text-on-surface-variant">The latest rejected request will appear here for correction.</p>
            </div>
            <button id="global-returned-close" type="button" class="inline-flex items-center justify-center rounded-lg border border-outline-variant px-3 py-2 text-xs font-bold uppercase tracking-wider text-on-surface-variant hover:bg-surface-container-low">
                Close
            </button>
        </div>

        <div id="global-returned-list" class="max-h-[60vh] overflow-y-auto pr-1"></div>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('global-returned-modal');
        const closeButton = document.getElementById('global-returned-close');
        const list = document.getElementById('global-returned-list');
        const userId = @json((int) ($returnedRequestMessageUserId ?? 0));
        const endpoint = @json(route('user.notifications.returned-requests'));
        const storageKey = `nia_ems_returned_requests_last_seen_id_user_${userId}`;
        const initialReturnedRequests = @json($initialReturnedRequestRows);

        let latestReturnedRequest = Array.isArray(initialReturnedRequests) && initialReturnedRequests.length > 0
            ? initialReturnedRequests[0]
            : null;
        let latestReturnedRequestSignature = latestReturnedRequest && Number(latestReturnedRequest.id) > 0
            ? String(latestReturnedRequest.id)
            : '';
        let fetchInFlight = false;

        if (!modal || !closeButton || !list || userId <= 0) {
            return;
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function getLastSeenRejectedId() {
            try {
                const storedValue = Number(window.localStorage.getItem(storageKey) || 0);
                return Number.isFinite(storedValue) ? Math.max(0, Math.trunc(storedValue)) : 0;
            } catch (error) {
                return 0;
            }
        }

        function setLastSeenRejectedId(value) {
            const normalizedValue = Number(value);
            if (!Number.isFinite(normalizedValue) || normalizedValue <= 0) {
                return;
            }

            try {
                window.localStorage.setItem(storageKey, String(Math.trunc(normalizedValue)));
            } catch (error) {
                // Ignore storage failures.
            }
        }

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal(markAsSeen) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            if (!markAsSeen || !latestReturnedRequest) {
                return;
            }

            setLastSeenRejectedId(latestReturnedRequest.id);
        }

        function renderLatestReturnedRequest() {
            if (!latestReturnedRequest || Number(latestReturnedRequest.id) <= 0) {
                list.innerHTML = '<p class="rounded-xl border border-outline-variant/40 bg-surface-container-low p-4 text-sm text-on-surface-variant">No returned requests right now.</p>';
                return;
            }

            const attachments = Array.isArray(latestReturnedRequest.attachments)
                ? latestReturnedRequest.attachments
                : [];

            const attachmentsHtml = attachments.length > 0
                ? '<div class="mt-3 space-y-1">' + attachments.map(function (attachment) {
                    const href = String((attachment && attachment.url) ? attachment.url : '#');
                    const fileName = escapeHtml((attachment && attachment.fileName) ? attachment.fileName : 'Attachment');

                    return [
                        '<a href="' + href + '" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:text-primary-container hover:underline">',
                        '<span class="material-symbols-outlined text-sm">attach_file</span>',
                        fileName,
                        '</a>'
                    ].join('');
                }).join('') + '</div>'
                : '';

            list.innerHTML = [
                '<div class="rounded-xl border border-error/20 bg-error-container/60 p-4" data-returned-request-id="' + escapeHtml(latestReturnedRequest.id) + '">',
                '<p class="text-sm font-bold text-error">' + escapeHtml(latestReturnedRequest.formId || 'N/A') + ' was rejected.</p>',
                '<p class="mt-1 text-sm font-semibold text-on-error-container">' + escapeHtml(latestReturnedRequest.rejectionReason || 'No rejection reason provided.') + '</p>',
                attachmentsHtml,
                '</div>'
            ].join('');
        }

        function applyState() {
            renderLatestReturnedRequest();

            if (!latestReturnedRequest || Number(latestReturnedRequest.id) <= 0) {
                closeModal(false);
                return;
            }

            const lastSeenRejectedId = getLastSeenRejectedId();

            if (Number(latestReturnedRequest.id) > lastSeenRejectedId) {
                openModal();
                return;
            }

            closeModal(false);
        }

        async function refreshReturnedRequests() {
            if (fetchInFlight || endpoint === '') {
                return;
            }

            fetchInFlight = true;

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
                const nextLatestRequest = payload && payload.latestRequest && Number(payload.latestRequest.id) > 0
                    ? payload.latestRequest
                    : null;
                const nextSignature = String(payload.latestRequestSignature ?? '');
                const currentLatestId = latestReturnedRequest ? Number(latestReturnedRequest.id) : 0;
                const nextLatestId = Number(payload.latestRequestId ?? 0);
                const hasChanged = nextSignature !== latestReturnedRequestSignature
                    || currentLatestId !== nextLatestId;

                latestReturnedRequest = nextLatestRequest;
                latestReturnedRequestSignature = nextSignature;

                if (hasChanged) {
                    applyState();
                }
            } catch (error) {
                // Ignore polling failures.
            } finally {
                fetchInFlight = false;
            }
        }

        applyState();

        closeButton.addEventListener('click', function () {
            closeModal(true);
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal(true);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModal(true);
            }
        });

        const pollIntervalMs = 10000;
        window.setInterval(refreshReturnedRequests, pollIntervalMs);

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                refreshReturnedRequests();
            }
        });
    })();
</script>

<div id="global-pending-evaluation-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/45 px-4">
    <div class="w-full max-w-lg rounded-2xl bg-surface-container-lowest p-6 shadow-2xl border border-outline-variant/30">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h3 class="text-xl font-extrabold text-primary">Pending Driver Evaluations</h3>
                <p id="global-pending-evaluation-description" class="text-sm text-on-surface-variant">You have {{ number_format((int) ($pendingUserEvaluationCount ?? 0)) }} trip evaluation{{ ((int) ($pendingUserEvaluationCount ?? 0)) === 1 ? '' : 's' }} waiting for submission.</p>
            </div>
            <button id="global-pending-evaluation-close" type="button" class="inline-flex items-center justify-center rounded-lg border border-outline-variant px-3 py-2 text-xs font-bold uppercase tracking-wider text-on-surface-variant hover:bg-surface-container-low">
                Later
            </button>
        </div>

        <div class="rounded-xl border border-primary/20 bg-primary-fixed/30 p-4">
            <p class="text-sm text-on-surface-variant">Open your Driver Performance Evaluation page to complete pending evaluations.</p>
            <a id="global-pending-evaluation-go" href="{{ route('evaluation-performance') }}" class="mt-3 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-primary-container">
                <span class="material-symbols-outlined text-sm">edit_note</span>
                Go to Evaluation
            </a>
        </div>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('global-pending-evaluation-modal');
        const closeButton = document.getElementById('global-pending-evaluation-close');
        const goButton = document.getElementById('global-pending-evaluation-go');
        const description = document.getElementById('global-pending-evaluation-description');
        const userId = @json((int) ($returnedRequestMessageUserId ?? 0));
        const endpoint = @json(route('user.notifications.pending-evaluations'));
        const storageKey = `nia_ems_pending_evaluations_seen_signature_user_${userId}`;
        let pendingEvaluationCount = @json((int) ($pendingUserEvaluationCount ?? 0));
        let pendingEvaluationSignature = @json((string) ($pendingUserEvaluationSignature ?? ''));

        if (!modal || !closeButton || !goButton || !description || userId <= 0) {
            return;
        }

        function updateDescription(count) {
            const normalizedCount = Number.isFinite(Number(count))
                ? Math.max(0, Math.trunc(Number(count)))
                : 0;

            description.textContent = `You have ${normalizedCount.toLocaleString('en-US')} trip evaluation${normalizedCount === 1 ? '' : 's'} waiting for submission.`;
        }

        function showModalWhenReady(attempt) {
            const returnedModal = document.getElementById('global-returned-modal');
            const isReturnedModalOpen = returnedModal && !returnedModal.classList.contains('hidden');

            if (isReturnedModalOpen && attempt < 20) {
                window.setTimeout(function () {
                    showModalWhenReady(attempt + 1);
                }, 180);

                return;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal(markAsSeen = true) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            if (!markAsSeen || pendingEvaluationSignature === '') {
                return;
            }

            try {
                window.localStorage.setItem(storageKey, pendingEvaluationSignature);
            } catch (error) {
                // Ignore storage failures.
            }
        }

        function shouldOpenModal() {
            if (pendingEvaluationCount <= 0 || pendingEvaluationSignature === '') {
                return false;
            }

            let lastSeenSignature = '';
            try {
                lastSeenSignature = window.localStorage.getItem(storageKey) || '';
            } catch (error) {
                lastSeenSignature = '';
            }

            return lastSeenSignature !== pendingEvaluationSignature;
        }

        function applyStateAndToggleModal() {
            updateDescription(pendingEvaluationCount);

            if (shouldOpenModal()) {
                showModalWhenReady(0);
                return;
            }

            closeModal(false);
        }

        async function refreshPendingEvaluationState() {
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

                const hasChanged = normalizedCount !== pendingEvaluationCount
                    || nextSignature !== pendingEvaluationSignature;

                pendingEvaluationCount = normalizedCount;
                pendingEvaluationSignature = nextSignature;

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
        window.setInterval(refreshPendingEvaluationState, pollIntervalMs);

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                refreshPendingEvaluationState();
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

@include('layouts.partials.date_picker_enhancer')