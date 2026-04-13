<!-- Landing TopAppBar -->
<header data-landing-header="true" class="fixed top-0 z-50 w-full bg-slate-50/80 dark:bg-slate-900/80 backdrop-blur-md shadow-sm">
<style>
    .nav-link {
        position: relative;
        padding-bottom: 0.25rem;
        font-weight: 600;
        transition: color 220ms ease;
    }

    .nav-link::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: 0;
        height: 2px;
        width: 100%;
        transform: scaleX(0);
        transform-origin: left;
        background-color: currentColor;
        transition: transform 280ms ease;
    }

    .nav-link:hover::after {
        transform: scaleX(1);
    }

    .nav-link-active {
        color: rgb(30 64 175);
        animation: navActivePop 320ms ease-out;
    }

    .dark .nav-link-active {
        color: rgb(147 197 253);
    }

    .nav-link-active::after {
        transform: scaleX(1);
        animation: navActiveGlow 450ms ease-out;
    }

    @keyframes navActivePop {
        0% {
            transform: translateY(-2px);
            opacity: 0.7;
        }

        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes navActiveGlow {
        0% {
            opacity: 0;
            filter: blur(1px);
        }

        100% {
            opacity: 1;
            filter: blur(0);
        }
    }

    .mobile-nav-link {
        display: block;
        padding: 0.6rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.925rem;
        font-weight: 600;
        color: rgb(51 65 85);
        transition: background-color 180ms ease, color 180ms ease;
    }

    .dark .mobile-nav-link {
        color: rgb(203 213 225);
    }

    .mobile-nav-link:hover {
        background: rgb(226 232 240);
        color: rgb(30 64 175);
    }

    .dark .mobile-nav-link:hover {
        background: rgba(51, 65, 85, 0.7);
        color: rgb(191 219 254);
    }

    .mobile-nav-link-active {
        background: rgb(219 234 254);
        color: rgb(30 64 175);
    }

    .dark .mobile-nav-link-active {
        background: rgba(30, 64, 175, 0.25);
        color: rgb(191 219 254);
    }
</style>
<div class="flex justify-between items-center w-full px-8 py-4 max-w-full h-20">
    <div class="flex items-center gap-8">
        <a href="{{ route('landing-page') }}" class="text-xl font-bold text-blue-900 dark:text-white tracking-tight">EMS</a>
        <nav class="hidden lg:flex items-center gap-6">
            <a class="nav-link nav-link-active text-slate-600 dark:text-slate-400 hover:text-blue-800 dark:hover:text-blue-200" href="#home-section" data-section-link="home-section">Home</a>
            <a class="nav-link text-slate-600 dark:text-slate-400 hover:text-blue-800 dark:hover:text-blue-200" href="#operations-section" data-section-link="operations-section">Operations</a>
            <a class="nav-link text-slate-600 dark:text-slate-400 hover:text-blue-800 dark:hover:text-blue-200" href="#reports-section" data-section-link="reports-section">Reports</a>
            <a class="nav-link text-slate-600 dark:text-slate-400 hover:text-blue-800 dark:hover:text-blue-200" href="#evaluation-section" data-section-link="evaluation-section">Evaluation</a>
        </nav>
    </div>

    <div class="flex items-center gap-3">
        <a href="{{ route('login') }}" class="hidden lg:inline-flex items-center justify-center px-4 py-2 rounded-lg font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-200/70 dark:hover:bg-slate-700/60 transition-colors">
            Login
        </a>
        <button
            type="button"
            id="landing-mobile-nav-toggle"
            class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-200/60 dark:hover:bg-slate-700/50 transition-colors"
            aria-label="Open navigation menu"
            aria-controls="landing-mobile-nav-panel"
            aria-expanded="false"
        >
            <span id="landing-mobile-nav-icon" class="material-symbols-outlined">menu</span>
        </button>
    </div>
</div>

<div id="landing-mobile-nav-panel" class="lg:hidden hidden px-8 pb-5">
    <div class="mt-1 rounded-xl border border-slate-200/70 dark:border-slate-700/70 bg-white/90 dark:bg-slate-800/90 p-3 shadow-sm space-y-1">
        <a class="mobile-nav-link mobile-nav-link-active" href="#home-section" data-mobile-section-link="home-section">Home</a>
        <a class="mobile-nav-link" href="#operations-section" data-mobile-section-link="operations-section">Operations</a>
        <a class="mobile-nav-link" href="#reports-section" data-mobile-section-link="reports-section">Reports</a>
        <a class="mobile-nav-link" href="#evaluation-section" data-mobile-section-link="evaluation-section">Evaluation</a>
        <a href="{{ route('login') }}" class="mobile-nav-link mt-2 border border-slate-200/70 dark:border-slate-700/70 text-center">Login</a>
    </div>
</div>
</header>

<script>
    (function () {
        const toggleButton = document.getElementById('landing-mobile-nav-toggle');
        const panel = document.getElementById('landing-mobile-nav-panel');
        const icon = document.getElementById('landing-mobile-nav-icon');
        const header = document.querySelector('header[data-landing-header="true"]');
        const sectionIds = ['home-section', 'operations-section', 'reports-section', 'evaluation-section'];
        const desktopLinks = Array.from(document.querySelectorAll('[data-section-link]'));
        const mobileLinks = Array.from(document.querySelectorAll('[data-mobile-section-link]'));

        function closeMobilePanel() {
            if (!toggleButton || !panel || !icon) {
                return;
            }

            panel.classList.add('hidden');
            toggleButton.setAttribute('aria-expanded', 'false');
            icon.textContent = 'menu';
        }

        function setActiveSection(sectionId) {
            desktopLinks.forEach(function (link) {
                link.classList.toggle('nav-link-active', link.getAttribute('data-section-link') === sectionId);
            });

            mobileLinks.forEach(function (link) {
                link.classList.toggle('mobile-nav-link-active', link.getAttribute('data-mobile-section-link') === sectionId);
            });
        }

        function scrollToSection(sectionId) {
            const section = document.getElementById(sectionId);
            if (!section) {
                return;
            }

            const headerOffset = (header ? header.offsetHeight : 80) + 12;
            const targetTop = section.getBoundingClientRect().top + window.scrollY - headerOffset;

            window.scrollTo({
                top: targetTop,
                behavior: 'smooth',
            });
        }

        function handleSectionLinkClick(event) {
            const sectionId = this.getAttribute('data-section-link') || this.getAttribute('data-mobile-section-link');
            const section = sectionId ? document.getElementById(sectionId) : null;

            if (!section) {
                return;
            }

            event.preventDefault();
            scrollToSection(sectionId);
            setActiveSection(sectionId);
            closeMobilePanel();

            if (window.history && typeof window.history.replaceState === 'function') {
                window.history.replaceState(null, '', '#' + sectionId);
            }
        }

        desktopLinks.forEach(function (link) {
            link.addEventListener('click', handleSectionLinkClick);
        });

        mobileLinks.forEach(function (link) {
            link.addEventListener('click', handleSectionLinkClick);
        });

        if (toggleButton && panel && icon) {
            toggleButton.addEventListener('click', function () {
                const isHidden = panel.classList.contains('hidden');
                panel.classList.toggle('hidden');
                toggleButton.setAttribute('aria-expanded', String(isHidden));
                icon.textContent = isHidden ? 'close' : 'menu';
            });
        }

        const observer = new IntersectionObserver(function (entries) {
            const visibleEntries = entries
                .filter(function (entry) {
                    return entry.isIntersecting;
                })
                .sort(function (a, b) {
                    return b.intersectionRatio - a.intersectionRatio;
                });

            if (visibleEntries.length > 0) {
                setActiveSection(visibleEntries[0].target.id);
            }
        }, {
            rootMargin: '-40% 0px -45% 0px',
            threshold: [0.2, 0.35, 0.5, 0.7],
        });

        sectionIds.forEach(function (sectionId) {
            const section = document.getElementById(sectionId);
            if (section) {
                observer.observe(section);
            }
        });

        const hashSectionId = window.location.hash.replace('#', '');
        if (sectionIds.includes(hashSectionId) && document.getElementById(hashSectionId)) {
            setActiveSection(hashSectionId);
            window.setTimeout(function () {
                scrollToSection(hashSectionId);
            }, 0);
        } else {
            setActiveSection('home-section');
        }
    })();
</script>
