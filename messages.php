<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/vendor/autoload.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    header("Location: admin/");
    exit;
}

$db = new MysqliDb();
$current_user_id = $_SESSION['user_id'];

// Handle search request
if (isset($_GET['search'])) {
    $query = trim($_GET['q'] ?? '');
    
    if (!empty($query)) {
        // Search users
        $db->where("(username LIKE ? OR user_id IN (
            SELECT user_id FROM user_profile 
            WHERE first_name LIKE ? OR last_name LIKE ?
        ))", ["%$query%", "%$query%", "%$query%"]);
        $db->where("user_id != ?", [$current_user_id]);
        $users = $db->get('users', 10, ['user_id', 'username']);
        
        // Search messages
        $db->where("content LIKE ?", ["%$query%"]);
        $db->where("sender_id = ? OR receiver_id = ?", [$current_user_id, $current_user_id]);
        $messages = $db->get('messages', 10, ['message_id', 'sender_id', 'receiver_id', 'content', 'created_at']);
        
        header('Content-Type: application/json');
        echo json_encode([
            'users' => $users,
            'messages' => $messages,
            'query' => $query
        ]);
        exit;
    }
}

// Get current user data
$db->where("user_id", $current_user_id);
$user = $db->getOne("users");

// Get current user profile data
$db->where("user_id", $current_user_id);
$current_user_profile = $db->getOne("user_profile");

// Merge user data with profile data
$current_user = array_merge($user, $current_user_profile ?: []);

// Set default profile picture if not exists
$current_user['profile_picture'] = $current_user['profile_picture'] ?? 'assets/default-avatar.png';

// Get all conversations for current user
$conversations = $db->rawQuery("
    SELECT u.user_id, u.username, up.profile_picture, 
           MAX(m.created_at) as last_message_time,
           (SELECT COUNT(*) FROM messages 
            WHERE (sender_id = u.user_id AND receiver_id = ? AND is_read = 0)) as unread_count
    FROM users u
    LEFT JOIN user_profile up ON u.user_id = up.user_id
    JOIN messages m ON (m.sender_id = u.user_id OR m.receiver_id = u.user_id)
    WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.user_id != ?
    GROUP BY u.user_id
    ORDER BY last_message_time DESC
", [$current_user_id, $current_user_id, $current_user_id, $current_user_id]);

// Get the selected conversation (if any)
$selected_conversation = null;
$conversation_id = null;

if (isset($_GET['user_id']) || isset($_GET['conversation_id'])) {
    $conversation_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (int)$_GET['conversation_id'];
    
    // Validate the user exists
    $db->where('user_id', $conversation_id);
    $selected_conversation_user = $db->getOne('users');
    
    if ($selected_conversation_user) {
        $db->where('user_id', $conversation_id);
        $selected_conversation_profile = $db->getOne('user_profile');
        $selected_conversation = array_merge($selected_conversation_user, $selected_conversation_profile ?: []);
        $selected_conversation['profile_picture'] = $selected_conversation['profile_picture'] ?? 'assets/default-avatar.png';
        
        // Mark messages as read
        $db->where('receiver_id', $current_user_id);
        $db->where('sender_id', $conversation_id);
        $db->where('is_read', 0);
        $db->update('messages', ['is_read' => 1]);
        
        // Get messages for this conversation
        $db->where('(sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)', 
                  [$current_user_id, $conversation_id, $conversation_id, $current_user_id]);
        $db->orderBy('created_at', 'ASC');
        $messages = $db->get('messages');
    } else {
        header("Location: error.php?message=User+not+found");
        exit;
    }
}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $time_units = [
        'y' => ['value' => $diff->y, 'name' => 'year'],
        'm' => ['value' => $diff->m, 'name' => 'month'],
        'd' => ['value' => $diff->d, 'name' => 'day'],
        'h' => ['value' => $diff->h, 'name' => 'hour'],
        'i' => ['value' => $diff->i, 'name' => 'minute'],
        's' => ['value' => $diff->s, 'name' => 'second']
    ];

    $parts = [];
    foreach ($time_units as $unit) {
        if ($unit['value'] > 0) {
            $parts[] = $unit['value'] . ' ' . $unit['name'] . ($unit['value'] > 1 ? 's' : '');
        }
    }

    if (!$full) $parts = array_slice($parts, 0, 1);
    return $parts ? implode(', ', $parts) . ' ago' : 'just now';
}


 include_once 'includes/header1.php'; 

