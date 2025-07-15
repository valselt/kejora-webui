const textarea = document.getElementById('prompt-input');

textarea.addEventListener('input', () => {
    // Reset tinggi agar bisa menyusut kembali jika teks dihapus
    textarea.style.height = 'auto';
    
    // Atur tinggi sesuai dengan tinggi konten scroll-nya
    textarea.style.height = textarea.scrollHeight + 'px';
});