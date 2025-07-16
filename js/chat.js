document.addEventListener('DOMContentLoaded', function() {
    // --- Elemen-elemen UI ---
    const chatForm = document.querySelector('.chat-bar');
    const promptInput = document.getElementById('prompt-input');
    const sendButton = chatForm.querySelector('.send-btn');
    const sendButtonIcon = sendButton.querySelector('.material-symbols-outlined');
    const conversationContainer = document.querySelector('.input-output');
    const chatOpener = document.querySelector('.chat-opener');
    const newChatButton = document.querySelector('.new-chat');
    const chatListContainer = document.querySelector('.list-chats');

    let currentConversationId = null;

    // --- FUNGSI-FUNGSI UTAMA ---

    /** Memuat daftar percakapan saat halaman dibuka */
    async function loadInitialConversations() {
        try {
            const response = await fetch('chat_api.php?action=get_conversations');
            const data = await response.json();
            if (data.status === 'success') {
                chatListContainer.innerHTML = ''; // Kosongkan placeholder
                data.conversations.forEach(conv => {
                    addConversationToList(conv.id, conv.title, false); // false = jangan set aktif
                });
            }
        } catch (error) {
            console.error('Gagal memuat percakapan:', error);
        }
    }
    
    /** Memuat pesan dari percakapan yang dipilih */
    async function loadConversation(convId) {
        if (!convId) return;

        resetChatView();
        chatOpener.style.display = 'none';
        currentConversationId = convId;
        updateActiveChatInList(convId);

        try {
            const response = await fetch(`chat_api.php?action=get_messages&conversation_id=${convId}`);
            const data = await response.json();
            if (data.status === 'success') {
                data.messages.forEach(msg => {
                    appendMessage(msg.sender, msg.content);
                });
            }
        } catch (error) {
            console.error('Gagal memuat pesan:', error);
            appendMessage('ai', 'Gagal memuat riwayat pesan.', true);
        }
    }
    
    /** Fungsi utama pengiriman chat */
    async function handleChatSubmit(e) {
        e.preventDefault();
        const promptText = promptInput.value.trim();

        if (!promptText) return;
        if (!selectedServer || !selectedAiModel) {
            alert('Silakan pilih Server dan Model AI terlebih dahulu.');
            return;
        }

        setLoadingState(true);
        if (chatOpener) chatOpener.style.display = 'none';
        appendMessage('user', promptText);
        promptInput.value = '';
        promptInput.style.height = 'auto';

        try {
            const response = await fetch('chat_api.php?action=send_chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    prompt: promptText,
                    server: selectedServer,
                    model: selectedAiModel,
                    conversation_id: currentConversationId
                })
            });

            const result = await response.json();

            if (result.status === 'success') {
                appendMessage('ai', result.message);
                
                // Jika ini percakapan baru, tambahkan ke list
                if (result.title) {
                    addConversationToList(result.conversation_id, result.title, true); // true = set aktif
                }
                currentConversationId = result.conversation_id; // Selalu update ID
            } else {
                appendMessage('ai', result.message, true);
            }
        } catch (error) {
            console.error('Fetch error:', error);
            appendMessage('ai', 'Gagal terhubung ke server. Periksa koneksi Anda.', true);
        } finally {
            setLoadingState(false);
        }
    }

    // --- FUNGSI-FUNGSI BANTUAN ---

    /** Mengatur ulang tampilan chat ke kondisi awal */
    function resetChatView() {
        conversationContainer.innerHTML = '';
        chatOpener.style.display = 'flex';
        currentConversationId = null;
        updateActiveChatInList(null);
    }

    /** Menambahkan item percakapan ke sidebar */
    function addConversationToList(id, title, setActive) {
        const chatItem = document.createElement('p');
        chatItem.className = 'bar-chats';
        chatItem.textContent = title;
        chatItem.dataset.conversationId = id;
        chatItem.title = title;
        
        chatListContainer.prepend(chatItem); // Tambahkan ke paling atas
        if (setActive) {
            updateActiveChatInList(id);
        }
    }

    /** Mengupdate item mana yang aktif di sidebar */
    function updateActiveChatInList(activeId) {
        const allChats = chatListContainer.querySelectorAll('.bar-chats');
        allChats.forEach(chat => {
            if (chat.dataset.conversationId == activeId) {
                chat.classList.add('active');
            } else {
                chat.classList.remove('active');
            }
        });
    }

    /** Mengatur status loading pada tombol kirim */
    function setLoadingState(isLoading) {
        if (isLoading) {
            sendButtonIcon.textContent = 'hourglass_empty';
            sendButton.classList.add('spinning');
            promptInput.disabled = true;
            sendButton.disabled = true;
        } else {
            sendButtonIcon.textContent = 'send';
            sendButton.classList.remove('spinning');
            promptInput.disabled = false;
            sendButton.disabled = false;
        }
    }

    /** Menambahkan bubble pesan ke dalam kontainer chat */
    function appendMessage(sender, text, isError = false) {
        let messageHtml = '';
        const sanitizedText = text.replace(/</g, "&lt;").replace(/>/g, "&gt;");
        if (sender === 'user') {
            messageHtml = `<div class="prompt-box-input"><p>${sanitizedText}</p></div>`;
        } else { // sender === 'ai'
            const errorClass = isError ? 'error-bubble' : '';
            messageHtml = `<div class="answer-output ${errorClass}"><img src="images/kejoraai.png" class="logo-output"/><p>${sanitizedText}</p></div>`;
        }
        conversationContainer.insertAdjacentHTML('beforeend', messageHtml);
        conversationContainer.scrollTop = conversationContainer.scrollHeight;
    }

    // --- EVENT LISTENERS ---
    
    // Kirim chat
    chatForm.addEventListener('submit', handleChatSubmit);
    
    // Tombol New Chat
    newChatButton.addEventListener('click', resetChatView);
    
    // Klik pada list chat (Event Delegation)
    chatListContainer.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('bar-chats')) {
            const convId = e.target.dataset.conversationId;
            if (convId && convId != currentConversationId) {
                loadConversation(convId);
            }
        }
    });

    // Auto-resize textarea
    promptInput.addEventListener('input', () => {
        promptInput.style.height = 'auto';
        promptInput.style.height = `${promptInput.scrollHeight}px`;
    });

    // --- INISIALISASI ---
    loadInitialConversations();
});