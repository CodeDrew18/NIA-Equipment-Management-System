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

@if (($returnedRequestMessages ?? collect())->isNotEmpty())
<div id="global-returned-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/45 px-4">
    <div class="w-full max-w-2xl rounded-2xl bg-surface-container-lowest p-6 shadow-2xl border border-outline-variant/30">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h3 class="text-xl font-extrabold text-primary">Returned Requests</h3>
                <p class="text-sm text-on-surface-variant">The following request(s) were rejected and sent back for correction.</p>
            </div>
            <button id="global-returned-close" type="button" class="inline-flex items-center justify-center rounded-lg border border-outline-variant px-3 py-2 text-xs font-bold uppercase tracking-wider text-on-surface-variant hover:bg-surface-container-low">
                Close
            </button>
        </div>

        <div class="max-h-[60vh] overflow-y-auto space-y-3 pr-1">
            @foreach ($returnedRequestMessages as $messageRequest)
                @php
                    $requestAttachments = is_array($messageRequest->attachments) ? $messageRequest->attachments : [];
                @endphp
                <div class="rounded-xl border border-error/20 bg-error-container/60 p-4">
                    <p class="text-sm font-bold text-error">{{ $messageRequest->form_id }} was rejected.</p>
                    <p class="mt-1 text-sm font-semibold text-on-error-container">{{ $messageRequest->rejection_reason }}</p>

                    @if (count($requestAttachments) > 0)
                        <div class="mt-3 space-y-1">
                            @foreach ($requestAttachments as $attachmentIndex => $attachment)
                                <a
                                    href="{{ route('request-form.attachment.view', ['transportationRequest' => $messageRequest->id, 'index' => $attachmentIndex]) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:text-primary-container hover:underline"
                                >
                                    <span class="material-symbols-outlined text-sm">attach_file</span>
                                    {{ $attachment['file_name'] ?? 'Attachment' }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('global-returned-modal');
        const closeButton = document.getElementById('global-returned-close');
        const userId = @json((int) ($returnedRequestMessageUserId ?? 0));
        const storageKey = `nia_ems_returned_requests_seen_signature_user_${userId}`;
        const returnedRequestsSignature = @json(
            ($returnedRequestMessages ?? collect())
                ->map(function ($request) {
                    return (string) $request->id . '|' . (string) optional($request->updated_at)->timestamp;
                })
                ->implode(',')
        );

        if (!modal || !closeButton || returnedRequestsSignature === '') {
            return;
        }

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            try {
                window.localStorage.setItem(storageKey, returnedRequestsSignature);
            } catch (error) {
                // Ignore storage failures.
            }
        }

        let lastSeenSignature = '';
        try {
            lastSeenSignature = window.localStorage.getItem(storageKey) || '';
        } catch (error) {
            lastSeenSignature = '';
        }

        if (lastSeenSignature !== returnedRequestsSignature) {
            openModal();
        }

        closeButton.addEventListener('click', closeModal);

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
    })();
</script>
@endif

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