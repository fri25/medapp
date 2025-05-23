document.addEventListener('DOMContentLoaded', function() {
    const chatButton = document.getElementById('chatButton');
    const chatModal = document.getElementById('chatModal');
    const closeChatModal = document.getElementById('closeChatModal');
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const chatMessages = document.getElementById('chatMessages');

    // Ouvrir/fermer le modal
    chatButton.addEventListener('click', () => {
        chatModal.classList.remove('hidden');
    });

    closeChatModal.addEventListener('click', () => {
        chatModal.classList.add('hidden');
    });

    // Gérer l'envoi des messages
    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const message = messageInput.value.trim();
        if (!message) return;

        // Afficher le message de l'utilisateur
        appendMessage('user', message);
        messageInput.value = '';

        try {
            const response = await fetch('/medapp/views/patient/chatbot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message })
            });

            const data = await response.json();
            appendMessage('bot', data.reply);
        } catch (error) {
            appendMessage('bot', 'Désolé, une erreur est survenue.');
        }
    });

    function appendMessage(type, content) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `flex ${type === 'user' ? 'justify-end' : 'justify-start'}`;
        
        const messageBubble = document.createElement('div');
        messageBubble.className = type === 'user' 
            ? 'bg-[#3b82f6] text-white rounded-lg py-2 px-4 max-w-[80%]'
            : 'bg-gray-100 text-gray-800 rounded-lg py-2 px-4 max-w-[80%]';
        messageBubble.textContent = content;
        
        messageDiv.appendChild(messageBubble);
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
});