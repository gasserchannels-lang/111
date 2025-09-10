<?php

// اختبار Controllers
arch('controllers')
    ->expect('App\Http\Controllers')
    ->toOnlyDependOn([
        'App\Models',
        'App\Services',
        'Illuminate\Http',
        'Illuminate\View',
        'Illuminate\Support',
    ]);

// اختبار Models
arch('models')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model');

// اختبار Services
arch('services')
    ->expect('App\Services')
    ->toOnlyDependOn([
        'App\Models',
        'Illuminate\Support',
        'Illuminate\Process',
    ]);

// اختبار Middleware
arch('middleware')
    ->expect('App\Http\Middleware')
    ->toOnlyDependOn([
        'Illuminate\Http',
        'Illuminate\Support',
        'Illuminate\Auth',
    ]);

// اختبار Providers
arch('providers')
    ->expect('App\Providers')
    ->toOnlyDependOn([
        'Illuminate\Support',
        'Illuminate\Foundation',
    ]);
