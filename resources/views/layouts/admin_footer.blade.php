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