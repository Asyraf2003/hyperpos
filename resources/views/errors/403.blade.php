@extends('layouts.error')

@section('title', '403 Akses Ditolak')
@section('heading', 'Akses ke halaman ini ditolak')
@section('message', 'Anda tidak memiliki izin untuk membuka halaman ini.')
@section('actions')
    <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
    <a href="{{ url('/') }}" class="btn btn-primary">Ke Beranda</a>
@endsection
