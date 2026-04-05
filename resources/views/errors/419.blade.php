@extends('layouts.error')

@section('title', '419 Sesi Berakhir')
@section('heading', 'Sesi Anda sudah berakhir')
@section('message', 'Halaman ini perlu dimuat ulang sebelum Anda melanjutkan.')
@section('actions')
    <a href="{{ url()->current() }}" class="btn btn-primary">Muat Ulang Halaman</a>
    <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
@endsection
