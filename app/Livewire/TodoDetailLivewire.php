<?php

namespace App\Livewire;

use App\Models\Todo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class TodoDetailLivewire extends Component
{
    use WithFileUploads;

    public $todo;
    public $auth;

    public function mount()
    {
        $this->auth = Auth::user();

        $todo_id = request()->route('todo_id');
        $targetTodo = Todo::where('id', $todo_id)->first();
        if (!$targetTodo) {
            return redirect()->route('app.home');
        }

        $this->todo = $targetTodo;
    }

    public function render()
    {
        return view('livewire.todo-detail-livewire');
    }

    // Ubah Cover Todo
    public $editCoverTodoFile;

    public function editCoverTodo()
    {
        $this->validate([
            'editCoverTodoFile' => 'required|image|max:2048',  // 2MB Max
        ]);

        if ($this->editCoverTodoFile) {
            // Hapus cover lama jika ada
            if ($this->todo->cover && Storage::disk('public')->exists($this->todo->cover)) {
                Storage::disk('public')->delete($this->todo->cover);
            }

            $userId = $this->auth->id;
            $dateNumber = now()->format('YmdHis');
            $extension = $this->editCoverTodoFile->getClientOriginalExtension();
            $filename = $userId . '_' . $dateNumber . '.' . $extension;
            $path = $this->editCoverTodoFile->storeAs('covers', $filename, 'public');
            $this->todo->cover = $path;
            $this->todo->save();
        }

        $this->reset(['editCoverTodoFile']);

        $this->dispatch('closeModal', id: 'editCoverTodoModal');
    }
}