?>

   

    <div class="container mt-4">
        <div class="row">
            <!-- Conversations List -->
            <div class="col-lg-4">
                <div class="sidebar">
                    <h4 class="mb-4">Messages</h4>
                    
                    <!-- Search Bar -->
                    <div class="input-group mb-3 search-input-group">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search messages or people...">
                        <button class="btn btn-outline-secondary" type="button" id="searchButton">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>

                    <div class="list-group" id="conversationsList">
                        <?php if (empty($conversations)): ?>
                            <div class="list-group-item">
                                <p class="text-muted">No conversations yet</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($conversations as $conversation): ?>
                                <a href="?conversation_id=<?= $conversation['user_id'] ?>" 
                                   class="list-group-item list-group-item-action conversation-item <?= ($selected_conversation && $selected_conversation['user_id'] == $conversation['user_id']) ? 'active' : '' ?>">
                                    <div class="d-flex align-items-center">
                                        <div class="position-relative">
                                            <img src="<?= !empty($conversation['profile_picture']) ? htmlspecialchars($conversation['profile_picture']) : 'assets/default-avatar.png' ?>" 
                                                 class="profile-pic me-3" 
                                                 alt="<?= htmlspecialchars($conversation['username']) ?>">
                                            <?php if ($db->where('user_id', $conversation['user_id'])->getValue('sessions', 'is_online')): ?>
                                                <div class="online-status"></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="mb-1"><?= htmlspecialchars($conversation['username']) ?></h6>
                                                <small class="text-muted"><?= time_elapsed_string($conversation['last_message_time']) ?></small>
                                            </div>
                                            <?php 
                                            $db->where('(sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)', 
                                                     [$current_user_id, $conversation['user_id'], $conversation['user_id'], $current_user_id]);
                                            $db->orderBy('created_at', 'DESC');
                                            $last_message = $db->getOne('messages', ['content']);
                                            ?>
                                            <p class="mb-1 text-muted text-truncate" style="max-width: 200px;">
                                                <?= $last_message ? htmlspecialchars($last_message['content']) : 'No messages yet' ?>
                                            </p>
                                        </div>
                                        <?php if ($conversation['unread_count'] > 0): ?>
                                            <span class="badge bg-primary rounded-pill"><?= $conversation['unread_count'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Chat Window -->
            <div class="col-lg-8">
                <div class="sidebar">
                    <?php if ($selected_conversation): ?>
                        <div id="chatHeader" class="d-flex align-items-center mb-3">
                            <img src="<?= htmlspecialchars($selected_conversation['profile_picture']) ?>" 
                                 class="profile-pic me-3" id="chatProfilePic" 
                                 alt="Profile picture of <?= htmlspecialchars($selected_conversation['username']) ?>">
                            <h5 class="mb-0" id="chatName"><?= htmlspecialchars($selected_conversation['username']) ?></h5>
                            <?php if ($db->where('user_id', $selected_conversation['user_id'])->getValue('sessions', 'is_online')): ?>
                                <span class="badge bg-success ms-2">Online</span>
                            <?php else: ?>
                                <span class="badge bg-secondary ms-2">Offline</span>
                            <?php endif; ?>
                        </div>
                        <div class="chat-container" id="chatContainer">
                            <?php if (!empty($messages)): ?>
                                <?php foreach ($messages as $message): ?>
                                    <div class="message <?= $message['sender_id'] == $current_user_id ? 'sent' : 'received' ?>" id="msg-<?= $message['message_id'] ?>">
    <?php if ($message['sender_id'] != $current_user_id): ?>
        <img src="<?= htmlspecialchars($selected_conversation['profile_picture']) ?>" class="profile-pic me-2" alt="Profile picture">
    <?php endif; ?>
    <div class="message-content">
        <?= htmlspecialchars($message['content']) ?>
    </div>
    <div class="message-time">
        <?= date('h:i A', strtotime($message['created_at'])) ?>
        <?php if ($message['sender_id'] == $current_user_id && $message['is_read']): ?>
            <i class="fas fa-check-double ms-1 text-primary"></i>
        <?php elseif ($message['sender_id'] == $current_user_id): ?>
            <i class="fas fa-check ms-1 text-muted"></i>
        <?php endif; ?>
    </div>
</div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    No messages yet. Start the conversation!
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="input-group mt-3" id="messageInput">
                            <input type="text" class="form-control" id="messageText" 
                                   placeholder="Type a message..." aria-label="Type a message">
                            <button class="btn btn-primary" onclick="sendMessage(<?= $selected_conversation['user_id'] ?>)">Send</button>
                        </div>
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center" style="height: 400px;">
                            <div class="text-center">
                                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                <h5>Select a conversation to start chatting</h5>
                                <p class="text-muted">Or start a new conversation with a friend</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

     <!-- Search Results Modal -->
     <div class="modal fade" id="searchResultsModal" tabindex="-1" aria-labelledby="searchResultsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchResultsModalLabel">Search Results</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="searchResultsBody">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="<?= settings()['root']?>assets/js/lightbox-plus-jquery.min.js"></script>
    <script>
   // Global variables
let currentUserId = <?= $current_user_id ?>;
let selectedConversationId = <?= $selected_conversation ? $selected_conversation['user_id'] : 'null' ?>;
let lastMessageId = <?= !empty($messages) ? end($messages)['message_id'] : 0 ?>;
let searchResultsModal = new bootstrap.Modal('#searchResultsModal');
let searchTimeout;
let isCheckingMessages = false; // Add this flag

    // Function to send a message
    function sendMessage(receiverId) {
        const messageText = document.getElementById('messageText').value.trim();
        if (!messageText) return;

        // Optimistic UI update
        const chatContainer = document.getElementById('chatContainer');
        const now = new Date();
        const timeString = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        
        const messageElement = document.createElement('div');
        messageElement.className = 'message sent';
        messageElement.innerHTML = `
            <div class="message-content">${messageText}</div>
            <div class="message-time">${timeString} <i class="fas fa-check ms-1 text-muted"></i></div>
        `;
        chatContainer.appendChild(messageElement);
        chatContainer.scrollTop = chatContainer.scrollHeight;
        
        document.getElementById('messageText').value = '';
        
        // Send to server
        fetch('send_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                receiver_id: receiverId,
                content: messageText
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const checkIcon = messageElement.querySelector('.fa-check');
                if (checkIcon) checkIcon.className = 'fas fa-check-double ms-1 text-primary';
                lastMessageId = data.message_id;
            } else {
                alert('Failed to send message');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error sending message');
        });
    }

   // Function to check for new messages
