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
        $initialApprovedRequests = ($approvedRequestMessages ?? collect())
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
                    'attachments' => $attachments,
                ];
            })
            ->sortByDesc('id')
            ->values()
            ->all();
        $initialCancelledRequests = ($cancelledRequestMessages ?? collect())
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
                    'cancellationReason' => (string) ($request->rejection_reason ?? ''),
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

<div id="global-approved-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/45 px-4">
    <div class="w-full max-w-2xl rounded-2xl bg-surface-container-lowest p-6 shadow-2xl border border-outline-variant/30">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h3 class="text-xl font-extrabold text-primary">Approved Requests</h3>
                <p class="text-sm text-on-surface-variant">The latest approved request will appear here.</p>
            </div>
            <button id="global-approved-close" type="button" class="inline-flex items-center justify-center rounded-lg border border-outline-variant px-3 py-2 text-xs font-bold uppercase tracking-wider text-on-surface-variant hover:bg-surface-container-low">
                Close
            </button>
        </div>

        <div id="global-approved-list" class="max-h-[60vh] overflow-y-auto pr-1"></div>
    </div>
</div>

<div id="global-cancelled-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/45 px-4">
    <div class="w-full max-w-2xl rounded-2xl bg-surface-container-lowest p-6 shadow-2xl border border-outline-variant/30">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h3 class="text-xl font-extrabold text-primary">Cancelled Requests</h3>
                <p class="text-sm text-on-surface-variant">The latest cancelled request will appear here with the reason.</p>
            </div>
            <button id="global-cancelled-close" type="button" class="inline-flex items-center justify-center rounded-lg border border-outline-variant px-3 py-2 text-xs font-bold uppercase tracking-wider text-on-surface-variant hover:bg-surface-container-low">
                Close
            </button>
        </div>

        <div id="global-cancelled-list" class="max-h-[60vh] overflow-y-auto pr-1"></div>
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

<script>
    (function () {
        const modal = document.getElementById('global-approved-modal');
        const closeButton = document.getElementById('global-approved-close');
        const list = document.getElementById('global-approved-list');
        const userId = @json((int) ($approvedRequestMessageUserId ?? 0));
        const endpoint = @json(route('user.notifications.approved-requests'));
        const storageKey = `nia_ems_approved_requests_last_seen_id_user_${userId}`;
        const initialApproved = @json($initialApprovedRequests);

        let latestApprovedRequest = Array.isArray(initialApproved) && initialApproved.length > 0
            ? initialApproved[0]
            : null;
        let latestApprovedRequestSignature = latestApprovedRequest && Number(latestApprovedRequest.id) > 0
            ? String(latestApprovedRequest.id)
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

        function getLastSeenApprovedId() {
            try {
                const storedValue = Number(window.localStorage.getItem(storageKey) || 0);
                return Number.isFinite(storedValue) ? Math.max(0, Math.trunc(storedValue)) : 0;
            } catch (error) {
                return 0;
            }
        }

        function setLastSeenApprovedId(value) {
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

            if (!markAsSeen || !latestApprovedRequest) {
                return;
            }

            setLastSeenApprovedId(latestApprovedRequest.id);
        }

        function renderLatestApprovedRequest() {
            if (!latestApprovedRequest || Number(latestApprovedRequest.id) <= 0) {
                list.innerHTML = '<p class="rounded-xl border border-outline-variant/40 bg-surface-container-low p-4 text-sm text-on-surface-variant">No approved requests right now.</p>';
                return;
            }

            const attachments = Array.isArray(latestApprovedRequest.attachments)
                ? latestApprovedRequest.attachments
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
                '<div class="rounded-xl border border-primary/20 bg-primary-fixed/20 p-4" data-approved-request-id="' + escapeHtml(latestApprovedRequest.id) + '">',
                '<p class="text-sm font-bold text-primary">' + escapeHtml(latestApprovedRequest.formId || 'N/A') + ' was approved.</p>',
                attachmentsHtml,
                '</div>'
            ].join('');
        }

        function applyState() {
            renderLatestApprovedRequest();

            if (!latestApprovedRequest || Number(latestApprovedRequest.id) <= 0) {
                closeModal(false);
                return;
            }

            const lastSeenId = getLastSeenApprovedId();

            if (Number(latestApprovedRequest.id) > lastSeenId) {
                openModal();
                return;
            }

            closeModal(false);
        }

        async function refreshApprovedRequests() {
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
                const currentLatestId = latestApprovedRequest ? Number(latestApprovedRequest.id) : 0;
                const nextLatestId = Number(payload.latestRequestId ?? 0);
                const hasChanged = nextSignature !== latestApprovedRequestSignature
                    || currentLatestId !== nextLatestId;

                latestApprovedRequest = nextLatestRequest;
                latestApprovedRequestSignature = nextSignature;

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
        window.setInterval(refreshApprovedRequests, pollIntervalMs);

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                refreshApprovedRequests();
            }
        });
    })();
