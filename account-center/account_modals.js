document.addEventListener('DOMContentLoaded', function() {
    const mainAccContent = document.getElementById('mainAccContent');
    const hoverOverlay = document.getElementById('hoverOverlay');
    const modalHoverBox = document.getElementById('modalHoverBox');

    // Tombol-tombol pemicu modal
    const editBiodataBtn = document.getElementById('edit_biodata');
    const changePasswordBtn = document.getElementById('change_password');
    const deleteAccBtn = document.getElementById('delete_acc_btn');

    // Data pengguna dari PHP (dilewatkan melalui atribut data atau disimpan di elemen tersembunyi)
    // Untuk kesederhanaan, kita bisa mengambilnya langsung dari elemen HTML yang sudah ada di halaman
    const currentUsername = document.querySelector('#biodata .acc-info:nth-child(1) .info-right p').textContent;
    const currentFullname = document.querySelector('#biodata .acc-info:nth-child(2) .info-right p').textContent;
    const currentEmail = document.querySelector('#biodata .acc-info:nth-child(3) .info-right p').textContent;
    const currentPhonenumber = document.querySelector('#biodata .acc-info:nth-child(4) .info-right p').textContent;
    
    // Fungsi untuk menampilkan modal
    function showModal(contentHtml) {
        modalHoverBox.innerHTML = contentHtml; // Masukkan konten ke dalam modal
        mainAccContent.classList.add('blurred'); // Blur konten utama
        hoverOverlay.classList.add('active'); // Tampilkan overlay dan modal
        document.body.style.overflow = 'hidden'; // Nonaktifkan scroll body
    }

    // Fungsi untuk menyembunyikan modal
    function hideModal() {
        mainAccContent.classList.remove('blurred');
        hoverOverlay.classList.remove('active');
        document.body.style.overflow = ''; // Aktifkan kembali scroll body
        modalHoverBox.innerHTML = ''; // Kosongkan konten modal
    }

    // Event listener untuk tombol close pada modal (akan ditambahkan secara dinamis)
    modalHoverBox.addEventListener('click', function(event) {
        if (event.target.closest('.hover-button-close')) {
            hideModal();
        }
    });

    // Event listener untuk overlay (klik di luar box modal untuk menutup)
    hoverOverlay.addEventListener('click', function(event) {
        if (event.target === hoverOverlay) { // Hanya jika klik langsung pada overlay, bukan pada modal itu sendiri
            hideModal();
        }
    });

    // === 1. Edit Biodata Pengguna ===
    if (editBiodataBtn) {
        editBiodataBtn.addEventListener('click', function() {
            const editProfileHtml = `
                <button class="hover-button-close"><span class="material-symbols-outlined">close</span></button>
                <p class="hover-title">Edit Profile</p>
                <div class="hover-input">
                    <p>Username</p>
                    <input type="text" id="username-hover" class="input-data input-edit" placeholder="Username Anda" value="${currentUsername}">
                </div>
                <div class="hover-input">
                    <p>Nama Lengkap</p>
                    <input type="text" id="fullname-hover" class="input-data input-edit" placeholder="Nama Lengkap Anda" value="${currentFullname}">
                </div>
                <div class="hover-input">
                    <p>Email</p>
                    <input type="email" id="email-hover" class="input-data input-edit" placeholder="Email Anda" value="${currentEmail}">
                </div>
                <div class="hover-input">
                    <p>Nomor Telepon</p>
                    <input type="tel" id="phonenumber-hover" class="input-data input-edit" placeholder="Nomor Telepon Anda" value="${currentPhonenumber}">
                </div>
                <button class="hover-button" id="edit-profile-submit-btn">Ubah Profile</button>
            `;
            showModal(editProfileHtml);

            // Tambahkan event listener untuk tombol submit di dalam modal setelah konten dimuat
            document.getElementById('edit-profile-submit-btn').addEventListener('click', function() {
                // Ambil nilai dari input form
                const newUsername = document.getElementById('username-hover').value;
                const newFullname = document.getElementById('fullname-hover').value;
                const newEmail = document.getElementById('email-hover').value;
                const newPhonenumber = document.getElementById('phonenumber-hover').value;

                // Lakukan validasi sisi klien jika diperlukan
                if (!newUsername || !newFullname || !newEmail || !newPhonenumber) {
                    alert('Semua bidang harus diisi!');
                    return;
                }

                // Kirim data ke edit_profile.php menggunakan AJAX (Fetch API)
                const formData = new FormData();
                formData.append('username', newUsername);
                formData.append('fullname', newFullname);
                formData.append('email', newEmail);
                formData.append('phonenumber', newPhonenumber);

                fetch('edit_profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(data.message);
                        hideModal();
                        location.reload(); // Reload halaman untuk menampilkan data terbaru
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengubah profil.');
                });
            });
        });
    }

    // === 2. Ubah Password Pengguna ===
    if (changePasswordBtn) {
        changePasswordBtn.addEventListener('click', function() {
            const changePasswordHtml = `
                <button class="hover-button-close"><span class="material-symbols-outlined">close</span></button>
                <p class="hover-title">Ubah Password</p>
                <div class="hover-input">
                    <p>Password Lama</p>
                    <input type="password" id="oldpassword-hover" class="input-data input-edit">
                </div>
                <div class="hover-input">
                    <p>Password Baru</p>
                    <input type="password" id="newpassword-hover" class="input-data input-edit">
                </div>
                <div class="hover-input">
                    <p>Konfirmasi Password Baru</p>
                    <input type="password" id="checknewpassword-hover" class="input-data input-edit">
                </div>
                <button class="hover-button" id="change-password-submit-btn">Ubah Password</button>
            `;
            showModal(changePasswordHtml);

            document.getElementById('change-password-submit-btn').addEventListener('click', function() {
                const oldPassword = document.getElementById('oldpassword-hover').value;
                const newPassword = document.getElementById('newpassword-hover').value;
                const checkNewPassword = document.getElementById('checknewpassword-hover').value;

                if (!oldPassword || !newPassword || !checkNewPassword) {
                    alert('Semua bidang password harus diisi!');
                    return;
                }
                if (newPassword !== checkNewPassword) {
                    alert('Password baru tidak cocok dengan konfirmasi!');
                    return;
                }
                if (newPassword.length < 8 || !/[a-z]/.test(newPassword) || !/[A-Z]/.test(newPassword) || !/\d/.test(newPassword) || !/[!@#$%^&*(),.?":{}|<>]/.test(newPassword)) {
                    alert('Password baru tidak memenuhi syarat keamanan! Minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol.');
                    return;
                }
                if (oldPassword === newPassword) {
                    alert('Password baru tidak boleh sama dengan password lama!');
                    return;
                }

                const formData = new FormData();
                formData.append('old_password', oldPassword);
                formData.append('new_password', newPassword);

                fetch('change_password.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(data.message);
                        hideModal();
                        // Mungkin tidak perlu reload, tapi bisa log out atau tampilkan pesan sukses
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengubah password.');
                });
            });
        });
    }

    // === 3. Delete Akun ===
    if (deleteAccBtn) {
        deleteAccBtn.addEventListener('click', function() {
            const deleteAccountHtml = `
                <button class="hover-button-close"><span class="material-symbols-outlined">close</span></button>
                <p class="hover-title">Hapus Akun</p>
                <p>Anda akan menghapus akun ini secara permanen. Tindakan ini tidak dapat diurungkan dan semua data Anda akan hilang selamanya. Sebagai langkah konfirmasi akhir, silakan ketik HAPUS pada kolom di bawah ini untuk melanjutkan.</p>
                <input type="text" id="deleteconfirm-hover" class="input-data input-edit" placeholder="Ketik Kata Konfirmasi Disini">
                <button class="hover-button hover-button-red" id="delete-account-submit-btn">Hapus Akun</button>
            `;
            showModal(deleteAccountHtml);

            const deleteConfirmInput = document.getElementById('deleteconfirm-hover');
            const deleteAccountSubmitBtn = document.getElementById('delete-account-submit-btn');

            // Disable tombol secara default
            deleteAccountSubmitBtn.disabled = true;
            deleteAccountSubmitBtn.style.opacity = '0.6';
            deleteAccountSubmitBtn.style.cursor = 'not-allowed';

            // Event listener untuk input konfirmasi
            deleteConfirmInput.addEventListener('input', function() {
                if (deleteConfirmInput.value === 'HAPUS') {
                    deleteAccountSubmitBtn.disabled = false;
                    deleteAccountSubmitBtn.style.opacity = '1';
                    deleteAccountSubmitBtn.style.cursor = 'pointer';
                } else {
                    deleteAccountSubmitBtn.disabled = true;
                    deleteAccountSubmitBtn.style.opacity = '0.6';
                    deleteAccountSubmitBtn.style.cursor = 'not-allowed';
                }
            });

            deleteAccountSubmitBtn.addEventListener('click', function() {
                if (deleteConfirmInput.value !== 'HAPUS') {
                    alert('Silakan ketik "HAPUS" untuk konfirmasi.');
                    return;
                }

                // Kirim permintaan hapus akun ke delete_account.php
                fetch('delete_account.php', {
                    method: 'POST',
                    body: new FormData() // Tidak ada data khusus yang perlu dikirim selain konfirmasi dari sisi server
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(data.message);
                        window.location.href = '../login/index.php'; // Redirect ke halaman login setelah akun dihapus
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghapus akun.');
                });
            });
        });
    }
    // === 4. Edit Foto Profil ===
    const editPpBtn = document.getElementById('edit-pp');
    if (editPpBtn) {
        editPpBtn.addEventListener('click', function() {
            let cropper;
            const editPpHtml = `
                <button class="hover-button-close"><span class="material-symbols-outlined">close</span></button>
                <p class="hover-title">Ubah Foto Profil</p>
                <div class="drop-zone">
                    <p>Seret & lepas gambar di sini, atau klik untuk memilih file.</p>
                    <input type="file" id="profile-pic-input" accept="image/*" style="display: none;">
                </div>
                <div class="img-container" style="display:none; height: 300px; margin-top: 1rem;">
                    <img id="imageToCrop">
                </div>
                <button class="hover-button" id="save-pp-btn" style="display:none;">Simpan Foto</button>
            `;
            showModal(editPpHtml);

            const dropZone = document.querySelector('.drop-zone');
            const fileInput = document.getElementById('profile-pic-input');
            const imgContainer = document.querySelector('.img-container');
            const imageToCrop = document.getElementById('imageToCrop');
            const saveBtn = document.getElementById('save-pp-btn');

            dropZone.addEventListener('click', () => fileInput.click());
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.style.borderColor = 'var(--blue)';
            });
            dropZone.addEventListener('dragleave', () => dropZone.style.borderColor = 'var(--gray)');
            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.style.borderColor = 'var(--gray)';
                if (e.dataTransfer.files.length) {
                    handleFile(e.dataTransfer.files[0]);
                }
            });
            fileInput.addEventListener('change', () => handleFile(fileInput.files[0]));

            function handleFile(file) {
                if (file && file.type.startsWith('image')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imageToCrop.src = e.target.result;
                        imgContainer.style.display = 'block';
                        saveBtn.style.display = 'block';
                        dropZone.style.display = 'none';

                        if (cropper) cropper.destroy();
                        cropper = new Cropper(imageToCrop, {
                            aspectRatio: 1 / 1,
                            viewMode: 1,
                            background: false,
                        });
                    };
                    reader.readAsDataURL(file);
                }
            }
            
            saveBtn.addEventListener('click', function(){
                if(cropper){
                    const canvas = cropper.getCroppedCanvas({
                        width: 512,
                        height: 512,
                    });
                    
                    canvas.toBlob(function(blob){
                        const formData = new FormData();
                        // Dapatkan data gambar dalam format base64
                        const base64ImageData = canvas.toDataURL('image/png');
                        formData.append('image', base64ImageData);

                        fetch('upload_profile_picture.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if(data.status === 'success'){
                                alert(data.message);
                                location.reload();
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan saat mengunggah gambar.');
                        });
                    });
                }
            });
        });
    }

    // === 5. Hapus Foto Profil ===
    const deletePpBtn = document.getElementById('delete-pp');
    if(deletePpBtn){
        deletePpBtn.addEventListener('click', function() {
            const deletePpHtml = `
                <button class="hover-button-close"><span class="material-symbols-outlined">close</span></button>
                <p class="hover-title">Hapus Foto Profil</p>
                <p>Anda yakin ingin menghapus foto profil Anda? Foto akan dikembalikan ke gambar default.</p>
                <button class="hover-button hover-button-red" id="delete-pp-confirm-btn">Ya, Hapus Foto Profil</button>
            `;
            showModal(deletePpHtml);

            document.getElementById('delete-pp-confirm-btn').addEventListener('click', function() {
                fetch('delete_profile_picture.php', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan.');
                });
            });
        });
    }
});