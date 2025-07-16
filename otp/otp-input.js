document.addEventListener('DOMContentLoaded', () => {

    // === LOGIKA INPUT OTP (Auto-tab & Backspace) ===
    const otpInputs = document.querySelectorAll('.otp-input');
    const otpForm = document.getElementById('otpForm'); // Asumsikan form Anda punya ID ini

    otpInputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            // Bersihkan input dari karakter non-digit
            e.target.value = e.target.value.replace(/[^0-9]/g, '');

            // Jika input sudah terisi dan bukan input terakhir, fokus ke input berikutnya
            if (e.target.value.length === e.target.maxLength && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', (e) => {
            // Jika tombol backspace ditekan dan input kosong, fokus ke input sebelumnya
            if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                otpInputs[index - 1].focus();
            }
        });
    });

    // Otomatis submit form jika input terakhir sudah terisi
    if (otpInputs.length > 0) {
        otpInputs[otpInputs.length - 1].addEventListener('input', () => {
            const allFilled = [...otpInputs].every(input => input.value.length === input.maxLength);
            if (allFilled) {
                // Menggabungkan semua nilai input menjadi satu string OTP
                const otpValue = [...otpInputs].map(input => input.value).join('');
                console.log('OTP Lengkap, mengirim form:', otpValue);
                // Jika Anda punya input tersembunyi untuk OTP lengkap, set nilainya di sini
                // document.getElementById('full-otp-input').value = otpValue;
                // otpForm.submit(); // Anda bisa aktifkan ini jika ingin auto-submit
            }
        });
    }

    // === LOGIKA TOMBOL KIRIM ULANG OTP ===
    const resendBtn = document.querySelector('.resend-otp');
    if (resendBtn) {
        let cooldownTimer;

        function startCooldown(seconds) {
            resendBtn.disabled = true;
            let remaining = seconds;

            resendBtn.textContent = `Kirim Ulang OTP (${remaining}s)`;

            cooldownTimer = setInterval(() => {
                remaining--;
                if (remaining > 0) {
                    resendBtn.textContent = `Kirim Ulang OTP (${remaining}s)`;
                } else {
                    clearInterval(cooldownTimer);
                    resendBtn.textContent = 'Kirim Ulang OTP';
                    resendBtn.disabled = false;
                }
            }, 1000);
        }

        resendBtn.addEventListener('click', function() {
            // Nonaktifkan tombol sementara untuk mencegah klik ganda
            resendBtn.disabled = true;
            resendBtn.textContent = 'Mengirim...';

            // --- PERUBAHAN DI SINI ---
            // Kirim permintaan ke backend untuk meminta OTP baru
            fetch('resend_otp.php', { method: 'POST' })
              .then(response => response.json())
              .then(data => {
                 if (data.status === 'success') {
                     alert(data.message);
                     // Mulai cooldown HANYA jika pengiriman sukses
                     startCooldown(180); 
                 } else {
                     alert('Error: ' + data.message);
                     // Jika gagal, aktifkan kembali tombolnya tanpa cooldown
                     resendBtn.disabled = false;
                     resendBtn.textContent = 'Kirim Ulang OTP';
                 }
              })
              .catch(error => {
                  console.error('Fetch error:', error);
                  alert('Terjadi kesalahan koneksi. Silakan coba lagi.');
                  resendBtn.disabled = false;
                  resendBtn.textContent = 'Kirim Ulang OTP';
              });
            // --- AKHIR PERUBAHAN ---
        });
    }
});