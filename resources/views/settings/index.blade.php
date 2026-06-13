@extends('layouts.app')

@section('title', 'Pengaturan Aplikasi')
@section('page_title', 'Pengaturan Aplikasi')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Left Column: Guidelines & Preview -->
    <div class="space-y-4">
        <!-- Identity Preview Card -->
        <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm space-y-3">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Preview Identitas</h4>
            
            <div class="flex flex-col items-center justify-center p-4 bg-slate-50/50 rounded-2xl border border-slate-100/50 space-y-3">
                <!-- Logo Preview -->
                <div class="w-16 h-16 bg-white border border-slate-150 rounded-2xl flex items-center justify-center overflow-hidden shadow-sm">
                    @if($settings->logo)
                        <img src="{{ $settings->logo_url }}" id="logo-preview" alt="Logo Preview" class="max-w-full max-h-full object-contain p-2">
                    @else
                        <div class="w-full h-full bg-slate-900 text-white rounded-2xl flex items-center justify-center font-bold text-2xl" id="logo-placeholder">
                            PS
                        </div>
                        <img src="" id="logo-preview" alt="Logo Preview" class="max-w-full max-h-full object-contain p-2 hidden">
                    @endif
                </div>

                <div class="text-center space-y-0.5">
                    <h3 class="font-extrabold text-slate-900 text-base leading-tight" id="preview-name">{{ $settings->app_name }}</h3>
                    <p class="text-[10px] text-slate-500 font-medium max-w-xs" id="preview-desc">{{ $settings->description }}</p>
                </div>
            </div>

            <!-- Favicon Row -->
            <div class="flex items-center justify-between p-3 bg-slate-50/30 rounded-xl border border-slate-100/30">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Favicon Browser</span>
                <div class="w-7 h-7 rounded-lg bg-white border border-slate-200 flex items-center justify-center overflow-hidden">
                    @if($settings->favicon)
                        <img src="{{ $settings->favicon_url }}" id="favicon-preview" alt="Favicon Preview" class="w-4 h-4 object-contain">
                    @else
                        <img src="https://cdn-icons-png.flaticon.com/512/869/869045.png" id="favicon-preview" alt="Favicon Preview" class="w-4 h-4 object-contain">
                    @endif
                </div>
            </div>
        </div>

        <!-- Info Alert -->
        <div class="bg-indigo-50/40 border border-indigo-100/50 text-indigo-950 p-4 rounded-2xl text-[11px] space-y-1.5">
            <h5 class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-circle-info text-xs"></i> Catatan Teknis:</h5>
            <ul class="list-disc list-inside space-y-0.5 text-slate-600 font-medium">
                <li>Format file logo/favicon: jpg, jpeg, png, webp, atau ico.</li>
                <li>Ukuran maksimal masing-masing file: 2 MB.</li>
                <li>Gunakan format transparan (.png/.ico) agar tampak premium.</li>
            </ul>
        </div>
    </div>

    <!-- Right Column: Settings Form -->
    <div class="lg:col-span-2">
        <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm">
            <form id="settings-form" onsubmit="submitSettings(event)" class="space-y-4" enctype="multipart/form-data">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- App Name -->
                    <div class="space-y-1 col-span-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Aplikasi / Rental</label>
                        <input type="text" name="app_name" required value="{{ $settings->app_name }}" placeholder="Contoh: PlayStation Rental Center" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 placeholder:text-slate-400 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold transition-all">
                    </div>

                    <!-- WhatsApp / Phone -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nomor WhatsApp / HP</label>
                        <input type="text" name="phone" value="{{ $settings->phone }}" placeholder="Contoh: 081234567890" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 placeholder:text-slate-400 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold transition-all">
                    </div>


                    <!-- Logo Upload -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Unggah Logo</label>
                        <input type="file" name="logo" accept="image/*" onchange="previewFile(this, 'logo')" class="w-full px-2 py-1 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 file:mr-3 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800 text-[10px] transition-all">
                    </div>

                    <!-- Favicon Upload -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Unggah Favicon</label>
                        <input type="file" name="favicon" accept="image/*" onchange="previewFile(this, 'favicon')" class="w-full px-2 py-1 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 file:mr-3 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800 text-[10px] transition-all">
                    </div>

                    <!-- Description -->
                    <div class="space-y-1 col-span-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Keterangan / Slogan Singkat</label>
                        <textarea name="description" rows="2" placeholder="Slogan atau keterangan yang muncul di halaman login..." class="w-full px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 placeholder:text-slate-400 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-medium transition-all resize-none">{{ $settings->description }}</textarea>
                    </div>

                    <!-- Address -->
                    <div class="space-y-1 col-span-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat Tempat Rental</label>
                        <textarea name="address" rows="2" placeholder="Alamat lengkap lokasi rental..." class="w-full px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 placeholder:text-slate-400 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-medium transition-all resize-none">{{ $settings->address }}</textarea>
                    </div>
                </div>

                <!-- Action button -->
                <div class="flex items-center justify-end pt-3 border-t border-slate-50">
                    <button type="submit" class="py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs shadow-md transition-all active:scale-[0.98]">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>

        <!-- Admin Security Credentials Card -->
        <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm mt-6">
            <div class="border-b border-slate-100 pb-2.5 mb-4">
                <h4 class="text-xs font-bold text-slate-850 uppercase tracking-wider">Keamanan & Kredensial Admin</h4>
                <p class="text-[10px] text-slate-400 font-medium">Ubah email dan password login administrator.</p>
            </div>

            <form id="security-form" onsubmit="submitSecurity(event)" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Email Admin -->
                    <div class="space-y-1 col-span-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Email Admin</label>
                        <input type="email" name="email" required value="{{ auth()->user()->email }}" placeholder="admin@gmail.com" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 placeholder:text-slate-400 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold transition-all">
                    </div>

                    <!-- Password Saat Ini -->
                    <div class="space-y-1 col-span-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Password Saat Ini (Konfirmasi Keamanan)</label>
                        <input type="password" name="current_password" required placeholder="Masukkan password saat ini untuk memverifikasi" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 placeholder:text-slate-400 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold transition-all">
                    </div>

                    <!-- Password Baru -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Password Baru</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tidak ingin diubah" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 placeholder:text-slate-400 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold transition-all">
                    </div>

                    <!-- Konfirmasi Password Baru -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" placeholder="Konfirmasi password baru" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 placeholder:text-slate-400 focus:bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none text-xs font-semibold transition-all">
                    </div>
                </div>

                <!-- Action button -->
                <div class="flex items-center justify-end pt-3 border-t border-slate-50">
                    <button type="submit" class="py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs shadow-md transition-all active:scale-[0.98]">
                        Perbarui Kredensial
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
    // File upload live previews
    function previewFile(input, target) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (target === 'logo') {
                    const placeholder = document.getElementById('logo-placeholder');
                    const img = document.getElementById('logo-preview');
                    if (placeholder) placeholder.classList.add('hidden');
                    img.src = e.target.result;
                    img.classList.remove('hidden');
                } else if (target === 'favicon') {
                    document.getElementById('favicon-preview').src = e.target.result;
                }
            }
            reader.readAsDataURL(file);
        }
    }

    // Submit settings via AJAX
    async function submitSettings(e) {
        e.preventDefault();
        
        const form = document.getElementById('settings-form');
        const formData = new FormData(form);

        // Fetch submit with isMultipart = true
        try {
            const res = await ajaxRequest('/admin/api/settings/update', 'POST', formData, true);
            if (res.success) {
                showToast('success', res.message);
                
                // Update identity previews dynamically
                document.getElementById('preview-name').textContent = res.data.app_name;
                document.getElementById('preview-desc').textContent = res.data.description;
                
                // Reload page after a delay to ensure assets are reloaded in the sidebar/layout
                setTimeout(() => location.reload(), 1500);
            }
        } catch (err) {
            Swal.fire('Validasi Gagal', err.data?.message || 'Gagal menyimpan pengaturan.', 'error');
        }
    }

    // Submit security credentials via AJAX
    async function submitSecurity(e) {
        e.preventDefault();
        
        const form = document.getElementById('security-form');
        const formData = new FormData(form);
        
        const data = {};
        formData.forEach((value, key) => data[key] = value);

        try {
            const res = await ajaxRequest('/admin/api/settings/security', 'POST', data);
            if (res.success) {
                showToast('success', res.message);
                form.reset();
                setTimeout(() => location.reload(), 1500);
            }
        } catch (err) {
            Swal.fire('Validasi Gagal', err.data?.message || 'Gagal memperbarui kredensial.', 'error');
        }
    }
</script>
@endsection
