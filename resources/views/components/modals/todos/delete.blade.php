
<form wire:submit.prevent="deleteTodo">
    <div class="modal fade" tabindex="-1" id="deleteTodoModal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Todo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        Apakah kamu yakin ingin menghapus todo dengan judul <strong>"{{ $deleteTodoTitle }}"</strong>?
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Judul</label>
                        <input type="text" class="form-control" wire:model="deleteTodoConfirmTitle">
                        @error('deleteTodoConfirmTitle')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Lanjutkan, Hapus</button>
                </div>
            </div>
        </div>
    </div>
</form>