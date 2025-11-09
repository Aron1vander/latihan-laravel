<?php

namespace App\Livewire;

use App\Models\Todo;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class HomeLivewire extends Component
{
    public $auth;

    public function mount()
    {
        $this->auth = Auth::user();
    }

    public function render()
    {
        $todos = Todo::where('user_id', $this->auth->id)->orderBy('created_at', 'desc')->get();
        $data = [
            'todos' => $todos
        ];
        return view('livewire.home-livewire', $data);
    }

    // Add Todo
    public $addTodoTitle;
    public $addTodoDescription;

    public function addTodo()
    {
        $this->validate([
            'addTodoTitle' => 'required|string|max:255',
            'addTodoDescription' => 'required|string',
        ]);

        // Simpan todo ke database
        Todo::create([
            'title' => $this->addTodoTitle,
            'description' => $this->addTodoDescription,
            'user_id' => $this->auth->id,
            'is_finished' => false,
        ]);

        // Reset the form
        $this->reset(['addTodoTitle', 'addTodoDescription']);

        // Tutup modal
        $this->dispatch('closeModal', id: 'addTodoModal');
    }

    // Edit Todo
    public $editTodoId;
    public $editTodoTitle;
    public $editTodoIsFinished;
    public $editTodoDescription;

    public function prepareEditTodo($id)
    {
        $todo = Todo::where('id', $id)->first();
        if (!$todo) {
            return;
        }

        $this->editTodoId = $todo->id;
        $this->editTodoTitle = $todo->title;
        $this->editTodoIsFinished = $todo->is_finished ? '1' : '0';
        $this->editTodoDescription = $todo->description;

        $this->dispatch('showModal', id: 'editTodoModal');
    }

    public function editTodo()
    {
        $this->validate([
            'editTodoTitle' => 'required|string|max:255',
            'editTodoIsFinished' => 'required|boolean',
            'editTodoDescription' => 'required|string',
        ]);

        $todo = Todo::where('id', $this->editTodoId)->first();
        if (!$todo) {
            $this->addError('editTodoTitle', 'Data todo tidak tersedia.');
            return;
        }
        $todo->title = $this->editTodoTitle;
        $todo->is_finished = $this->editTodoIsFinished;
        $todo->description = $this->editTodoDescription;
        $todo->save();

        $this->reset(['editTodoId', 'editTodoTitle', 'editTodoDescription', 'editTodoIsFinished']);
        $this->dispatch('closeModal', id: 'editTodoModal');
    }

    // Delete Todo
    public $deleteTodoId;
    public $deleteTodoTitle;
    public $deleteTodoConfirmTitle;

    public function prepareDeleteTodo($id)
    {
        $todo = Todo::where('id', $id)->first();
        if (!$todo) {
            return;
        }

        $this->deleteTodoId = $todo->id;
        $this->deleteTodoTitle = $todo->title;
        $this->dispatch('showModal', id: 'deleteTodoModal');
    }

    public function deleteTodo()
    {
        if ($this->deleteTodoConfirmTitle !== $this->deleteTodoTitle) {
            $this->addError('deleteTodoConfirmTitle', 'Judul konfirmasi tidak sesuai dengan judul todo yang akan dihapus.');
            return;
        }

        Todo::destroy($this->deleteTodoId);
        $this->reset(['deleteTodoId', 'deleteTodoTitle', 'deleteTodoConfirmTitle']);
        $this->dispatch('closeModal', id: 'deleteTodoModal');
    }
}