document.addEventListener('DOMContentLoaded', function() {
    // =================================================
    // 1. PENGAMBILAN ELEMEN DARI HTML
    // =================================================
    const registerForm = document.getElementById('registerForm');
    const passwordInput = document.getElementById('password');
    const passwordCheckInput = document.getElementById('password-check');
    const allRequiredInputs = registerForm.querySelectorAll('input[required]'); // Memastikan ini berada di dalam form

    // =================================================
    // 2. LOGIKA VALIDASI KEKUATAN PASSWORD
    // =================================================

    // Buat elemen untuk daftar kriteria sekali saja saat script dimuat
    const criteriaList = document.createElement('div');
    criteriaList.id = 'password-criteria';
    criteriaList.innerHTML = `
        <p id="length">Minimal 8 karakter</p>
        <p id="lower">Minimal satu huruf kecil</p>
        <p id="upper">Minimal satu huruf besar</p>
        <p id="number">Minimal satu angka</p>
        <p id="symbol">Minimal satu simbol</p>
    `;
    // Secara default, sembunyikan daftar kriteria ini
    criteriaList.style.display = 'none';

    // Masukkan daftar kriteria ke dalam DOM setelah input password
    passwordInput.insertAdjacentElement('afterend', criteriaList);

    // Ambil referensi ke setiap elemen kriteria
    const lengthCheck = criteriaList.querySelector('#length');
    const lowerCaseCheck = criteriaList.querySelector('#lower');
    const upperCaseCheck = criteriaList.querySelector('#upper');
    const numberCheck = criteriaList.querySelector('#number');
    const symbolCheck = criteriaList.querySelector('#symbol');
    
    // Fungsi untuk mengecek kekuatan password
    function checkPasswordStrength() {
        // Tampilkan kriteria ketika input password difokuskan atau mulai diketik
        criteriaList.style.display = 'flex';

        const password = passwordInput.value;
        const hasLength = password.length >= 8;
        const hasLower = /[a-z]/.test(password);
        const hasUpper = /[A-Z]/.test(password);
        const hasNumber = /\d/.test(password);
        const hasSymbol = /[!@#$%^&*(),.?":{}|<>]/.test(password);

        // Update class 'valid' untuk mengubah warna background
        lengthCheck.classList.toggle('valid', hasLength);
        lowerCaseCheck.classList.toggle('valid', hasLower);
        upperCaseCheck.classList.toggle('valid', hasUpper);
        numberCheck.classList.toggle('valid', hasNumber);
        symbolCheck.classList.toggle('valid', hasSymbol);

        const allValid = hasLength && hasLower && hasUpper && hasNumber && hasSymbol;

        // Sembunyikan kriteria jika semua terpenuhi DAN input tidak kosong
        if (allValid && password !== '') {
            criteriaList.style.display = 'none';
        } else if (password === '') { // Jika password kosong, sembunyikan juga kriteria
            criteriaList.style.display = 'none';
        }

        return allValid;
    }

    // =================================================
    // 3. LOGIKA PENCOCOKAN PASSWORD
    // =================================================
    function handlePasswordMismatch() {
        const existingError = document.getElementById('password-error-message');
        if (existingError) {
            existingError.remove();
        }
        if (passwordInput.value !== passwordCheckInput.value && passwordCheckInput.value !== '') {
            const errorMessage = document.createElement('p');
            errorMessage.id = 'password-error-message';
            errorMessage.textContent = 'Password Tidak Sama';
            errorMessage.style.color = 'var(--red)';
            errorMessage.style.fontSize = '0.8rem';
            errorMessage.style.marginTop = '1px';
            passwordCheckInput.insertAdjacentElement('afterend', errorMessage);
        }
    }

    // =================================================
    // 4. EVENT LISTENERS
    // =================================================
    passwordInput.addEventListener('input', () => {
        checkPasswordStrength();
        handlePasswordMismatch();
    });

    passwordCheckInput.addEventListener('input', handlePasswordMismatch);

    // Tambahkan event listener untuk fokus dan blur pada passwordInput
    passwordInput.addEventListener('focus', () => {
        checkPasswordStrength(); // Tampilkan kriteria saat fokus
    });

    passwordInput.addEventListener('blur', () => {
        // Sembunyikan kriteria saat blur jika semua valid atau input kosong
        const password = passwordInput.value;
        const hasLength = password.length >= 8;
        const hasLower = /[a-z]/.test(password);
        const hasUpper = /[A-Z]/.test(password);
        const hasNumber = /\d/.test(password);
        const hasSymbol = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        const allValid = hasLength && hasLower && hasUpper && hasNumber && hasSymbol;

        if (allValid || password === '') {
            criteriaList.style.display = 'none';
        }
    });


    registerForm.addEventListener('submit', function(event) {
        // Validasi sisi klien sebelum mengirim ke server
        let allFieldsFilled = true;
        allRequiredInputs.forEach(input => {
            if (input.value.trim() === '') {
                allFieldsFilled = false;
            }
        });
        if (!allFieldsFilled) {
            alert('Harap isi semua bidang yang wajib diisi.');
            event.preventDefault(); // Mencegah submit form
            return;
        }

        if (!checkPasswordStrength()) {
            alert('Password tidak memenuhi syarat: minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol.');
            event.preventDefault(); // Mencegah submit form
            return;
        }

        if (passwordInput.value !== passwordCheckInput.value) {
            alert('Password Tidak Sama!');
            event.preventDefault(); // Mencegah submit form
            return;
        }

        // Jika semua validasi klien berhasil, form akan disubmit secara alami
        // alert('Registrasi berhasil! Mengirim data...'); // Komentar ini karena akan di-redirect
        // registerForm.submit(); // Ini tidak perlu lagi karena event.preventDefault() sudah dihapus jika valid
    });
});