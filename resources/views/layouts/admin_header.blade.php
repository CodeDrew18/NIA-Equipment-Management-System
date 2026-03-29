<!-- TopAppBar -->
<header class="fixed top-0 z-50 w-full bg-slate-50/80 dark:bg-slate-900/80 backdrop-blur-md shadow-sm">
<style>
	.nav-link {
		position: relative;
		padding-bottom: 0.25rem;
		font-weight: 600;
		transition: color 220ms ease;
	}

	.nav-dropdown {
		position: relative;
	}

	.nav-dropdown-menu {
		position: absolute;
		top: calc(100% + 0.5rem);
		left: 0;
		min-width: 19rem;
		padding: 0.5rem;
		border-radius: 0.75rem;
		background: rgba(248, 250, 252, 0.98);
		backdrop-filter: blur(6px);
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.14);
		border: 1px solid rgba(148, 163, 184, 0.2);
		opacity: 0;
		visibility: hidden;
		transform: translateY(-8px) scale(0.98);
		transform-origin: top left;
		transition: opacity 220ms ease, transform 220ms ease, visibility 220ms ease;
		z-index: 60;
	}

	.dark .nav-dropdown-menu {
		background: rgba(15, 23, 42, 0.96);
		border-color: rgba(100, 116, 139, 0.3);
		box-shadow: 0 12px 28px rgba(2, 6, 23, 0.5);
	}

	.nav-dropdown:hover .nav-dropdown-menu,
	.nav-dropdown:focus-within .nav-dropdown-menu {
		opacity: 1;
		visibility: visible;
		transform: translateY(0) scale(1);
	}

	.nav-dropdown-item {
		display: block;
		padding: 0.55rem 0.75rem;
		font-size: 0.875rem;
		font-weight: 500;
		color: rgb(71 85 105);
		border-radius: 0.5rem;
		transition: background-color 180ms ease, color 180ms ease;
	}

	.dark .nav-dropdown-item {
		color: rgb(148 163 184);
	}

	.nav-dropdown-item:hover {
		background: rgb(219 234 254);
		color: rgb(30 64 175);
	}

	.dark .nav-dropdown-item:hover {
		background: rgba(30, 64, 175, 0.25);
		color: rgb(191 219 254);
	}

	.nav-caret {
		display: inline-block;
		margin-left: 0.35rem;
		transition: transform 220ms ease;
	}

	.nav-dropdown:hover .nav-caret,
	.nav-dropdown:focus-within .nav-caret {
		transform: rotate(180deg);
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

	.mobile-nav-subitem {
		display: block;
		padding: 0.45rem 0.75rem;
		margin-left: 0.5rem;
		border-radius: 0.45rem;
		font-size: 0.85rem;
		font-weight: 500;
		color: rgb(71 85 105);
		transition: background-color 180ms ease, color 180ms ease;
	}

	.dark .mobile-nav-subitem {
		color: rgb(148 163 184);
	}

	.mobile-nav-subitem:hover {
		background: rgb(226 232 240);
		color: rgb(30 64 175);
	}

	.dark .mobile-nav-subitem:hover {
		background: rgba(51, 65, 85, 0.7);
		color: rgb(191 219 254);
	}

	.mobile-nav-subitem-active {
		background: rgb(219 234 254);
		color: rgb(30 64 175);
	}

	.dark .mobile-nav-subitem-active {
		background: rgba(30, 64, 175, 0.25);
		color: rgb(191 219 254);
	}

	.mobile-nav-group summary {
		list-style: none;
	}

	.mobile-nav-group summary::-webkit-details-marker {
		display: none;
	}

	.mobile-nav-summary-caret {
		transition: transform 200ms ease;
	}

	.mobile-nav-group[open] .mobile-nav-summary-caret {
		transform: rotate(180deg);
	}
</style>
@php
	$operationsActive = request()->routeIs('admin.transportation-request') || request()->routeIs('admin.vehicle-availability') || request()->routeIs('admin.daily-trip-ticket');
	$reportsActive = request()->routeIs('reports');
	$evaluationsActive = request()->routeIs('evaluations');
	$transportationRequestActive = request()->routeIs('admin.transportation-request');
@endphp
<div class="flex justify-between items-center w-full px-8 py-4 max-w-full h-20">
<div class="flex items-center gap-8">
<span class="text-xl font-bold text-blue-900 dark:text-white tracking-tight">EMS</span>
<nav class="hidden lg:flex items-center gap-6">
<a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : 'text-slate-600 dark:text-slate-400 hover:text-blue-800 dark:hover:text-blue-200' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
<div class="nav-dropdown">
	<button type="button" class="nav-link {{ $operationsActive ? 'nav-link-active' : 'text-slate-600 dark:text-slate-400 hover:text-blue-800 dark:hover:text-blue-200' }}" aria-haspopup="true" aria-expanded="false">
		Operations <span class="nav-caret">&#9662;</span>
	</button>
	<div class="nav-dropdown-menu" role="menu" aria-label="Operations menu">
		<a class="nav-dropdown-item" href="{{ route('admin.vehicle-availability') }}" role="menuitem">Vehicle Availability</a>
            <a class="nav-dropdown-item {{ $transportationRequestActive ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200' : '' }}" href="{{ route('admin.transportation-request') }}" role="menuitem">Transportation Request</a>
		<a class="nav-dropdown-item" href="{{ route('admin.daily-trip-ticket') }}" role="menuitem">Daily Driver's Trip Ticket</a>
            {{-- <a class="nav-dropdown-item" href="#" role="menuitem">Daily Equipment Utilization Report</a> --}}
            {{-- <a class="nav-dropdown-item" href="#" role="menuitem">Fuel Issuance Slips</a> --}}
	</div>
</div>
<div class="nav-dropdown">
	<button type="button" class="nav-link {{ $reportsActive ? 'nav-link-active' : 'text-slate-600 dark:text-slate-400 hover:text-blue-800 dark:hover:text-blue-200' }}" aria-haspopup="true" aria-expanded="false">
		Reports <span class="nav-caret">&#9662;</span>
	</button>
	<div class="nav-dropdown-menu" role="menu" aria-label="Reports menu">
		<a class="nav-dropdown-item" href="#" role="menuitem">Monthly Equipment Utilization Report</a>
		<a class="nav-dropdown-item" href="#" role="menuitem">Monhtly Fuel Consumption Report (Service Vehicle)</a>
	</div>
</div>
<a class="nav-link {{ $evaluationsActive ? 'nav-link-active' : 'text-slate-600 dark:text-slate-400 hover:text-blue-800 dark:hover:text-blue-200' }}" href="{{ route('landing-page') }}">Evaluations</a>
</nav>
</div>
<button
	type="button"
	id="mobile-nav-toggle"
	class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-200/60 dark:hover:bg-slate-700/50 transition-colors"
	aria-label="Open navigation menu"
	aria-controls="mobile-nav-panel"
	aria-expanded="false"
>
	<span id="mobile-nav-icon" class="material-symbols-outlined">menu</span>
</button>
</div>
<div id="mobile-nav-panel" class="lg:hidden hidden px-8 pb-5">
	<div class="mt-1 rounded-xl border border-slate-200/70 dark:border-slate-700/70 bg-white/90 dark:bg-slate-800/90 p-3 shadow-sm space-y-1">
		<a class="mobile-nav-link {{ request()->routeIs('admin.dashboard') ? 'mobile-nav-link-active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>

		<details class="mobile-nav-group" {{ $operationsActive ? 'open' : '' }}>	
			<summary class="mobile-nav-link {{ $operationsActive ? 'mobile-nav-link-active' : '' }} flex items-center justify-between cursor-pointer">
				<span>Operations</span>
				<span class="mobile-nav-summary-caret">&#9662;</span>
			</summary>
			<div class="pt-1 pb-1 space-y-1">
				<a class="mobile-nav-subitem {{ request()->routeIs('admin.vehicle-availability') ? 'mobile-nav-subitem-active' : '' }}" href="{{ route('admin.vehicle-availability') }}">Vehicle Availability</a>
				<a class="mobile-nav-subitem {{ request()->routeIs('admin.transportation-request') ? 'mobile-nav-subitem-active' : '' }}" href="{{ route('admin.transportation-request') }}">Transportation Request</a>	
				<a class="mobile-nav-subitem {{ request()->routeIs('admin.daily-trip-ticket') ? 'mobile-nav-subitem-active' : '' }}" href="{{ route('admin.daily-trip-ticket') }}">Daily Driver's Trip Ticket</a>
			</div>
		</details>

		<details class="mobile-nav-group" {{ $reportsActive ? 'open' : '' }}>
			<summary class="mobile-nav-link {{ $reportsActive ? 'mobile-nav-link-active' : '' }} flex items-center justify-between cursor-pointer">
				<span>Reports</span>
				<span class="mobile-nav-summary-caret">&#9662;</span>
			</summary>
			<div class="pt-1 pb-1 space-y-1">
				<a class="mobile-nav-subitem" href="#">Monthly Equipment Utilization Report</a>
				<a class="mobile-nav-subitem" href="#">Monhtly Fuel Consumption Report (Service Vehicle)</a>
			</div>
		</details>

		<a class="mobile-nav-link {{ $evaluationsActive ? 'mobile-nav-link-active' : '' }}" href="{{ route('landing-page') }}">Evaluations</a>
	</div>
</div>
</header>
<script>
	(function () {
		const toggleButton = document.getElementById('mobile-nav-toggle');
		const panel = document.getElementById('mobile-nav-panel');
		const icon = document.getElementById('mobile-nav-icon');
		const closeSelectors = 'a, button, summary';

		if (!toggleButton || !panel || !icon) {
			return;
		}

		function setMenuState(isOpen) {
			panel.classList.toggle('hidden', !isOpen);
			toggleButton.setAttribute('aria-expanded', String(isOpen));
			icon.textContent = isOpen ? 'close' : 'menu';
		}

		toggleButton.addEventListener('click', function () {
			const willOpen = panel.classList.contains('hidden');
			setMenuState(willOpen);
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape') {
				setMenuState(false);
			}
		});

		document.addEventListener('click', function (event) {
			if (panel.classList.contains('hidden')) {
				return;
			}

			const clickInsidePanel = panel.contains(event.target);
			const clickToggle = toggleButton.contains(event.target);

			if (!clickInsidePanel && !clickToggle) {
				setMenuState(false);
			}
		});

		panel.addEventListener('click', function (event) {
			const actionTarget = event.target.closest(closeSelectors);
			if (actionTarget && actionTarget.tagName === 'A') {
				setMenuState(false);
			}
		});
	})();
</script>