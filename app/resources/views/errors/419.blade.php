@extends('layouts.error')

@section('title', '419 - Sesi Berakhir')
@section('heading', 'Sesi Anda Sudah Berakhir')
@section('message', 'Halaman ini perlu dimuat ulang sebelum Anda melanjutkan. Data yang belum tersimpan mungkin perlu Anda cek kembali.')
@section('actions')
    <a href="{{ url()->current() }}" class="btn btn-lg btn-primary">Muat Ulang Halaman</a>
    <a href="{{ url()->previous() }}" class="btn btn-lg btn-outline-primary">Kembali</a>
@endsection