</script>

<script>
    (function () {
        const modal = document.getElementById('global-cancelled-modal');
        const closeButton = document.getElementById('global-cancelled-close');
        const list = document.getElementById('global-cancelled-list');
        const userId = @json((int) ($cancelledRequestMessageUserId ?? 0));
        const endpoint = @json(route('user.notifications.cancelled-requests'));
        const storageKey = `nia_ems_cancelled_requests_last_seen_id_user_${userId}`;
        const initialCancelled = @json($initialCancelledRequests);

        let latestCancelledRequest = Array.isArray(initialCancelled) && initialCancelled.length > 0
            ? initialCancelled[0]
            : null;
        let latestCancelledRequestSignature = latestCancelledRequest && Number(latestCancelledRequest.id) > 0
            ? String(latestCancelledRequest.id)
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

        function getLastSeenCancelledId() {
            try {
                const storedValue = Number(window.localStorage.getItem(storageKey) || 0);
                return Number.isFinite(storedValue) ? Math.max(0, Math.trunc(storedValue)) : 0;
            } catch (error) {
                return 0;
            }
        }

        function setLastSeenCancelledId(value) {
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

            if (!markAsSeen || !latestCancelledRequest) {
                return;
            }

            setLastSeenCancelledId(latestCancelledRequest.id);
        }

        function renderLatestCancelledRequest() {
            if (!latestCancelledRequest || Number(latestCancelledRequest.id) <= 0) {
                list.innerHTML = '<p class="rounded-xl border border-outline-variant/40 bg-surface-container-low p-4 text-sm text-on-surface-variant">No cancelled requests right now.</p>';
                return;
            }

            const attachments = Array.isArray(latestCancelledRequest.attachments)
                ? latestCancelledRequest.attachments
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
                '<div class="rounded-xl border border-outline-variant/50 bg-surface-container-low p-4" data-cancelled-request-id="' + escapeHtml(latestCancelledRequest.id) + '">',
                '<p class="text-sm font-bold text-on-surface">' + escapeHtml(latestCancelledRequest.formId || 'N/A') + ' was cancelled.</p>',
                '<p class="mt-1 text-sm font-semibold text-on-surface-variant">' + escapeHtml(latestCancelledRequest.cancellationReason || 'No cancellation reason provided.') + '</p>',
                attachmentsHtml,
                '</div>'
            ].join('');
        }

        function applyState() {
            renderLatestCancelledRequest();

            if (!latestCancelledRequest || Number(latestCancelledRequest.id) <= 0) {
                closeModal(false);
                return;
            }

            const lastSeenId = getLastSeenCancelledId();

            if (Number(latestCancelledRequest.id) > lastSeenId) {
                openModal();
                return;
            }

            closeModal(false);
        }

        async function refreshCancelledRequests() {
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
                const currentLatestId = latestCancelledRequest ? Number(latestCancelledRequest.id) : 0;
                const nextLatestId = Number(payload.latestRequestId ?? 0);
                const hasChanged = nextSignature !== latestCancelledRequestSignature
                    || currentLatestId !== nextLatestId;

                latestCancelledRequest = nextLatestRequest;
                latestCancelledRequestSignature = nextSignature;

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
        window.setInterval(refreshCancelledRequests, pollIntervalMs);

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                refreshCancelledRequests();
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
        const storageKey = `nia_ems_pending_evaluations_last_seen_latest_id_user_${userId}`;
        const legacyStorageKey = `nia_ems_pending_evaluations_seen_signature_user_${userId}`;
        let pendingEvaluationCount = @json((int) ($pendingUserEvaluationCount ?? 0));
        let pendingEvaluationSignature = @json((string) ($pendingUserEvaluationSignature ?? ''));
        let pendingLatestEvaluationId = extractLatestEvaluationId(pendingEvaluationSignature);

        if (!modal || !closeButton || !goButton || !description || userId <= 0) {
            return;
        }

        function updateDescription(count) {
            const normalizedCount = Number.isFinite(Number(count))
                ? Math.max(0, Math.trunc(Number(count)))
                : 0;

            description.textContent = `You have ${normalizedCount.toLocaleString('en-US')} trip evaluation${normalizedCount === 1 ? '' : 's'} waiting for submission.`;
        }

        function extractLatestEvaluationId(signature) {
            const raw = String(signature ?? '').trim();
            if (raw === '') {
                return 0;
            }

            const parts = raw.split('|');
            const candidate = parts.length >= 2 ? Number(parts[1]) : Number(parts[0]);

            if (!Number.isFinite(candidate)) {
                return 0;
            }

            return Math.max(0, Math.trunc(candidate));
        }

        function getLastSeenLatestEvaluationId() {
            try {
                const storedValue = window.localStorage.getItem(storageKey);
                const parsedValue = Number(storedValue || 0);

                if (Number.isFinite(parsedValue) && parsedValue > 0) {
                    return Math.max(0, Math.trunc(parsedValue));
                }

                const legacyStoredValue = window.localStorage.getItem(legacyStorageKey) || '';
                return extractLatestEvaluationId(legacyStoredValue);
            } catch (error) {
                return 0;
            }
        }

        function setLastSeenLatestEvaluationId(value) {
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

        function showModalWhenReady(attempt) {
            const returnedModal = document.getElementById('global-returned-modal');
            const approvedModal = document.getElementById('global-approved-modal');
            const cancelledModal = document.getElementById('global-cancelled-modal');
            const isReturnedModalOpen = returnedModal && !returnedModal.classList.contains('hidden');
            const isApprovedModalOpen = approvedModal && !approvedModal.classList.contains('hidden');
            const isCancelledModalOpen = cancelledModal && !cancelledModal.classList.contains('hidden');

            if ((isReturnedModalOpen || isApprovedModalOpen || isCancelledModalOpen) && attempt < 20) {
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

            if (!markAsSeen || pendingLatestEvaluationId <= 0) {
                return;
            }

            setLastSeenLatestEvaluationId(pendingLatestEvaluationId);
        }

        function shouldOpenModal() {
            if (pendingEvaluationCount <= 0 || pendingLatestEvaluationId <= 0) {
                return false;
            }

            const lastSeenLatestEvaluationId = getLastSeenLatestEvaluationId();
            return pendingLatestEvaluationId > lastSeenLatestEvaluationId;
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
                pendingLatestEvaluationId = extractLatestEvaluationId(nextSignature);

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
                if (!root) return;

                // accept either form or any container element
                const container = (root instanceof HTMLElement) ? root : (document.getElementById(String(root || '')) || null);
                if (!container) return;

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
                        // For file inputs, also clear any preview containers if data-preview-target set
                        const preview = el.getAttribute && el.getAttribute('data-preview-target');
                        if (preview) {
                            const p = document.getElementById(preview);
                            if (p) p.innerHTML = '';
                        }
                        return;
                    }

                    try { el.value = ''; } catch (e) { /* ignore */ }
                });
            } catch (error) {
                // swallow errors - helper must be safe
            }
        }

        // Expose globally for convenience
        window.emsClearFormInputs = clearFormInputs;

        // Helper to dispatch a clear-on-success event on a form element
        window.emsDispatchClearOnSuccess = function (form) {
            try {
                if (!form) return;
                const target = (form instanceof Event) ? (form.target || null) : form;
                const el = (target && target instanceof HTMLElement) ? target : (document.getElementById(String(target || '')) || null);
                if (!el) return;
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