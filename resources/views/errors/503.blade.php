@extends('layouts.error')

@section('title', '503 - Layanan Tidak Tersedia')
@section('heading', 'Layanan Sedang Tidak Tersedia')
@section('message', 'Sistem sedang sibuk atau dalam proses pemeliharaan. Silakan coba lagi beberapa saat lagi.')
@section('actions')
    <a href="{{ url()->current() }}" class="btn btn-lg btn-primary">Coba Lagi</a>
    <a href="{{ url('/') }}" class="btn btn-lg btn-outline-primary">Ke Beranda</a>
@endsection
