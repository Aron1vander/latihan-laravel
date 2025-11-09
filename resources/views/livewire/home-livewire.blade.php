<div class="mt-3">
    <div class="card">
        <div class="card-header d-flex">
            <div class="flex-fill">
                <h3>Hay, {{ $auth->name }}</h3>
            </div>
            <div>
                <a href="{{ route('auth.logout') }}" class="btn btn-warning">Keluar</a>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex mb-2">
                <div class="flex-fill">
                    <h3>Daftar Todo</h3>
                </div>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTodoModal">
                        Tambah Todo
                    </button>
                </div>
            </div>
            <table class="table table-striped">
                <tr class="table-light">
                    <th>No</th>
                    <th>Judul</th>
                    <th>Dibuat pada</th>
                    <th>Diubah pada</th>
                    <th>Status</th>
                    <th>Tindakan</th>
                </tr>
                @foreach ($todos as $key => $todo)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $todo->title }}</td>
                        <td>{{ date('d F Y, H:i', strtotime($todo->created_at)) }}</td>
                        <td>{{ date('d F Y, H:i', strtotime($todo->updated_at)) }}</td>
                        <td>
                            @if ($todo->is_finished)
                                <span class="badge bg-success">Selesai</span>
                            @else
                                <span class="badge bg-danger">Belum selesai</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('app.todos.detail', ['todo_id' => $todo->id]) }}"
                                class="btn btn-sm btn-info">
                                Detail
                            </a>
                            <button wire:click="prepareEditTodo({{ $todo->id }})" class="btn btn-sm btn-warning">
                                Edit
                            </button>
                            <button wire:click="prepareDeleteTodo({{ $todo->id }})" class="btn btn-sm btn-danger">
                                Hapus
                            </button>
                        </td>
                    </tr>
                @endforeach
                @if (sizeof($todos) === 0)
                    <tr>
                        <td colspan="6" class="text-center">Belum ada data todo yang tersedia.</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- Modals --}}
    @include('components.modals.todos.add')
    @include('components.modals.todos.edit')
    @include('components.modals.todos.delete')
</div>