@extends('layouts.app')

@section('title', 'Detail Karyawan')
@section('heading', 'Detail Karyawan')

@section('content')
    <section class="section">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">{{ $page['heading'] }}</h4>
                <p class="text-muted mb-0">{{ $page['subtitle'] }}</p>
            </div>

            <div class="d-flex flex-column flex-sm-row gap-2">
                <a
                    href="{{ route('admin.employees.edit', ['employeeId' => $detail['summary']['id']]) }}"
                    class="btn btn-primary"
                >
                    Edit Karyawan
                </a>
            </div>
        </div>

        {{-- Start Row Utama --}}
        <div class="row g-4">
            
            {{-- Kolom Kiri: Detail & Data Awal --}}
            <div class="col-12 col-xl-5">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Ringkasan Karyawan</h5>
                        <p class="text-muted mb-0 mt-1">Identitas Saat Ini</p>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Nama Karyawan</small>
                            <div class="fw-semibold">{{ $page['current_identity']['employee_name'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Telepon</small>
                            <div class="fw-semibold">{{ $page['current_identity']['phone'] ?? '-' }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Basis Gaji</small>
                            <div class="fw-semibold">{{ $page['current_identity']['salary_basis_label'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Default Gaji</small>
                            <div class="fw-semibold">{{ $page['current_identity']['default_salary_amount_label'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Status</small>
                            <div class="fw-semibold">{{ $page['current_identity']['employment_status_label'] }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Mulai Kerja</small>
                            <div class="fw-semibold">{{ $page['current_identity']['started_at'] ?? '-' }}</div>
                        </div>

                        <div>
                            <small class="text-muted d-block">Berakhir</small>
                            <div class="fw-semibold">{{ $page['current_identity']['ended_at'] ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                @if ($page['initial_identity'] !== null || $page['initial_identity_meta']['note'] !== null)
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <h5 class="card-title mb-0">{{ $page['initial_identity_meta']['title'] }}</h5>
                                <span class="badge bg-light-{{ $page['initial_identity_meta']['badge_tone'] }} text-{{ $page['initial_identity_meta']['badge_tone'] }}">
                                    {{ $page['initial_identity_meta']['badge_label'] }}
                                </span>
                            </div>
                        </div>

                        <div class="card-body">
                            @if ($page['initial_identity_meta']['note'] !== null)
                                <div class="alert alert-light-{{ $page['initial_identity_meta']['badge_tone'] }} mb-4">
                                    {{ $page['initial_identity_meta']['note'] }}
                                </div>
                            @endif

                            @if ($page['initial_identity_meta']['show_values'] && $page['initial_identity'] !== null)
                                <div class="mb-3">
                                    <small class="text-muted d-block">Nama Karyawan</small>
                                    <div class="fw-semibold">{{ $page['initial_identity']['employee_name'] }}</div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted d-block">Telepon</small>
                                    <div class="fw-semibold">{{ $page['initial_identity']['phone'] ?? '-' }}</div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted d-block">Basis Gaji</small>
                                    <div class="fw-semibold">{{ $page['initial_identity']['salary_basis_label'] }}</div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted d-block">Default Gaji</small>
                                    <div class="fw-semibold">{{ $page['initial_identity']['default_salary_amount_label'] }}</div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted d-block">Status</small>
                                    <div class="fw-semibold">{{ $page['initial_identity']['employment_status_label'] }}</div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted d-block">Mulai Kerja</small>
                                    <div class="fw-semibold">{{ $page['initial_identity']['started_at'] ?? '-' }}</div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted d-block">Berakhir</small>
                                    <div class="fw-semibold">{{ $page['initial_identity']['ended_at'] ?? '-' }}</div>
                                </div>

                                <div>
                                    <small class="text-muted d-block">Tercatat Pada</small>
                                    <div class="fw-semibold">{{ $page['initial_identity']['changed_at'] }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-12 col-xl-7">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Riwayat Versi Karyawan</h5>
                    </div>

                    <div class="card-body">
                        @if (count($page['timeline']) === 0)
                            <p class="text-muted mb-0">Belum ada riwayat versi karyawan.</p>
                        @else
                            <div class="timeline">
                                @foreach ($page['timeline'] as $entry)
                                    <div class="timeline-item pb-4">
                                        <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-2">
                                            <div>
                                                <h6 class="mb-1">
                                                    {{ $entry['revision_label'] }} · {{ $entry['event_name'] }}
                                                </h6>
                                                <small class="text-muted">
                                                    {{ $entry['changed_at'] }}
                                                    @if ($entry['actor_label'])
                                                        · {{ $entry['actor_label'] }}
                                                    @endif
                                                </small>
                                            </div>

                                            @if ($entry['reason_label'])
                                                <span class="badge bg-light-info text-info align-self-start">
                                                    {{ $entry['reason_label'] }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="border rounded p-3 bg-light-subtle">
                                            <div class="row g-3">
                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Nama Karyawan</small>
                                                    <div class="fw-semibold">{{ $entry['snapshot']['employee_name'] }}</div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Telepon</small>
                                                    <div class="fw-semibold">{{ $entry['snapshot']['phone'] }}</div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Basis Gaji</small>
                                                    <div class="fw-semibold">{{ $entry['snapshot']['salary_basis_label'] }}</div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Default Gaji</small>
                                                    <div class="fw-semibold">{{ $entry['snapshot']['default_salary_amount_label'] }}</div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Status</small>
                                                    <div class="fw-semibold">{{ $entry['snapshot']['employment_status_label'] }}</div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Mulai Kerja</small>
                                                    <div class="fw-semibold">{{ $entry['snapshot']['started_at'] }}</div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <small class="text-muted d-block">Berakhir</small>
                                                    <div class="fw-semibold">{{ $entry['snapshot']['ended_at'] }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            {{-- End Kolom Kanan --}}

        </div>
        {{-- End Row Utama --}}
    </section>
@endsection