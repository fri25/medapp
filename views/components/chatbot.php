<!-- Bouton de chat flottant -->
<button id="chatButton" class="fixed bottom-4 right-4 bg-[#3b82f6] hover:bg-[#2563eb] text-white rounded-full w-14 h-14 text-2xl shadow-lg transition-colors duration-300 flex items-center justify-center">
    <i class="fas fa-comments"></i>
</button>

<!-- Modal de chat -->
<div id="chatModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg w-full max-w-md shadow-xl">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold text-[#1e40af]">Assistant Médical</h3>
            <button id="closeChatModal" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="chatMessages" class="p-4 h-96 overflow-y-auto space-y-4"></div>
        <form id="chatForm" class="p-4 border-t">
            <div class="flex gap-2">
                <input type="text" id="messageInput" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:border-[#2E7D32]" placeholder="Écrivez votre message..." required>
                <button type="submit" class="bg-[#3b82f6] hover:bg-[#2563eb] text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>