@extends('layouts.error')

@section('title', '404 - Halaman Tidak Ditemukan')
@section('image_asset', asset('assets/compiled/svg/error-404.svg'))
@section('image_alt', '404 Halaman Tidak Ditemukan')
@section('heading', 'Halaman Tidak Ditemukan')
@section('message', 'Halaman yang Anda cari tidak tersedia, sudah dipindahkan, atau alamatnya tidak tepat.')
@section('actions')
    <a href="{{ url()->previous() }}" class="btn btn-lg btn-outline-primary">Kembali</a>
    <a href="{{ url('/') }}" class="btn btn-lg btn-primary">Ke Beranda</a>
@endsection
