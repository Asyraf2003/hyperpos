@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <h1 class="auth-title">Log in.</h1>
    <p class="auth-subtitle mb-5">Aplikasi internal. Registrasi publik tidak tersedia.</p>

    <form action="#" method="get">
        <div class="form-group position-relative has-icon-left mb-4">
            <input type="text" name="email" class="form-control form-control-xl" placeholder="Email/Username">
            <div class="form-control-icon">
                <i class="bi bi-person"></i>
            </div>
        </div>

        <div class="form-group position-relative has-icon-left mb-4">
            <input type="password" name="password" class="form-control form-control-xl" placeholder="Password">
            <div class="form-control-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
        </div>

        <div class="form-check form-check-lg d-flex align-items-end">
            <input class="form-check-input me-2" type="checkbox" name="remember" id="remember-me">
            <label class="form-check-label text-gray-600" for="remember-me">
                Keep me logged in
            </label>
        </div>

        <div class="d-grid gap-2 mt-5">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg shadow-lg">
                Masuk sebagai Admin
            </a>
            <a href="{{ route('cashier.dashboard') }}" class="btn btn-outline-primary btn-lg">
                Masuk sebagai Kasir
            </a>
        </div>
    </form>
@endsection
