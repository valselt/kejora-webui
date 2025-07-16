// dropdown.js

// DATA UNTUK MODEL LISTS (Ini tetap sama)
const modelLists = {
    groq: `
        <a href="#" class="custom-dropdown-item" data-value="gemma2-9b-it"><div class="ai-dropdown-things"><div class="title-ai"><img src="images/icon/gemma.png"/><span class="text">Gemma 2</span></div></div><div class="check-api"><span class="version-text">9b-it</span></div></a>
        <a href="#" class="custom-dropdown-item" data-value="llama-3.1-8b-instant"><div class="ai-dropdown-things"><div class="title-ai"><img src="images/icon/llama.png"/><span class="text">Llama 3.1</span></div></div><div class="check-api"><span class="version-text">8b-instant</span></div></a>
        <a href="#" class="custom-dropdown-item" data-value="llama-3.3-70b-versatile"><div class="ai-dropdown-things"><div class="title-ai"><img src="images/icon/llama.png"/><span class="text">Llama 3.3</span></div></div><div class="check-api"><span class="version-text">70b Versatile</span></div></a>
        <a href="#" class="custom-dropdown-item" data-value="meta-llama/llama-guard-4-12b"><div class="ai-dropdown-things"><div class="title-ai"><img src="images/icon/llama.png"/><span class="text">Llama Guard 4</span></div></div><div class="check-api"><span class="version-text">12b</span></div></a>
    `,
    googleaistudio: `
        <a href="#" class="custom-dropdown-item" data-value="gemini-2.5-pro"><div class="ai-dropdown-things"><div class="title-ai"><img src="images/icon/gemma.png"/><span class="text">Gemini 2.5</span></div></div><div class="check-api"><span class="version-text">Pro</span></div></a>
        <a href="#" class="custom-dropdown-item" data-value="gemini-2.5-flash"><div class="ai-dropdown-things"><div class="title-ai"><img src="images/icon/gemma.png"/><span class="text">Gemini 2.5</span></div></div><div class="check-api"><span class="version-text">Flash</span></div></a>
        <a href="#" class="custom-dropdown-item" data-value="gemini-2.0-flash"><div class="ai-dropdown-things"><div class="title-ai"><img src="images/icon/gemma.png"/><span class="text">Gemini 2.0</span></div></div><div class="check-api"><span class="version-text">Flash</span></div></a>
        <a href="#" class="custom-dropdown-item" data-value="gemini-1.5-pro"><div class="ai-dropdown-things"><div class="title-ai"><img src="images/icon/gemma.png"/><span class="text">Gemini 1.5</span></div></div><div class="check-api"><span class="version-text">Pro</span></div></a>
        <a href="#" class="custom-dropdown-item" data-value="gemini-1.5-flash"><div class="ai-dropdown-things"><div class="title-ai"><img src="images/icon/gemma.png"/><span class="text">Gemini 1.5</span></div></div><div class="check-api"><span class="version-text">Flash</span></div></a>
    `
};

// VARIABEL GLOBAL
let selectedAiModel = null;
let selectedServer = null; 

