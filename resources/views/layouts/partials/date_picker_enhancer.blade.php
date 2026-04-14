<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar {
        border: 1px solid rgba(148, 163, 184, 0.25);
        border-radius: 0.9rem;
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.16);
        overflow: hidden;
    }

    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange,
    .flatpickr-day.selected:hover,
    .flatpickr-day.startRange:hover,
    .flatpickr-day.endRange:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
    }

    .flatpickr-day.today {
        border-color: #1d4ed8;
    }

    .flatpickr-months .flatpickr-month,
    .flatpickr-current-month .flatpickr-monthDropdown-months,
    .flatpickr-current-month input.cur-year {
        font-weight: 600;
    }

    .ems-flatpickr-input {
        background-color: rgba(248, 250, 252, 0.9);
        transition: box-shadow 180ms ease, border-color 180ms ease, background-color 180ms ease;
    }

    .ems-flatpickr-input:focus {
        outline: none;
        border-color: rgba(37, 99, 235, 0.85);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        background-color: #ffffff;
    }

    .dark .flatpickr-calendar {
        background: #0f172a;
        border-color: rgba(100, 116, 139, 0.35);
        box-shadow: 0 16px 34px rgba(2, 6, 23, 0.55);
    }

    .dark .flatpickr-months,
    .dark .flatpickr-weekdays,
    .dark .flatpickr-day,
    .dark .numInputWrapper input,
    .dark .flatpickr-time input {
        color: #e2e8f0;
        fill: #e2e8f0;
    }

    .dark .flatpickr-day:hover {
        background: rgba(59, 130, 246, 0.25);
        border-color: rgba(59, 130, 246, 0.25);
    }

    .dark .flatpickr-day.selected,
    .dark .flatpickr-day.startRange,
    .dark .flatpickr-day.endRange,
    .dark .flatpickr-day.selected:hover,
    .dark .flatpickr-day.startRange:hover,
    .dark .flatpickr-day.endRange:hover {
        background: #2563eb;
        border-color: #2563eb;
    }

    .dark .ems-flatpickr-input {
        background-color: rgba(30, 41, 59, 0.92);
    }

    .dark .ems-flatpickr-input:focus {
        border-color: rgba(147, 197, 253, 0.95);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        background-color: rgba(15, 23, 42, 0.95);
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
(function () {
    if (window.__emsDatePickerEnhancerInitialized) {
        return;
    }

    window.__emsDatePickerEnhancerInitialized = true;

    function enhanceInput(input) {
        if (!input || input.dataset.dateEnhanced === 'true' || typeof window.flatpickr !== 'function') {
            return;
        }

        const inputType = String(input.getAttribute('type') || '').toLowerCase();
        const withTime = inputType === 'datetime-local';

        const options = {
            allowInput: true,
            altInput: true,
            altFormat: withTime ? 'M j, Y h:i K' : 'F j, Y',
            dateFormat: withTime ? 'Y-m-d\\TH:i' : 'Y-m-d',
            disableMobile: true,
            enableTime: withTime,
            clickOpens: !(input.readOnly || input.disabled),
            minuteIncrement: withTime ? 5 : 1,
            time_24hr: false,
            defaultDate: input.value || null,
        };

        const minValue = input.getAttribute('min');
        const maxValue = input.getAttribute('max');

        if (minValue) {
            options.minDate = minValue;
        }

        if (maxValue) {
            options.maxDate = maxValue;
        }

        const picker = window.flatpickr(input, options);
        input.dataset.dateEnhanced = 'true';

        if (picker && picker.altInput) {
            picker.altInput.classList.add('ems-flatpickr-input');

            if (input.placeholder) {
                picker.altInput.setAttribute('placeholder', input.placeholder);
            }

            if (input.disabled) {
                picker.altInput.setAttribute('disabled', 'disabled');
            }

            if (input.readOnly) {
                picker.altInput.setAttribute('readonly', 'readonly');
            }
        }
    }

    function enhanceRoot(root) {
        const dateInputs = root.querySelectorAll('input[type="date"], input[type="datetime-local"]');

        dateInputs.forEach(function (input) {
            enhanceInput(input);
        });
    }

    function initializeEnhancer() {
        enhanceRoot(document);

        if (!document.body || typeof MutationObserver !== 'function') {
            return;
        }

        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (!(node instanceof HTMLElement)) {
                        return;
                    }

                    if (node.matches && (node.matches('input[type="date"]') || node.matches('input[type="datetime-local"]'))) {
                        enhanceInput(node);
                    }

                    if (node.querySelectorAll) {
                        enhanceRoot(node);
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    function runWhenFlatpickrReady() {
        let attempts = 0;
        const maxAttempts = 20;

        function tryInitialize() {
            if (typeof window.flatpickr === 'function') {
                initializeEnhancer();
                return;
            }

            attempts += 1;
            if (attempts >= maxAttempts) {
                return;
            }

            window.setTimeout(tryInitialize, 150);
        }

        tryInitialize();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runWhenFlatpickrReady);
    } else {
        runWhenFlatpickrReady();
    }
})();
</script>
