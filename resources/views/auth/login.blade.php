@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <h1 class="auth-title">Login</h1>
    <p class="auth-subtitle mb-5">Aplikasi internal. Registrasi publik tidak tersedia.</p>

    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('login.attempt') }}" method="post">
        @csrf

        <div class="form-group position-relative has-icon-left mb-4">
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                class="form-control form-control-xl @error('email') is-invalid @enderror"
                placeholder="Email"
                autocomplete="username"
                autofocus
            >
            <div class="form-control-icon">
                <i class="bi bi-person"></i>
            </div>
            @error('email')
                <div class="invalid-feedback d-block">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="form-group position-relative has-icon-left mb-4">
            <input
                type="password"
                name="password"
                class="form-control form-control-xl @error('password') is-invalid @enderror"
                placeholder="Password"
                autocomplete="current-password"
            >
            <div class="form-control-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="form-check form-check-lg d-flex align-items-end">
            <input
                class="form-check-input me-2"
                type="checkbox"
                name="remember"
                id="remember-me"
                value="1"
                {{ old('remember') ? 'checked' : '' }}
            >
            <label class="form-check-label text-gray-600" for="remember-me">
                Keep me logged in
            </label>
        </div>

        <div class="d-grid gap-2 mt-5">
            <button type="submit" class="btn btn-primary btn-lg shadow-lg">
                Masuk
            </button>
        </div>
    </form>
@endsection