document.addEventListener('DOMContentLoaded', function() {
    
    const allDropdowns = document.querySelectorAll('.custom-dropdown-wrapper');
    const modelDropdownButton = document.querySelector('#model-selected');
    const modelDropdownPanel = document.querySelector('#models-panel');
    const modelSelectedContent = document.querySelector('#model-selected-content');
    const serverDropdownPanel = document.querySelector('.custom-dropdown-panel'); // Panel untuk server

    // Fungsi untuk mengaplikasikan event listener ke item dropdown
    function applyItemListeners(panel) {
        const items = panel.querySelectorAll('.custom-dropdown-item');
        const wrapper = panel.parentElement;
        const selectedContentContainer = wrapper.querySelector('.selected-item-content');

        items.forEach(function(item) {
            item.addEventListener('click', function(e) {
                if (item.classList.contains('is-disabled')) {
                    e.preventDefault(); 
                    e.stopPropagation(); 
                    alert('API key untuk server ini belum dikonfigurasi. Silakan konfigurasi di Account Center.');
                    closeAllDropdowns(null); 
                    return; 
                }

                e.preventDefault(); 
                e.stopPropagation(); 
                
                const newContentHTML = item.querySelector('.ai-dropdown-things').innerHTML;
                const value = item.getAttribute('data-value');

                // Jika item yang diklik ada di dropdown server
                if (wrapper.querySelector('#server-selected')) {
                    selectedServer = value; // Simpan server yang dipilih
                    updateModelList(value);
                    updateModelButtonState(); // Panggil fungsi untuk memperbarui status tombol Model
                    console.log("Server AI yang dipilih:", selectedServer); 
                } else if (wrapper.querySelector('#model-selected')) {
                    selectedAiModel = value;
                    console.log("Model AI yang dipilih:", selectedAiModel);
                }

                selectedContentContainer.innerHTML = newContentHTML;
                panel.classList.remove('show');
            });
        });
    }

    // Fungsi untuk mengupdate daftar model
    function updateModelList(serverValue) {
        const newModelHTML = modelLists[serverValue] || '';
        modelDropdownPanel.innerHTML = newModelHTML;
        
        // --- Baris ini sudah ada, memastikan text 'Model' muncul saat server dipilih ---
        modelSelectedContent.innerHTML = '<span>Model</span>'; 
        selectedAiModel = null; 
        
        applyItemListeners(modelDropdownPanel);
    }

    // Fungsi untuk memperbarui status tombol Model dan memastikan teks "Model" terlihat
    function updateModelButtonState() {
        const groqServerItem = serverDropdownPanel.querySelector('.custom-dropdown-item[data-value="groq"]');
        const googleServerItem = serverDropdownPanel.querySelector('.custom-dropdown-item[data-value="googleaistudio"]');

        let isAnyApiConfigured = false;
        if (groqServerItem && groqServerItem.getAttribute('data-api-status') === 'green') {
            isAnyApiConfigured = true;
        }
        if (googleServerItem && googleServerItem.getAttribute('data-api-status') === 'green') {
            isAnyApiConfigured = true;
        }

        // Jika tidak ada API yang dikonfigurasi sama sekali, tombol Model disabled total
        if (!isAnyApiConfigured) {
            modelDropdownButton.classList.add('is-disabled');
            modelDropdownButton.classList.remove('no-server-selected'); 
        } 
        // Jika ada API terkonfigurasi, tapi belum ada server yang dipilih
        else if (selectedServer === null) {
            modelDropdownButton.classList.remove('is-disabled'); 
            modelDropdownButton.classList.add('no-server-selected'); // Tambahkan kelas abu-abu
        } 
        // Jika server sudah dipilih dan API terkonfigurasi
        else {
            modelDropdownButton.classList.remove('is-disabled');
            modelDropdownButton.classList.remove('no-server-selected'); 
        }

        // --- Perbaikan di SINI: Pastikan teks "Model" selalu terlihat ---
        // Jika tidak ada model spesifik yang dipilih (saat inisialisasi atau setelah memilih server baru)
        // maka pastikan konten menampilkan "Model".
        if (selectedAiModel === null) {
            // Cek apakah konten saat ini sudah "Model" untuk menghindari update DOM yang tidak perlu.
            // Gunakan textContent untuk membandingkan teks murni, bukan HTML.
            if (modelSelectedContent.textContent.trim() !== 'Model') {
                modelSelectedContent.innerHTML = '<span>Model</span>';
            }
        }
        // --- AKHIR PERBAIKAN ---
    }
    
    // Loop awal untuk semua dropdown yang ada saat halaman dimuat
    allDropdowns.forEach(wrapper => {
        const button = wrapper.querySelector('.custom-dropdown-button');
        const panel = wrapper.querySelector('.custom-dropdown-panel');

        button.addEventListener('click', function(event) {
            event.stopPropagation();
            closeAllDropdowns(wrapper);
            panel.classList.toggle('show');
        });

        if (panel) {
            applyItemListeners(panel);
        }
    });
    
    // Panggil fungsi ini saat halaman pertama kali dimuat untuk mengatur status awal tombol Model
    updateModelButtonState();


    // Fungsi untuk menutup semua dropdown
    function closeAllDropdowns(exceptThisOne) {
        allDropdowns.forEach(wrapper => {
            const panel = wrapper.querySelector('.custom-dropdown-panel');
            if (wrapper !== exceptThisOne && panel) {
                panel.classList.remove('show');
            }
        });
    }

    window.addEventListener('click', function() {
        closeAllDropdowns(null);
    });
});