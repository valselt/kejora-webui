// login/login.js
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');

    loginForm.addEventListener('submit', function(event) {
        // Simple client-side validation
        if (usernameInput.value.trim() === '' || passwordInput.value.trim() === '') {
            alert('Harap isi username dan password Anda.');
            event.preventDefault(); // Mencegah form disubmit jika ada field kosong
        }
        // Lebih banyak validasi bisa ditambahkan di sini jika diperlukan
    });
});