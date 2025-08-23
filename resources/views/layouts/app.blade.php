<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' || app()->getLocale() == 'ur' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'COPRRA') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --coprra-navy: #1e3a8a;
            --coprra-blue: #3b82f6;
            --coprra-cream: #fef7ed;
            --coprra-light-blue: #dbeafe;
            --coprra-dark-navy: #1e40af;
        }

        /* Light Mode (Default) */
        body {
            background-color: var(--coprra-cream);
            color: var(--coprra-navy);
            font-family: 'Figtree', sans-serif;
        }

        .navbar {
            background-color: var(--coprra-navy) !important;
        }

        .navbar-brand {
            color: white !important;
            font-weight: 600;
            font-size: 1.5rem;
        }

        .navbar-nav .nav-link {
            color: white !important;
        }

        .btn-primary {
            background-color: var(--coprra-blue);
            border-color: var(--coprra-blue);
        }

        .btn-primary:hover {
            background-color: var(--coprra-dark-navy);
            border-color: var(--coprra-dark-navy);
        }

        .card {
            background-color: white;
            border: 1px solid var(--coprra-light-blue);
        }

        /* Dark Mode */
        .dark-mode body {
            background-color: #1f2937;
            color: #f9fafb;
        }

        .dark-mode .navbar {
            background-color: #111827 !important;
        }

        .dark-mode .card {
            background-color: #374151;
            color: #f9fafb;
            border-color: #4b5563;
        }

        .dark-mode .btn-primary {
            background-color: var(--coprra-blue);
            border-color: var(--coprra-blue);
        }

        /* RTL Support */
        [dir="rtl"] .navbar-nav {
            margin-right: auto;
            margin-left: 0;
        }

        [dir="rtl"] .dropdown-menu {
            right: 0;
            left: auto;
        }

        /* Language and Currency Selectors */
        .locale-selector {
            min-width: 120px;
        }

        .locale-selector .dropdown-toggle {
            background-color: transparent;
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
        }

        .locale-selector .dropdown-toggle:hover {
            background-color: rgba(255,255,255,0.1);
        }

        /* Theme Toggle Button */
        .theme-toggle {
            background: none;
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            cursor: pointer;
        }

        .theme-toggle:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }

        /* COPRRA Branding */
        .coprra-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .coprra-acronym {
            font-size: 0.75rem;
            opacity: 0.8;
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand coprra-brand" href="{{ url('/') }}">
                <span>كوبرا - COPRRA</span>
                <small class="coprra-acronym">(COmparison PRice RAnge)</small>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}">{{ __('Home') }}</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <!-- Language Selector -->
                    <div class="dropdown locale-selector">
                        <button class="btn dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown">
                            @php
                                $currentLang = app()->getLocale();
                                $currentLanguage = \App\Models\Language::where('code', $currentLang)->first();
                            @endphp
                            {{ $currentLanguage ? $currentLanguage->native_name : 'English' }}
                        </button>
                        <ul class="dropdown-menu">
                            @foreach(\App\Models\Language::active()->ordered()->get() as $language)
                                <li>
                                    <a class="dropdown-item" href="{{ route('change.language', $language->code) }}">
                                        {{ $language->native_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Currency Selector -->
                    <div class="dropdown locale-selector">
                        <button class="btn dropdown-toggle" type="button" id="currencyDropdown" data-bs-toggle="dropdown">
                            @php
                                $currentCurrency = Session::get('locale_currency', 'USD');
                                $currency = \App\Models\Currency::where('code', $currentCurrency)->first();
                            @endphp
                            {{ $currency ? $currency->code : 'USD' }}
                        </button>
                        <ul class="dropdown-menu">
                            @php
                                $currentLangId = $currentLanguage ? $currentLanguage->id : 1;
                                $availableCurrencies = \App\Models\Currency::whereHas('languages', function($query) use ($currentLangId) {
                                    $query->where('language_id', $currentLangId);
                                })->active()->ordered()->get();
                            @endphp
                            @foreach($availableCurrencies as $currency)
                                <li>
                                    <a class="dropdown-item" href="{{ route('change.currency', $currency->code) }}">
                                        {{ $currency->code }} ({{ $currency->symbol }})
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Theme Toggle -->
                    <button id="theme-toggle" class="theme-toggle">
                        🌙/☀️
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-4">
        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Theme Switcher -->
    <script src="{{ asset('js/theme-switcher.js') }}"></script>
</body>
</html>

