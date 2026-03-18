@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <h1 class="auth-title">Log in.</h1>
    <p class="auth-subtitle mb-5">Aplikasi internal. Akses akun dikelola secara terbatas.</p>

    <form action="#" method="get">
        <div class="form-group position-relative has-icon-left mb-4">
            <input type="text" class="form-control form-control-xl" placeholder="Email">
            <div class="form-control-icon">
                <i class="bi bi-person"></i>
            </div>
        </div>

        <div class="form-group position-relative has-icon-left mb-4">
            <input type="password" class="form-control form-control-xl" placeholder="Password">
            <div class="form-control-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
        </div>

        <div class="form-check form-check-lg d-flex align-items-end">
            <input class="form-check-input me-2" type="checkbox" id="remember-me">
            <label class="form-check-label text-gray-600" for="remember-me">
                Remember Me
            </label>
        </div>

        <div class="d-grid gap-2 mt-4">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg">Masuk sebagai Admin</a>
            <a href="{{ route('cashier.dashboard') }}" class="btn btn-outline-primary btn-lg">Masuk sebagai Kasir</a>
        </div>
    </form>

    <div class="text-center mt-5 text-lg fs-6">
        <p class="text-gray-600 mb-0">
            Registrasi publik dinonaktifkan untuk aplikasi internal.
        </p>
    </div>
@endsection
