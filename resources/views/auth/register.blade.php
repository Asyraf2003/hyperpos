@extends('layouts.auth')

@section('title', 'Registration')

@section('content')
<h1 class="auth-title">Sign Up</h1>
<p class="auth-subtitle mb-5">Input your data to register to our website.</p>

<form action="{{ route('register') }}" method="POST">
    @csrf
    <div class="form-group position-relative has-icon-left mb-4">
        <input type="text" name="name" class="form-control form-control-xl" placeholder="Full Name">
        <div class="form-control-icon">
            <i class="bi bi-person"></i>
        </div>
    </div>
    <div class="form-group position-relative has-icon-left mb-4">
        <input type="email" name="email" class="form-control form-control-xl" placeholder="Email">
        <div class="form-control-icon">
            <i class="bi bi-envelope"></i>
        </div>
    </div>
    <div class="form-group position-relative has-icon-left mb-4">
        <input type="password" name="password" class="form-control form-control-xl" placeholder="Password">
        <div class="form-control-icon">
            <i class="bi bi-shield-lock"></i>
        </div>
    </div>
    <div class="form-group position-relative has-icon-left mb-4">
        <input type="password" name="password_confirmation" class="form-control form-control-xl" placeholder="Confirm Password">
        <div class="form-control-icon">
            <i class="bi bi-shield-check"></i>
        </div>
    </div>
    <button class="btn btn-primary btn-block btn-lg shadow-lg mt-5">Sign Up</button>
</form>
@endsection
