<div class="card shadow-sm border-0 rounded-4 overflow-hidden">
    @php
        $activeFilter = null;
        $filterMessage = '';
        
        if(request('today_only')) {
            $activeFilter = 'today';
            $filterMessage = 'Menampilkan data hari ini saja (' . today()->format('d M Y') . ')';
        } elseif(request('week_only')) {
            $activeFilter = 'week';
            $filterMessage = 'Menampilkan data minggu ini (' . now()->startOfWeek()->format('d M') . ' - ' . now()->endOfWeek()->format('d M Y') . ')';
        } elseif(request('month_only')) {
            $activeFilter = 'month';
            $filterMessage = 'Menampilkan data bulan ini (' . now()->format('F Y') . ')';
        } elseif(request('date_specific')) {
            $activeFilter = 'specific';
            $filterMessage = 'Menampilkan data tanggal ' . \Carbon\Carbon::parse(request('date_specific'))->format('d M Y');
        }
    @endphp
    
    @if($activeFilter)
        <div class="alert alert-info border-0 rounded-0 mb-0" role="alert">
            <i class="fas fa-calendar-{{ $activeFilter == 'today' ? 'day' : ($activeFilter == 'week' ? 'week' : ($activeFilter == 'month' ? 'alt' : 'check')) }}"></i> 
            <strong>Filter Aktif:</strong> {{ $filterMessage }}
        </div>
    @endif
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-dark">
                        <th class="fw-bold px-4">No</th>
                        <th class="fw-bold">Kategori</th>
                        <th class="fw-bold">Nama Pengantar</th>
                        <th class="fw-bold">Jenis Kelamin</th>
                        <th class="fw-bold">No. HP</th>
                        <th class="fw-bold">Plat Nomor</th>
                <th scope="col">Nama Pasien</th>
                <th scope="col">Status</th>
                <th scope="col">Tanggal Dibuat</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($escorts as $index => $escort)
                <tr>
                    <td class="px-4">
                        {{ 
                            method_exists($escorts, 'currentPage') 
                                ? ($escorts->currentPage() - 1) * $escorts->perPage() + $index + 1 
                                : $index + 1 
                        }}
                    </td>
                    <td>
                        <span class="badge kategori-badge
                            @if($escort->kategori_pengantar == 'Polisi') bg-primary
                            @elseif($escort->kategori_pengantar == 'Ambulans') bg-danger
                            @else bg-secondary
                            @endif">
                            {{ $escort->kategori_pengantar }}
                        </span>
                    </td>
                    <td>{{ $escort->nama_pengantar }}</td>
                    <td>{{ $escort->jenis_kelamin }}</td>
                    <td>
                        <a href="tel:{{ $escort->nomor_hp }}" class="text-decoration-none">
                            {{ $escort->nomor_hp }}
                        </a>
                    </td>
                    <td>
                        <code>{{ $escort->plat_nomor }}</code>
                    </td>
                    <td>{{ $escort->nama_pasien }}</td>
                    <td>
                        <span class="badge status-badge-{{ $escort->id }} {{ $escort->getStatusBadgeClass() }}">
                            {{ $escort->getStatusDisplayName() }}
                        </span>
                    </td>
                    <td>
                        <small>
                            {{ $escort->created_at->format('d/m/Y H:i') }}<br>
                            <span class="text-muted">{{ $escort->created_at->diffForHumans() }}</span>
                        </small>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <!-- Detail Button -->
                            <button type="button" 
                                    class="btn btn-sm btn-info detail-btn" 
                                    data-escort="{{ json_encode([
                                        'id' => $escort->id,
                                        'nama_pengantar' => $escort->nama_pengantar,
                                        'kategori_pengantar' => $escort->kategori_pengantar,
                                        'jenis_kelamin' => $escort->jenis_kelamin,
                                        'nomor_hp' => $escort->nomor_hp,
                                        'plat_nomor' => $escort->plat_nomor,
                                        'nama_pasien' => $escort->nama_pasien,
                                        'status' => $escort->getStatusDisplayName(),
                                        'status_badge_class' => $escort->getStatusBadgeClass(),
                                        'created_at' => $escort->created_at->format('d/m/Y H:i'),
                                        'created_at_diff' => $escort->created_at->diffForHumans(),
                                        'foto_pengantar' => $escort->foto_pengantar ? asset('storage/' . $escort->foto_pengantar) : null
                                    ]) }}"
                                    title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if($escort->status !== 'verified')
                                <button type="button" 
                                        class="btn btn-sm btn-outline-success btn-status-update" 
                                        data-escort-id="{{ $escort->id }}" 
                                        data-status="verified" 
                                        data-status-text="Terverifikasi"
                                        title="Verifikasi">
                                    <i class="fas fa-check"></i>
                                </button>
                            @endif
                            @if($escort->status !== 'rejected')
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger btn-status-update" 
                                        data-escort-id="{{ $escort->id }}" 
                                        data-status="rejected" 
                                        data-status-text="Ditolak"
                                        title="Tolak">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                            @if($escort->status !== 'pending')
                                <button type="button" 
                                        class="btn btn-sm btn-outline-warning btn-status-update" 
                                        data-escort-id="{{ $escort->id }}" 
                                        data-status="pending" 
                                        data-status-text="Menunggu"
                                        title="Kembalikan ke Menunggu">
                                    <i class="fas fa-clock"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center py-4">
                        <div class="text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p class="mb-0">Tidak ada data escort yang ditemukan</p>
                            <small>Coba ubah filter pencarian atau tunggu respon dari server</small>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handler untuk tombol detail
    $(document).on('click', '.detail-btn', function() {
        const escort = JSON.parse($(this).attr('data-escort'));
        
        let photoHtml = '';
        if (escort.foto_pengantar) {
            photoHtml = `
                <div class="text-center mb-4">
                    <img src="${escort.foto_pengantar}" 
                         class="img-fluid rounded show-full-photo" 
                         alt="Foto ${escort.nama_pengantar}"
                         style="max-height: 300px; cursor: zoom-in;"
                         data-full-photo="${escort.foto_pengantar}">
                </div>`;
        } else {
            photoHtml = `
                <div class="alert alert-info text-center mb-4">
                    <i class="fas fa-image-slash fa-2x mb-2"></i>
                    <p class="mb-0">Tidak ada foto</p>
                </div>`;
        }

        const detailHtml = `
            <div class="row">
                <div class="col-12 text-start">
                    ${photoHtml}
                    <table class="table table-sm mb-0 text-start">
                        <tr>
                            <td width="35%" class="text-start"><strong>Nama Pengantar</strong></td>
                            <td class="text-start">: ${escort.nama_pengantar}</td>
                        </tr>
                        <tr>
                            <td><strong>Kategori</strong></td>
                            <td>: ${escort.kategori_pengantar}</td>
                        </tr>
                        <tr>
                            <td><strong>Jenis Kelamin</strong></td>
                            <td>: ${escort.jenis_kelamin}</td>
                        </tr>
                        <tr>
                            <td><strong>No. HP</strong></td>
                            <td>: <a href="tel:${escort.nomor_hp}">${escort.nomor_hp}</a></td>
                        </tr>
                        <tr>
                            <td><strong>Plat Nomor</strong></td>
                            <td>: <code>${escort.plat_nomor}</code></td>
                        </tr>
                        <tr>
                            <td><strong>Nama Pasien</strong></td>
                            <td>: ${escort.nama_pasien}</td>
                        </tr>
                        <tr>
                            <td><strong>Status</strong></td>
                            <td>: <span class="badge ${escort.status_badge_class}">${escort.status}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Waktu Masuk</strong></td>
                            <td>: ${escort.created_at}
                                <small class="text-muted">${escort.created_at_diff}</small>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>`;

        Swal.fire({
            title: `Detail Pengantar - ${escort.nama_pengantar}`,
            html: detailHtml,
            width: 800,
            showCloseButton: true,
            showConfirmButton: false,
            customClass: {
                container: 'swal2-detail-modal'
            }
        });
    });

    // Handler untuk zoom foto
    $(document).on('click', '.show-full-photo', function() {
        const photoUrl = $(this).data('full-photo');
        
        Swal.fire({
            imageUrl: photoUrl,
            imageAlt: 'Foto Pengantar',
            width: 'auto',
            padding: '1rem',
            showCloseButton: true,
            showConfirmButton: false,
            customClass: {
                image: 'img-fluid',
                container: 'swal2-photo-modal'
            }
        });
    });
});
</script>

