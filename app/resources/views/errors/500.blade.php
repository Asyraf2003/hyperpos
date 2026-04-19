@extends('layouts.error')

@section('title', '500 - Gangguan Sistem')
@section('image_asset', asset('assets/compiled/svg/error-500.svg'))
@section('image_alt', '500 Gangguan Sistem')
@section('heading', 'Terjadi Gangguan pada Sistem')
@section('message', 'Permintaan Anda belum bisa diproses untuk saat ini. Coba lagi sebentar, lalu lanjutkan pekerjaan Anda seperti manusia yang dipaksa tetap tenang.')
@section('actions')
    <a href="{{ url()->current() }}" class="btn btn-lg btn-primary">Coba Lagi</a>
    <a href="{{ url()->previous() }}" class="btn btn-lg btn-outline-primary">Kembali</a>
@endsection

@section('note')
    Jika masalah ini terus berulang, catat waktu kejadian lalu laporkan ke admin internal agar jejak masalahnya bisa ditelusuri.
@endsection
