<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .admin-sidebar a.active { background: #2563eb; color: white; }
        .admin-sidebar a.active svg { color: white; }
    </style>
    @stack('head')
    @isset($head){!! $head !!}@endisset
    @if(request()->routeIs('admin.bookings.index'))
    @vite('resources/js/bookings.js')
    @endif
</head>
<body class="font-sans antialiased bg-slate-50" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        <!-- Backdrop (mobile only) -->
        <div x-show="sidebarOpen" x-transition @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-slate-900/50 md:hidden" style="display: none;"></div>

        <!-- Sidebar - spacious, clean design -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
               class="fixed md:static inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 shrink-0 flex flex-col transition-transform duration-200">
            <div class="px-6 py-6 border-b border-slate-200 flex items-center justify-between">
                <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-slate-800">NO5 Admin</a>
                <button @click="sidebarOpen = false" class="md:hidden p-2.5 rounded-lg hover:bg-slate-100 text-slate-500" aria-label="Close menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <nav class="px-6 py-7 space-y-3 admin-sidebar flex-1 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-4 px-4 py-4 rounded-xl {{ request()->routeIs('admin.dashboard') ? 'active' : 'hover:bg-slate-100 text-slate-700' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.services.index') }}" class="flex items-center gap-4 px-4 py-4 rounded-xl {{ request()->routeIs('admin.services.*') ? 'active' : 'hover:bg-slate-100 text-slate-700' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Services</span>
                </a>
                <a href="{{ route('admin.epos.index') }}" class="flex items-center gap-4 px-4 py-4 rounded-xl {{ request()->routeIs('admin.epos.*') ? 'active' : 'hover:bg-slate-100 text-slate-700' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <span>EPOS</span>
                </a>
                <a href="{{ route('admin.sales.index') }}" class="flex items-center gap-4 px-4 py-4 rounded-xl {{ request()->routeIs('admin.sales.*') ? 'active' : 'hover:bg-slate-100 text-slate-700' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>Sales & Accounting</span>
                </a>
                <a href="{{ route('admin.products.index') }}" class="flex items-center gap-4 px-4 py-4 rounded-xl {{ request()->routeIs('admin.products.*') ? 'active' : 'hover:bg-slate-100 text-slate-700' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>Products</span>
                </a>
                <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-4 px-4 py-4 rounded-xl {{ request()->routeIs('admin.categories.*') ? 'active' : 'hover:bg-slate-100 text-slate-700' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    <span>Categories</span>
                </a>
                <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-4 px-4 py-4 rounded-xl {{ request()->routeIs('admin.settings.*') ? 'active' : 'hover:bg-slate-100 text-slate-700' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Site Settings</span>
                </a>
                <a href="{{ route('admin.faqs.index') }}" class="flex items-center gap-4 px-4 py-4 rounded-xl {{ request()->routeIs('admin.faqs.*') ? 'active' : 'hover:bg-slate-100 text-slate-700' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>FAQs</span>
                </a>
                <a href="{{ route('admin.areas.index') }}" class="flex items-center gap-4 px-4 py-4 rounded-xl {{ request()->routeIs('admin.areas.*') ? 'active' : 'hover:bg-slate-100 text-slate-700' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Areas</span>
                </a>
                <a href="{{ route('admin.bookings.index') }}" class="flex items-center gap-4 px-4 py-4 rounded-xl {{ request()->routeIs('admin.bookings.*') ? 'active' : 'hover:bg-slate-100 text-slate-700' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Bookings</span>
                </a>
                <a href="{{ route('admin.export') }}" class="flex items-center gap-4 px-4 py-4 rounded-xl {{ request()->routeIs('admin.export') ? 'active' : 'hover:bg-slate-100 text-slate-700' }}" @click="sidebarOpen = false">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Export to Site</span>
                </a>
            </nav>
            <div class="mt-auto px-5 py-5 border-t border-slate-200">
                <span class="text-sm text-slate-500 block truncate">{{ Auth::user()->email }}</span>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-slate-900">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Log out
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 overflow-auto min-w-0 bg-slate-50">
            <header class="bg-white border-b border-slate-200 px-6 py-4">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="md:hidden p-2 rounded-lg hover:bg-slate-100 text-slate-600" aria-label="Open menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="flex-1 flex items-center justify-between gap-4">
                        @if(trim((string)($header ?? '')) !== '' && str_contains((string)$header, '<'))
                            {!! $header !!}
                        @else
                            <h1 class="text-xl font-semibold text-slate-800">{{ $header ?? 'Admin' }}</h1>
                        @endif
                    </div>
                </div>
            </header>
            <div class="p-6">
                @if (session('success'))
                    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-2">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-2">{{ session('error') }}</div>
                @endif
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