function checkForNewMessages() {
    if (!selectedConversationId || isCheckingMessages) return;
    
    isCheckingMessages = true;
    
    fetch(`get_new_messages.php?conversation_id=${selectedConversationId}&last_id=${lastMessageId}`)
    .then(response => response.json())
    .then(data => {
        if (data.messages?.length > 0) {
            const chatContainer = document.getElementById('chatContainer');
            
            data.messages.forEach(message => {
                // Skip if message already exists (just in case)
                if (document.getElementById(`msg-${message.message_id}`)) {
                    return;
                }
                
                const timeString = new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                
                const messageElement = document.createElement('div');
                messageElement.className = 'message received';
                messageElement.id = `msg-${message.message_id}`;
                messageElement.innerHTML = `
                    <div class="message-content">${message.content}</div>
                    <div class="message-time">${timeString}</div>
                `;
                chatContainer.appendChild(messageElement);
                lastMessageId = message.message_id;
            });
            
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
        
        isCheckingMessages = false;
        setTimeout(checkForNewMessages, 3000);
    })
    .catch(error => {
        console.error('Error checking for messages:', error);
        isCheckingMessages = false;
        setTimeout(checkForNewMessages, 3000);
    });
}

    // Function to perform search
    function performSearch(query) {
        document.getElementById('searchResultsBody').innerHTML = `
            <div class="text-center py-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>`;
        
        searchResultsModal.show();
        
        fetch(`?search=1&q=${encodeURIComponent(query)}&user_id=${currentUserId}`)
        .then(response => response.json())
        .then(data => {
            let resultsHTML = '';
            
            if ((!data.users || data.users.length === 0) && (!data.messages || data.messages.length === 0)) {
                resultsHTML = '<div class="alert alert-info">No results found</div>';
            } else {
                if (data.users && data.users.length > 0) {
                    resultsHTML += '<h6 class="mb-3">People</h6><div class="list-group mb-4">';
                    data.users.forEach(user => {
                        resultsHTML += `
                            <a href="?conversation_id=${user.user_id}" class="list-group-item list-group-item-action search-result-item">
                                <div class="d-flex align-items-center">
                                    <img src="assets/default-avatar.png" class="rounded-circle me-3" width="40" height="40" alt="${user.username}">
                                    <div>
                                        <h6 class="mb-0">${user.username}</h6>
                                    </div>
                                </div>
                            </a>`;
                    });
                    resultsHTML += '</div>';
                }
                
                if (data.messages && data.messages.length > 0) {
                    resultsHTML += '<h6 class="mb-3">Messages</h6><div class="list-group">';
                    data.messages.forEach(message => {
                        const otherUserId = message.sender_id == currentUserId ? message.receiver_id : message.sender_id;
                        const timeString = new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        const content = message.content.replace(
                            new RegExp(data.query, 'gi'), 
                            match => `<mark>${match}</mark>`
                        );
                        
                        resultsHTML += `
                            <a href="?conversation_id=${otherUserId}#msg-${message.message_id}" class="list-group-item list-group-item-action search-result-item">
                                <div class="d-flex align-items-center mb-2">
                                    <small class="text-muted">${timeString}</small>
                                </div>
                                <p class="mb-0">${content}</p>
                            </a>`;
                    });
                    resultsHTML += '</div>';
                }
            }
            
            document.getElementById('searchResultsBody').innerHTML = resultsHTML;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('searchResultsBody').innerHTML = `
                <div class="alert alert-danger">Error loading search results</div>`;
        });
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize chat if in conversation
        if (selectedConversationId) {
            const chatContainer = document.getElementById('chatContainer');
            if (chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;
            document.getElementById('messageText')?.focus();
            checkForNewMessages();
        }

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const searchButton = document.getElementById('searchButton');
        
        searchInput?.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => performSearch(query), 300);
            }
        });

        searchButton?.addEventListener('click', function() {
            const query = searchInput.value.trim();
            if (query.length >= 2) performSearch(query);
        });

        searchInput?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = searchInput.value.trim();
                if (query.length >= 2) performSearch(query);
            }
        });

        // Handle sending message on Enter
        document.getElementById('messageText')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage(selectedConversationId);
            }
        });
    });
    </script>
</body>
</html>