<style>
/* Custom styling untuk modal SweetAlert2 */
.swal2-detail-modal .swal2-popup {
    padding: 2rem;
    text-align: left;
}

.swal2-detail-modal .swal2-title {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    text-align: left;
}

.swal2-detail-modal .table td {
    padding: 0.5rem;
    text-align: left !important;
}

.swal2-detail-modal .swal2-html-container {
    text-align: left;
}

.swal2-detail-modal .table {
    text-align: left;
    margin-left: 0;
}

.swal2-photo-modal {
    background: rgba(0, 0, 0, 0.9);
}

.swal2-photo-modal .swal2-popup {
    background: transparent;
    box-shadow: none;
}

.swal2-photo-modal .swal2-image {
    max-height: 85vh !important;
    border-radius: 0.5rem;
}
</style>
</script>

@if(method_exists($escorts, 'hasPages') && $escorts->hasPages())
    <div class="d-flex justify-content-center align-items-center mt-3">
        <div class="text-muted small">
            Menampilkan {{ $escorts->firstItem() ?? 0 }} sampai {{ $escorts->lastItem() ?? 0 }} 
            dari {{ $escorts->total() }} hasil
        </div>
    </div>
@elseif(is_countable($escorts) && count($escorts) > 0)
    <div class="d-flex justify-content-center align-items-center mt-3">
        <div class="text-muted small">
            Menampilkan {{ count($escorts) }} dari {{ count($escorts) }} hasil
            @if(request('view_all') === 'true')
                <span class="badge bg-info ms-2">Semua Data</span>
            @endif
        </div>
    </div>
@endif