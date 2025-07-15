// navbar.js

document.addEventListener('DOMContentLoaded', function() {
    // Ambil semua elemen yang kita butuhkan
    const chatButton = document.querySelector('.chat-button');
    const container = document.querySelector('.container');
    const menuChat = document.querySelector('.menu-chat');

    // Pastikan semua elemen ada
    if (chatButton && container && menuChat) {

        // Fungsi ini akan berjalan SETELAH animasi transisi selesai
        function afterTransition() {
            // Jika menu dalam kondisi tertutup (tidak ada kelas 'chat-menu-open')
            if (!container.classList.contains('chat-menu-open')) {
                // Maka sembunyikan total elemennya
                menuChat.style.display = 'none';
            }
        }

        // Tambahkan "pendengar" untuk event 'transitionend' pada menu
        menuChat.addEventListener('transitionend', afterTransition);

        // Saat tombol chat diklik
        chatButton.addEventListener('click', function() {
            // Cek apakah kita akan membuka atau menutup menu
            const isOpening = !container.classList.contains('chat-menu-open');

            if (isOpening) {
                // Jika mau BUKA: hilangkan dulu display:none agar bisa dianimasikan
                menuChat.style.display = 'flex';
            }

            // Beri jeda sesaat agar browser sempat menerapkan 'display: flex'
            // sebelum memulai transisi dengan menambahkan kelas.
            setTimeout(() => {
                container.classList.toggle('chat-menu-open');
            }, 10);
        });
    }
});