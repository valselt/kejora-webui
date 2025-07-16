document.addEventListener('DOMContentLoaded', function() {
    const groqApiInput = document.getElementById('groq_api_input');
    const groqApiSaveBtn = document.getElementById('groq_api_save');
    const googleApiInput = document.getElementById('google_api_input');
    const googleApiSaveBtn = document.getElementById('google_api_save');

    // Fungsi untuk menampilkan pesan (mirip dengan session messages, tapi di JS)
    function showAlert(message, type) {
        const messageArea = document.querySelector('.main-acc .box-acc'); // Atau area yang lebih spesifik
        if (!messageArea) return;

        const existingAlert = messageArea.querySelector('.alert-js');
        if (existingAlert) {
            existingAlert.remove();
        }

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert-js ${type}`; // Add a specific class for JS alerts
        alertDiv.style.width = '70vw';
        alertDiv.style.padding = 'var(--space-s)';
        alertDiv.style.borderRadius = 'var(--radius-m)';
        alertDiv.style.textAlign = 'center';
        alertDiv.style.marginBottom = 'var(--space-m)';
        alertDiv.style.fontWeight = 'bold';

        if (type === 'success') {
            alertDiv.style.backgroundColor = 'var(--green)';
            alertDiv.style.color = 'var(--white)';
        } else if (type === 'error') {
            alertDiv.style.backgroundColor = 'var(--red)';
            alertDiv.style.color = 'var(--white)';
        } else { // Default styling
            alertDiv.style.backgroundColor = 'var(--gray)';
            alertDiv.style.color = 'var(--black)';
        }

        alertDiv.textContent = message;
        messageArea.prepend(alertDiv); // Insert at the beginning of box-acc

        // Hapus alert setelah beberapa detik
        setTimeout(() => {
            alertDiv.remove();
        }, 3000); // Pesan akan hilang setelah 3 detik
    }


    // Fungsi umum untuk mengirim API key
    async function saveApiKey(apiType, apiValue) {
        const formData = new FormData();
        formData.append('action', 'save_api_key');
        formData.append('api_type', apiType);
        formData.append('api_value', apiValue);

        try {
            const response = await fetch('../api.php', { // Path ke file api.php di root
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.status === 'success') {
                showAlert(result.message, 'success');
                // Tidak perlu reload, karena session akan diupdate server
            } else {
                showAlert(result.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Terjadi kesalahan koneksi. Silakan coba lagi.', 'error');
        }
    }

    // Event Listeners untuk tombol Save API
    if (groqApiSaveBtn) {
        groqApiSaveBtn.addEventListener('click', function() {
            saveApiKey('groq', groqApiInput.value);
        });
    }

    if (googleApiSaveBtn) {
        googleApiSaveBtn.addEventListener('click', function() {
            saveApiKey('google', googleApiInput.value);
        });
    }
});