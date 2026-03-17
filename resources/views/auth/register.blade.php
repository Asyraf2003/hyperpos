@extends('layouts.auth')

@section('title', 'Register')

@section('content')
    <h1 class="auth-title">Register.</h1>
    <p class="auth-subtitle mb-5">Halaman register dummy untuk slice UI awal.</p>

    <form action="#" method="get">
        <div class="form-group position-relative has-icon-left mb-4">
            <input type="text" class="form-control form-control-xl" placeholder="Nama">
            <div class="form-control-icon">
                <i class="bi bi-person"></i>
            </div>
        </div>

        <div class="form-group position-relative has-icon-left mb-4">
            <input type="email" class="form-control form-control-xl" placeholder="Email">
            <div class="form-control-icon">
                <i class="bi bi-envelope"></i>
            </div>
        </div>

        <div class="form-group position-relative has-icon-left mb-4">
            <input type="password" class="form-control form-control-xl" placeholder="Password">
            <div class="form-control-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
        </div>

        <div class="d-grid gap-2 mt-4">
            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Daftar</a>
        </div>
    </form>

    <div class="text-center mt-5 text-lg fs-4">
        <p class="text-gray-600">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-bold">Login</a>
        </p>
    </div>
@endsection
