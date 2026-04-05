@extends('layouts.error')

@section('title', '403 - Akses Ditolak')
@section('image_asset', asset('assets/compiled/svg/error-403.svg'))
@section('image_alt', '403 Akses Ditolak')
@section('heading', 'Akses Ditolak')
@section('message', 'Anda tidak memiliki izin untuk membuka halaman ini. Pastikan akun dan peran yang Anda gunakan memang sesuai.')
@section('actions')
    <a href="{{ url()->previous() }}" class="btn btn-lg btn-outline-primary">Kembali</a>
    <a href="{{ url('/') }}" class="btn btn-lg btn-primary">Ke Beranda</a>
@endsection
