@extends('layouts.app')

@section('title', 'Pratinjau Bukti Pembayaran')
@section('heading', 'Pratinjau Bukti Pembayaran')
@section('back_url', $backUrl)

@section('content')
    <section class="section">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-1">{{ $originalFilename }}</h4>
                <p class="mb-0 text-muted">Tipe Berkas: {{ $mimeType }}</p>
            </div>

            <div class="card-body">
                @if ($isImagePreview)
                    <img
                        src="{{ $rawUrl }}"
                        alt="{{ $originalFilename }}"
                        class="img-fluid rounded border"
                    >
                @elseif ($isPdfPreview)
                    <div class="ratio ratio-4x3 border rounded overflow-hidden">
                        <iframe
                            src="{{ $rawUrl }}"
                            title="Pratinjau {{ $originalFilename }}"
                        ></iframe>
                    </div>
                @else
                    <div class="alert alert-light border mb-0">
                        Pratinjau langsung tidak tersedia untuk tipe berkas ini. Gunakan tombol unduh untuk membuka berkas.
                    </div>
                @endif

                <div class="ui-form-actions mt-3">
                    <a href="{{ $backUrl }}" class="btn btn-light-secondary">
                        Kembali
                    </a>

                    <a href="{{ $downloadUrl }}" class="btn btn-outline-secondary">
                        Unduh
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
