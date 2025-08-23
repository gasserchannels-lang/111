@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header text-center">
                    <h1 class="mb-2">مرحباً بكم في كوبرا - Welcome to COPRRA</h1>
                    <p class="text-muted mb-0">
                        <strong>كوبرا</strong> - منصة مقارنة الأسعار الشاملة<br>
                        <strong>COPRRA</strong> - COmparison PRice RAnge Platform
                    </p>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h2>قارن الأسعار بطريقة لم تشهدها من قبل</h2>
                        <h3>Compare Prices Like Never Before</h3>
                        <p class="lead">اعثر على أفضل العروض عبر متاجر متعددة ووفر المال في مشترياتك</p>
                        <p class="lead">Find the best deals across multiple stores and save money on your purchases</p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <h5 class="card-title">🌍 دعم متعدد اللغات</h5>
                                    <h6 class="text-muted">Multi-Language Support</h6>
                                    <p class="card-text">متوفر بأكثر من 20 لغة مع الاكتشاف التلقائي</p>
                                    <p class="card-text small">Available in 20+ languages with automatic detection</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <h5 class="card-title">💰 عملات متعددة</h5>
                                    <h6 class="text-muted">Multi-Currency</h6>
                                    <p class="card-text">الأسعار بأكثر من 25 عملة مع التحويل الفوري</p>
                                    <p class="card-text small">Prices in 25+ currencies with real-time conversion</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 border-info">
                                <div class="card-body text-center">
                                    <h5 class="card-title">🔍 مقارنة ذكية</h5>
                                    <h6 class="text-muted">Smart Comparison</h6>
                                    <p class="card-text">مقارنة متقدمة مع تمييز الميزات</p>
                                    <p class="card-text small">Advanced comparison with feature highlighting</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-body">
                                    <h5 class="card-title">🌙 الوضع الليلي والنهاري</h5>
                                    <h6 class="text-muted">Dark/Light Mode</h6>
                                    <p class="card-text">تبديل سهل بين الأوضاع حسب تفضيلك</p>
                                    <p class="card-text small">Easy switching between modes based on your preference</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-secondary">
                                <div class="card-body">
                                    <h5 class="card-title">📱 تصميم متجاوب</h5>
                                    <h6 class="text-muted">Responsive Design</h6>
                                    <p class="card-text">يعمل بسلاسة على جميع الأجهزة</p>
                                    <p class="card-text small">Works seamlessly on all devices</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info text-center">
                        <h6>الحالة الحالية - Current Status</h6>
                        <p class="mb-1">
                            <strong>اللغة الحالية - Current Language:</strong> 
                            @php
                                $currentLang = app()->getLocale();
                                $currentLanguage = \App\Models\Language::where('code', $currentLang)->first();
                            @endphp
                            {{ $currentLanguage ? $currentLanguage->native_name : 'English' }} ({{ $currentLang }})
                        </p>
                        <p class="mb-0">
                            <strong>العملة الحالية - Current Currency:</strong> 
                            @php
                                $currentCurrency = Session::get('locale_currency', 'USD');
                                $currency = \App\Models\Currency::where('code', $currentCurrency)->first();
                            @endphp
                            {{ $currency ? $currency->name : 'US Dollar' }} ({{ $currentCurrency }})
                        </p>
                    </div>

                    <div class="text-center">
                        <p class="text-muted">
                            استخدم القوائم المنسدلة في الأعلى لتغيير اللغة والعملة<br>
                            <small>Use the dropdown menus above to change language and currency</small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

