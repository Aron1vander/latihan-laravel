<form wire:submit.prevent="register">
    <div class="card mx-auto" style="max-width: 360px;">
        <div class="card-body">
            <div>
                <div class="text-center">
                    <img src="/logo.png" alt="Logo">
                    <h2>Mendaftar</h2>
                </div>
                <hr>
                {{-- Nama --}}
                <div class="form-group mb-3">
                    <label>Nama</label>
                    <input type="text" class="form-control" wire:model="name">
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                {{-- Alamat Email --}}
                <div class="form-group mb-3">
                    <label>Email</label>
                    <input type="email" class="form-control" wire:model="email">
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                {{-- Kata Sandi --}}
                <div class="form-group mb-3">
                    <label>Kata Sandi</label>
                    <input type="password" class="form-control" wire:model="password">
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Kirim --}}
                <div class="form-group mt-3 text-end">
                    <button type="submit" class="btn btn-primary btn-block">Kirim</button>
                </div>

                <hr>
                <p class="text-center">Sudah memiliki akun? <a href="{{ route('auth.login') }}">Masuk</a></p>
            </div>
        </div>
    </div>
</form>