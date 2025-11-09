<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class FinanceIndexLivewire extends Component
{
    use WithPagination;
    use WithFileUploads;

    // --- Properti untuk Filter & Pagination ---
    public $search = '';
    public $filter_type = 'all'; // 'all', 'income', 'expense'
    public $filter_category = 'all';

    // --- Properti untuk Form CRUD ---
    public $transaction_id;
    public $category_name;
    public $type;
    public $amount;
    public $description;
    public $transaction_date;
    public $cover_image; // Untuk upload file baru
    public $current_cover_image; // Untuk menampilkan gambar saat ini
    public $is_edit_mode = false;
    
    // --- Properti untuk Chart ---
    public $chartData = [];
    public $categoriesList = [];

    protected $rules = [
        'category_name' => 'required|string|max:100',
        'type' => 'required|in:income,expense',
        'amount' => 'required|numeric|min:1',
        'description' => 'nullable|string',
        'transaction_date' => 'required|date',
        'cover_image' => 'nullable|image|max:2048', // Maks 2MB
    ];

    public function mount()
    {
        $this->transaction_date = now()->toDateString();
        $this->loadCategoriesList();
    }
    
    // --- SweetAlert2 Dispatch Helper ---
    public function dispatchSwal($icon, $title, $text)
    {
        $this->dispatch('swal:alert', [
            'icon' => $icon,
            'title' => $title,
            'text' => $text,
        ]);
    }

    // --- Load Kategori Pengguna ---
    public function loadCategoriesList()
    {
        $this->categoriesList = Category::where('user_id', Auth::id())
            ->orderBy('type')
            ->get(['id', 'name', 'type']);
    }

    // --- FUNGSI SAVE/UPDATE TRANSAKSI ---
    public function saveTransaction()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                // 1. Dapatkan atau Buat Kategori
                $category = Category::firstOrCreate(
                    ['user_id' => Auth::id(), 'name' => $this->category_name],
                    ['type' => $this->type]
                );

                // 2. Tentukan Path Gambar (jika ada)
                $imagePath = $this->current_cover_image;
                if ($this->cover_image) {
                    if ($this->is_edit_mode && $imagePath) {
                        Storage::disk('public')->delete($imagePath); // Hapus gambar lama
                    }
                    $imagePath = $this->cover_image->store('covers', 'public');
                }
                
                // 3. Simpan atau Update Transaksi
                $data = [
                    'user_id' => Auth::id(),
                    'category_id' => $category->id,
                    'type' => $this->type,
                    'amount' => $this->amount,
                    'description' => $this->description,
                    'transaction_date' => $this->transaction_date,
                    'cover_image' => $imagePath,
                ];

                if ($this->is_edit_mode) {
                    Transaction::find($this->transaction_id)->update($data);
                    $this->dispatchSwal('success', 'Berhasil!', 'Catatan Keuangan berhasil diperbarui.');
                } else {
                    Transaction::create($data);
                    $this->dispatchSwal('success', 'Berhasil!', 'Catatan Keuangan berhasil ditambahkan.');
                }

                $this->resetForm();
                $this->dispatch('close-modal');
            });
        } catch (\Exception $e) {
            $this->dispatchSwal('error', 'Gagal!', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }
    
    // --- FUNGSI DELETE TRANSAKSI ---
    public function deleteTransaction()
    {
        $transaction = Transaction::find($this->transaction_id);

        if ($transaction && $transaction->user_id === Auth::id()) {
            if ($transaction->cover_image) {
                Storage::disk('public')->delete($transaction->cover_image);
            }
            $transaction->delete();
            $this->dispatchSwal('success', 'Dihapus!', 'Catatan Keuangan berhasil dihapus.');
        } else {
            $this->dispatchSwal('error', 'Gagal!', 'Catatan tidak ditemukan atau Anda tidak memiliki izin.');
        }

        $this->resetForm();
        $this->dispatch('close-modal');
    }

    // --- Fungsi Buka Form Edit ---
    public function edit($id)
    {
        $transaction = Transaction::findOrFail($id);
        $this->transaction_id = $transaction->id;
        $this->category_name = $transaction->category->name;
        $this->type = $transaction->type;
        $this->amount = $transaction->amount;
        $this->description = $transaction->description;
        $this->transaction_date = $transaction->transaction_date->toDateString();
        $this->current_cover_image = $transaction->cover_image;
        $this->is_edit_mode = true;
        $this->dispatch('open-modal');
    }

    // --- Fungsi Hapus Gambar Cover ---
    public function removeCoverImage($id = null)
    {
        $id = $id ?? $this->transaction_id;
        $transaction = Transaction::find($id);

        if ($transaction && $transaction->cover_image) {
            Storage::disk('public')->delete($transaction->cover_image);
            $transaction->cover_image = null;
            $transaction->save();
            $this->current_cover_image = null;
            $this->dispatchSwal('info', 'Cover Dihapus', 'Gambar cover telah dihapus.');
        }
    }

    // --- Reset Form ---
    public function resetForm()
    {
        $this->resetValidation();
        $this->reset([
            'transaction_id', 'category_name', 'type', 'amount', 'description', 
            'transaction_date', 'cover_image', 'current_cover_image', 'is_edit_mode'
        ]);
        $this->transaction_date = now()->toDateString();
    }

    // --- FUNGSI RENDER UTAMA ---
    public function render()
    {
        $user_id = Auth::id();
        
        // Query untuk Filter dan Pencarian
        $query = Transaction::where('user_id', $user_id)
            ->with('category')
            ->latest()
            ->when($this->search, function ($q) {
                $q->where('description', 'ilike', '%' . $this->search . '%') // 'ilike' for PostgreSQL case-insensitive search
                  ->orWhereHas('category', function ($qc) {
                      $qc->where('name', 'ilike', '%' . $this->search . '%');
                  });
            })
            ->when($this->filter_type !== 'all', function ($q) {
                $q->where('type', $this->filter_type);
            })
            ->when($this->filter_category !== 'all', function ($q) {
                $q->where('category_id', $this->filter_category);
            });
        
        // Paginasi 20 data per halaman
        $transactions = $query->paginate(20);
        
        // Data untuk Statistik (ApexCharts)
        $this->loadChartData($user_id);

        return view('livewire.finance-index-livewire', [
            'transactions' => $transactions,
        ]);
    }

    // --- PREPARASI DATA CHART ---
    public function loadChartData($user_id)
    {
        // 1. Ambil data total Income dan Expense per bulan
        $monthlyTotals = Transaction::select(
            DB::raw("EXTRACT(YEAR FROM transaction_date) as year"),
            DB::raw("EXTRACT(MONTH FROM transaction_date) as month"),
            DB::raw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income"),
            DB::raw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense")
        )
        ->where('user_id', $user_id)
        ->where('transaction_date', '>=', now()->subMonths(6)) // Hanya 6 bulan terakhir
        ->groupBy('year', 'month')
        ->orderBy('year')
        ->orderBy('month')
        ->get();

        // 2. Format data untuk ApexCharts
        $months = [];
        $incomeData = [];
        $expenseData = [];

        foreach ($monthlyTotals as $total) {
            $monthName = \DateTime::createFromFormat('!m', $total->month)->format('M Y');
            $months[] = $monthName;
            $incomeData[] = (float)$total->total_income;
            $expenseData[] = (float)$total->total_expense;
        }

        $this->chartData = [
            'categories' => $months,
            'income' => $incomeData,
            'expense' => $expenseData,
            'balance' => collect($incomeData)->sum() - collect($expenseData)->sum()
        ];
    }
}