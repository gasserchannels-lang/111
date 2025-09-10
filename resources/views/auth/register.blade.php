@extends('layouts.app')

@section('title', __('messages.register') . ' - ' . config('app.name'))

@section('content')
<div class="container py-5" style="max-width:480px">
    <h1 class="h3 mb-4">{{ __('messages.register') }}</h1>
    <form method="POST" action="#">
        @csrf
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">{{ __('messages.register') }}</button>
    </form>
</div>
@endsection


