<div class="mt-3">
    <div class="card">
        <div class="card-header d-flex">
            <div class="flex-fill">
                <a href="{{ route('app.home') }}" class="text-decoration-none">
                    <small class="text-muted">
                        &lt; Kembali
                    </small>
                </a>
                <h3>
                    {{ $todo->title }}
                    @if ($todo->is_finished)
                        <small class="badge bg-success">Selesai</small>
                    @else
                        <small class="badge bg-danger">Belum selesai</small>
                    @endif
                </h3>
            </div>
            <div>
                <button class="btn btn-warning" data-bs-target="#editCoverTodoModal" data-bs-toggle="modal">
                    Ubah Cover
                </button>
            </div>
        </div>
        <div class="card-body">
            @if ($todo->cover)
                <img src="{{ asset('storage/' . $todo->cover) }}" alt="Cover" style="max-width: 100%;">
                <hr>
            @endif
            <p style="font-size: 18px;">{{ $todo->description }}</p>
        </div>
    </div>

    {{-- Modals --}}
    @include('components.modals.todos.edit-cover')
</div>