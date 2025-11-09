<div class="container-fluid py-4">
    {{-- SweetAlert2 Listener --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('swal:alert', (event) => {
                Swal.fire({
                    icon: event.icon,
                    title: event.title,
                    text: event.text,
                    timer: 3000,
                    showConfirmButton: false,
                });
            });
            @this.on('close-modal', () => {
                $('#financeModal').modal('hide');
                $('#deleteModal').modal('hide');
            });
            @this.on('open-modal', () => {
                $('#financeModal').modal('show');
            });
        });
    </script>
    
    <div class="row">
        {{-- Card Statistik --}}
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total Saldo (6 Bulan)</h5>
                    <h3 class="text-success">
                        Rp {{ number_format($chartData['balance'] ?? 0, 0, ',', '.') }}
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Statistik Pemasukan vs Pengeluaran (6 Bulan Terakhir)</h5>
                    <div id="apex-chart"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Catatan Keuangan</h5>
                    <button class="btn btn-primary" wire:click="resetForm" data-bs-toggle="modal" data-bs-target="#financeModal">
                        <i class="fas fa-plus"></i> Tambah Catatan
                    </button>
                </div>
                
                <div class="card-body">
                    {{-- Filter dan Pencarian --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari deskripsi atau kategori...">
                        </div>
                        <div class="col-md-4">
                            <select wire:model.live="filter_type" class="form-select">
                                <option value="all">Semua Jenis</option>
                                <option value="income">Pemasukan</option>
                                <option value="expense">Pengeluaran</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select wire:model.live="filter_category" class="form-select">
                                <option value="all">Semua Kategori</option>
                                @foreach($categoriesList as $category)
                                    <option value="{{ $category->id }}" class="{{ $category->type == 'income' ? 'text-success' : 'text-danger' }}">
                                        {{ $category->name }} ({{ ucfirst($category->type) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    {{-- Tabel Transaksi --}}
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Kategori</th>
                                    <th>Jumlah</th>
                                    <th>Deskripsi</th>
                                    <th>Cover</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->transaction_date->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge {{ $transaction->type == 'income' ? 'bg-success' : 'bg-danger' }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->category->name }}</td>
                                    <td class="{{ $transaction->type == 'income' ? 'text-success' : 'text-danger' }}">
                                        **Rp {{ number_format($transaction->amount, 0, ',', '.') }}**
                                    </td>
                                    <td>{!! \Illuminate\Support\Str::words(strip_tags($transaction->description), 5, '...') !!}</td>
                                    <td>
                                        @if($transaction->cover_image)
                                            <a href="{{ $transaction->cover_image_url }}" target="_blank">Lihat Gambar</a>
                                            <button wire:click="removeCoverImage({{ $transaction->id }})" class="btn btn-sm text-danger p-0" title="Hapus Gambar"><i class="fas fa-trash"></i></button>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <button wire:click="edit({{ $transaction->id }})" class="btn btn-sm btn-info text-white me-2">Edit</button>
                                        <button wire:click="$set('transaction_id', {{ $transaction->id }})" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">Hapus</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada catatan keuangan ditemukan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Pagination --}}
                    <div class="mt-3">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah/Edit --}}
    <div wire:ignore.self class="modal fade" id="financeModal" tabindex="-1" aria-labelledby="financeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="financeModalLabel">{{ $is_edit_mode ? 'Ubah' : 'Tambah' }} Catatan Keuangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="resetForm"></button>
                </div>
                <form wire:submit="saveTransaction">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Transaksi</label>
                                <select wire:model.live="type" class="form-select @error('type') is-invalid @enderror">
                                    <option value="">Pilih Jenis</option>
                                    <option value="income">Pemasukan</option>
                                    <option value="expense">Pengeluaran</option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Kategori</label>
                                @if($type)
                                    <input type="text" wire:model="category_name" list="category-list" class="form-control @error('category_name') is-invalid @enderror" placeholder="Contoh: Gaji, Makanan">
                                    <datalist id="category-list">
                                        @foreach($categoriesList->where('type', $type) as $category)
                                            <option value="{{ $category->name }}">
                                        @endforeach
                                    </datalist>
                                @else
                                    <input type="text" class="form-control" placeholder="Pilih Jenis Transaksi Dulu" disabled>
                                @endif
                                @error('category_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jumlah (Rp)</label>
                                <input type="number" wire:model="amount" class="form-control @error('amount') is-invalid @enderror" min="1" step="0.01">
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Transaksi</label>
                                <input type="date" wire:model="transaction_date" class="form-control @error('transaction_date') is-invalid @enderror">
                                @error('transaction_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 mb-3" wire:ignore>
                                <label class="form-label">Deskripsi (Trix Editor)</label>
                                <input id="trix-editor" type="hidden" wire:model="description">
                                <trix-editor input="trix-editor" class="@error('description') border-danger @enderror"></trix-editor>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Cover Gambar (Opsional)</label>
                                <input type="file" wire:model="cover_image" class="form-control @error('cover_image') is-invalid @enderror">
                                @error('cover_image') <div class="invalid-feedback">{{ $message }}</div> @enderror

                                @if ($current_cover_image)
                                    <div class="mt-2">
                                        Gambar Saat Ini: <a href="{{ Storage::disk('public')->url($current_cover_image) }}" target="_blank">Lihat</a>
                                        <button type="button" wire:click="removeCoverImage" class="btn btn-sm btn-danger ms-2">Hapus Cover</button>
                                    </div>
                                @elseif ($cover_image)
                                    <div class="mt-2">Preview: <img src="{{ $cover_image->temporaryUrl() }}" style="max-height: 100px;"></div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetForm">Batal</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading wire:target="saveTransaction">Memproses...</span>
                            <span wire:loading.remove wire:target="saveTransaction">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Hapus --}}
    <div wire:ignore.self class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus catatan keuangan ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" wire:click="deleteTransaction" class="btn btn-danger">Hapus</button>
                </div>
            </div>
        </div>
    </div>
    
    {{-- ApexCharts Script --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            const chartOptions = {
                series: [{
                    name: 'Pemasukan',
                    data: @json($chartData['income'] ?? [])
                }, {
                    name: 'Pengeluaran',
                    data: @json($chartData['expense'] ?? [])
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    stacked: false,
                    toolbar: { show: false }
                },
                plotOptions: { bar: { horizontal: false, columnWidth: '55%', } },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: @json($chartData['categories'] ?? []),
                    title: { text: 'Bulan' }
                },
                yaxis: { title: { text: 'Jumlah (Rp)' } },
                tooltip: { y: { formatter: function (val) { return "Rp " + val.toLocaleString('id-ID'); } } },
                colors: ['#28a745', '#dc3545']
            };
            
            let chart = new ApexCharts(document.querySelector("#apex-chart"), chartOptions);
            chart.render();

            // Update chart saat data Livewire berubah
            Livewire.hook('element.updated', (el, component) => {
                if (component.name === 'finance-index-livewire') {
                    chart.updateOptions({
                        series: [{
                            name: 'Pemasukan',
                            data: component.serverMemo.data.chartData.income
                        }, {
                            name: 'Pengeluaran',
                            data: component.serverMemo.data.chartData.expense
                        }],
                        xaxis: {
                            categories: component.serverMemo.data.chartData.categories
                        }
                    });
                }
            });
            
            // Trix Editor Listener
            document.querySelector('#trix-editor').addEventListener('trix-change', function(event) {
                @this.set('description', event.target.value);
            });
            
            // Memastikan Trix editor diperbarui saat mode edit
            @this.on('open-modal', () => {
                // Memberi sedikit waktu agar modal muncul
                setTimeout(() => {
                    document.querySelector('trix-editor').editor.loadHTML(@this.get('description') || '');
                }, 100); 
            });
        });
    </script>
</div>