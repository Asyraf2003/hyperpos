@extends('layouts.error')

@section('title', '429 - Terlalu Banyak Permintaan')
@section('heading', 'Permintaan Terlalu Sering')
@section('message', 'Sistem menerima terlalu banyak permintaan dalam waktu singkat. Tunggu sebentar lalu coba lagi.')
@section('actions')
    <a href="{{ url()->previous() }}" class="btn btn-lg btn-outline-primary">Kembali</a>
    <a href="{{ url('/') }}" class="btn btn-lg btn-primary">Ke Beranda</a>
@endsection
