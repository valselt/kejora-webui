// dropdown.js

// DATA UNTUK MODEL LISTS
const modelLists = {
    groq: `
        <a href="#" class="custom-dropdown-item" data-value="gemma2-9b-it"><div class="ai-dropdown-things"><div class="title-ai"><img src="https://i.postimg.cc/pTxzwhCw/gemma.png"/><span class="text">Gemma 2</span></div></div><div class="check-api"><span class="version-text">9b-it</span></div></a>
        <a href="#" class="custom-dropdown-item" data-value="llama-3.1-8b-instant"><div class="ai-dropdown-things"><div class="title-ai"><img src="https://i.postimg.cc/MTDVq6rv/llama.png"/><span class="text">Llama 3.1</span></div></div><div class="check-api"><span class="version-text">8b-instant</span></div></a>
        <a href="#" class="custom-dropdown-item" data-value="llama-3.3-70b-versatile"><div class="ai-dropdown-things"><div class="title-ai"><img src="https://i.postimg.cc/MTDVq6rv/llama.png"/><span class="text">Llama 3.3</span></div></div><div class="check-api"><span class="version-text">70b Versatile</span></div></a>
        <a href="#" class="custom-dropdown-item" data-value="meta-llama/llama-guard-4-12b"><div class="ai-dropdown-things"><div class="title-ai"><img src="https://i.postimg.cc/MTDVq6rv/llama.png"/><span class="text">Llama Guard 4</span></div></div><div class="check-api"><span class="version-text">12b</span></div></a>
        <a href="#" class="custom-dropdown-item" data-value="whisper-large-v3"><div class="ai-dropdown-things"><div class="title-ai"><img src="https://i.postimg.cc/FRvHCSKW/openai.png"/><span class="text">Whisper Large</span></div></div><div class="check-api"><span class="version-text">v3</span></div></a>
        <a href="#" class="custom-dropdown-item" data-value="whisper-large-v3-turbo"><div class="ai-dropdown-things"><div class="title-ai"><img src="https://i.postimg.cc/FRvHCSKW/openai.png"/><span class="text">Whisper Large</span></div></div><div class="check-api"><span class="version-text">v3 Turbo</span></div></a>
    `,
    googleaistudio: `
        <a href="#" class="custom-dropdown-item" data-value="gemini-2.5-pro"><div class="ai-dropdown-things"><div class="title-ai"><img src="https://i.postimg.cc/pTxzwhCw/gemma.png"/><span class="text">Gemini 2.5</span></div></div><div class="check-api"><span class="version-text">Pro</span></div></a>
        <a href="#" class="custom-dropdown-item" data-value="gemini-2.5-flash"><div class="ai-dropdown-things"><div class="title-ai"><img src="https://i.postimg.cc/pTxzwhCw/gemma.png"/><span class="text">Gemini 2.5</span></div></div><div class="check-api"><span class="version-text">Flash</span></div></a>
        <a href="#" class="custom-dropdown-item" data-value="gemini-2.0-flash"><div class="ai-dropdown-things"><div class="title-ai"><img src="https://i.postimg.cc/pTxzwhCw/gemma.png"/><span class="text">Gemini 2.0</span></div></div><div class="check-api"><span class="version-text">Flash</span></div></a>
        <a href="#" class="custom-dropdown-item" data-value="gemini-1.5-pro"><div class="ai-dropdown-things"><div class="title-ai"><img src="https://i.postimg.cc/pTxzwhCw/gemma.png"/><span class="text">Gemini 1.5</span></div></div><div class="check-api"><span class="version-text">Pro</span></div></a>
        <a href="#" class="custom-dropdown-item" data-value="gemini-1.5-flash"><div class="ai-dropdown-things"><div class="title-ai"><img src="https://i.postimg.cc/pTxzwhCw/gemma.png"/><span class="text">Gemini 1.5</span></div></div><div class="check-api"><span class="version-text">Flash</span></div></a>
    `
};

// VARIABEL GLOBAL
let selectedAiModel = null;

document.addEventListener('DOMContentLoaded', function() {
    
    // Ambil elemen-elemen penting
    const allDropdowns = document.querySelectorAll('.custom-dropdown-wrapper');
    const modelDropdownButton = document.querySelector('#model-selected');
    const modelDropdownPanel = document.querySelector('#models-panel');
    const modelSelectedContent = document.querySelector('#model-selected-content');

    // Fungsi untuk mengaplikasikan event listener ke item dropdown
    function applyItemListeners(panel) {
        const items = panel.querySelectorAll('.custom-dropdown-item');
        const wrapper = panel.parentElement;
        const selectedContentContainer = wrapper.querySelector('.selected-item-content');

        items.forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const newContentHTML = item.querySelector('.ai-dropdown-things').innerHTML;
                const value = item.getAttribute('data-value');

                if (wrapper.querySelector('#model-selected')) {
                    selectedAiModel = value;
                    console.log("Model AI yang dipilih:", selectedAiModel);
                }

                // Jika item yang diklik ada di dropdown server
                if (wrapper.querySelector('#server-selected')) {
                    updateModelList(value);
                    // Aktifkan tombol model dengan menghapus kelas is-disabled
                    modelDropdownButton.classList.remove('is-disabled');
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
        
        // Reset pilihan model yang terlihat menjadi "-"
        modelSelectedContent.innerHTML = '<span>Model</span>';
        selectedAiModel = null; // Reset variabel global juga
        
        // Aplikasikan lagi listener untuk item-item model yang baru
        applyItemListeners(modelDropdownPanel);
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

        // Hanya terapkan listener jika panelnya ada (untuk menghindari error)
        if (panel) {
            applyItemListeners(panel);
        }
    });
    
    // Muat daftar model default (misalnya groq) saat pertama kali halaman dibuka
    updateModelList('groq');

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