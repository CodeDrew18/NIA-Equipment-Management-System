<!DOCTYPE html>

<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>NIA Equipment Management System - Institutional Access</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#1A4B84",
                        "error": "#ba1a1a",
                        "surface": "#f7f9fc",
                        "on-surface": "#191c1e",
                        "outline": "#737781",
                        "secondary": "#3a6843",
                    },
                    fontFamily: {
                        "headline": ["Public Sans"],
                        "body": ["Public Sans"],
                        "label": ["Public Sans"]
                    },
                    borderRadius: {"DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "2xl": "1rem", "full": "9999px"},
                },
            },
        }
    </script>
<style>
        body { font-family: 'Public Sans', sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .login-inner-card {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
</head>
<body class="bg-surface text-on-surface min-h-screen flex flex-col relative overflow-x-hidden font-body">
<!-- Background Layer -->
<div class="absolute inset-0 z-0">
<img alt="National Irrigation Administration service fleet" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCZFFaEQjIyF6Jdu6iPa7Fctb-VS4XrMc2ZSy_EdI8IQJXPzY6NdXqSw66So0yI4pFgScatzKVDoWp-JWpZsElZv3g6Oz7urpCCjo8GIXnncnpMDsuU_rcZQ0r9s3iUZ8wYVKa-o30g6KXTVP4l-8wZ05n4C1k7_uvVPl8DEOrSwW-6R9giPwphFNJv_4BzngA3oXSmKxQW5aXCh-IRc8LkpsMXCls4D10lkhORlagUMM9ErEgpe-YHN2xmR9n8n7e5ofR11x55_mph"/>
<div class="absolute inset-0 bg-black/40"></div>
</div>
<!-- Main Container (Matching layout style of IMAGE_4) -->
<main class="relative z-10 w-full max-w-6xl mx-auto px-6 py-12 flex-1 flex items-center">
<div class="glass-card rounded-[2rem] overflow-hidden flex flex-col md:flex-row shadow-2xl min-h-[600px]">
<!-- Left Section (Text placement similar to IMAGE_4) -->
<div class="w-full md:w-1/2 p-12 md:p-16 flex flex-col justify-start">
<div class="mb-12">
{{-- <div class="w-20 h-20 bg-white/90 rounded-2xl flex items-center justify-center p-3 shadow-lg mb-8">
<img alt="NIA Logo" class="w-full h-full object-contain" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBnmkNQv2L_kemAR0Ff1UHAZ1M-tEXU96_z5Nx4Tn1zEnyGp1QZ_Tfb7HFwYtm2xGwXzfllJ8dC-jHWIaeCqwBJz-ENit_pTMJ8uFuKqdM-we3z3nLyJxlMDZeFg44a5q50UjEIXlmHrnIQxpoOt5LCpYI-qK6iGKYiC0zs7bJ8EbfXMEHMUW_jXmMh-dlhmqPhpvDGohBL9eUml5sgXng8D3m4w7oAYAvs4d8u0TzeUPcyuBNn0Cvceqx8lmbLxaPvT7K65wxGeiKI"/>
</div> --}}
<p class="text-white/80 font-bold tracking-[0.3em] uppercase text-sm mb-4">Institutional Access</p>
<h1 class="text-5xl md:text-6xl font-extrabold text-white leading-tight mb-6">EQUIPMENT<br/>MANAGEMENT SYSTEM</h1>
<p class="text-white/70 text-lg max-w-md leading-relaxed">
                    Precision oversight for the National Irrigation Administration's critical fleet and machinery assets.
                </p>
</div>
<!-- Modern Status Badge -->
<div class="mt-auto inline-flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-2 rounded-full w-fit border border-white/20">
<span class="relative flex h-2.5 w-2.5">
<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
<span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-400"></span>
</span>
<span class="text-[11px] font-bold text-white uppercase tracking-[0.2em]">Secure Server Active</span>
</div>
</div>
<!-- Right Section (Login Card matching IMAGE_4 style) -->
<div class="w-full md:w-1/2 flex items-center justify-center p-8 md:p-12">
<div class="login-inner-card w-full max-w-md rounded-3xl p-10 md:p-12 border border-white/20 shadow-xl">
<form action="{{ route('login.authenticate') }}" method="POST" class="space-y-8">
@csrf
@if ($errors->any())
<div class="rounded-xl bg-error/90 px-4 py-3 text-sm font-semibold text-white">
{{ $errors->first() }}
</div>
@endif
<div class="space-y-2">
<label class="block text-sm font-medium text-white/90">User ID (6 Digits)</label>
<input name="personnel_id" value="{{ old('personnel_id') }}" class="w-full bg-white text-on-surface border-none py-4 px-5 rounded-xl shadow-inner text-lg font-mono tracking-widest placeholder:text-gray-400 focus:ring-2 focus:ring-primary/50 transition-all" maxlength="6" placeholder="000000" type="text"/>
</div>
<div class="space-y-2">
<div class="flex justify-between items-center">
<label class="block text-sm font-medium text-white/90">Password</label>
<a class="text-xs font-semibold text-white/80 hover:text-white underline decoration-white/30" href="#">Forgot password?</a>
</div>
<input name="password" class="w-full bg-white text-on-surface border-none py-4 px-5 rounded-xl shadow-inner text-lg placeholder:text-gray-400 focus:ring-2 focus:ring-primary/50 transition-all" placeholder="••••••••" type="password"/>
</div>
<button class="w-full bg-[#3B82F6] hover:bg-blue-500 text-white font-bold py-4 rounded-xl shadow-lg transition-all duration-300 transform active:scale-[0.98] uppercase tracking-widest text-sm" type="submit">
                        Sign In
                    </button>
<div class="relative flex items-center py-4">
<div class="flex-grow border-t border-white/20"></div>
<span class="flex-shrink mx-4 text-white/40 text-xs font-bold uppercase tracking-widest">Authorized Personnel Only</span>
<div class="flex-grow border-t border-white/20"></div>
</div>
<div class="flex items-center justify-center gap-2">
<label class="flex items-center gap-3 cursor-pointer group">
<input name="remember" class="rounded-md border-white/20 bg-white/10 text-primary focus:ring-primary w-5 h-5 cursor-pointer" type="checkbox" value="1"/>
<span class="text-sm font-medium text-white/80 group-hover:text-white transition-colors">Keep me signed in</span>
</label>
</div>
</form>
</div>
</div>
</div>
</main>
{{-- <!-- Global Footer -->
<div class="relative z-10 w-full">
@include('layouts.footer')
</div> --}}
</body></html>