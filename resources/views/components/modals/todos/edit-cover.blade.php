<form wire:submit.prevent="editCoverTodo">
    <div class="modal fade" tabindex="-1" id="editCoverTodoModal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Cover Todo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Cover</label>
                        <input type="file" class="form-control" wire:model="editCoverTodoFile">
                        @error('editCoverTodoFile')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" @if (!$editCoverTodoFile) disabled @endif>
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>