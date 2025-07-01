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

// Get current user data
$db->where("user_id", $current_user_id);
$user = $db->getOne("users");

// Get current user profile data
$db->where("user_id", $current_user_id);
$current_user_profile = $db->getOne("user_profile");

// Merge user data with profile data
$current_user = array_merge($user, $current_user_profile ?: []);

// Set default profile picture if not exists
if (empty($current_user['profile_picture'])) {
    $current_user['profile_picture'] = 'assets/default-avatar.png';
}

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
if (isset($_GET['conversation_id'])) {
    $db->where('user_id', $_GET['conversation_id']);
    $selected_conversation_user = $db->getOne('users');
    
    $db->where('user_id', $_GET['conversation_id']);
    $selected_conversation_profile = $db->getOne('user_profile');
    
    if ($selected_conversation_user) {
        $selected_conversation = array_merge($selected_conversation_user, $selected_conversation_profile ?: []);
        
        // Mark messages as read
        $db->where('receiver_id', $current_user_id);
        $db->where('sender_id', $_GET['conversation_id']);
        $db->where('is_read', 0);
        $db->update('messages', ['is_read' => 1]);
        
        // Get messages for this conversation
        $db->where('(sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)', 
                  [$current_user_id, $_GET['conversation_id'], $_GET['conversation_id'], $current_user_id]);
        $db->orderBy('created_at', 'ASC');
        $messages = $db->get('messages');
    }
}

include_once 'includes/header1.php';
?>

<!-- Main Content -->
<div class="container mt-4">
    <div class="row">
        <!-- Conversations List -->
        <div class="col-lg-4">
            <div class="sidebar">
                <h4 class="mb-4">Messages</h4>
                <div class="list-group">
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
                                             alt="Profile picture of <?= htmlspecialchars($conversation['username']) ?>">
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
                                        // Get last message preview
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
                        <img src="<?= !empty($selected_conversation['profile_picture']) ? htmlspecialchars($selected_conversation['profile_picture']) : 'assets/default-avatar.png' ?>" 
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
                                <div class="message <?= $message['sender_id'] == $current_user_id ? 'sent' : 'received' ?>">
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

<script>
// Function to send a message
function sendMessage(receiverId) {
    const messageText = document.getElementById('messageText').value.trim();
    if (!messageText) return;

    // Add message to UI immediately (optimistic update)
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
    
    // Send message to server
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
            // Update message status if needed
            const checkIcon = messageElement.querySelector('.fa-check');
            if (checkIcon) {
                checkIcon.className = 'fas fa-check-double ms-1 text-primary';
            }
        } else {
            // Handle error
            alert('Failed to send message');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error sending message');
    });
}

// Function to check for new messages (polling)
function checkForNewMessages() {
    if (!<?= $selected_conversation ? $selected_conversation['user_id'] : 'null' ?>) return;
    
    const lastMessageId = <?= !empty($messages) ? end($messages)['message_id'] : 0 ?>;
    
    fetch('get_new_messages.php?conversation_id=<?= $selected_conversation ? $selected_conversation['user_id'] : '' ?>&last_id=' + lastMessageId)
    .then(response => response.json())
    .then(data => {
        if (data.messages && data.messages.length > 0) {
            const chatContainer = document.getElementById('chatContainer');
            
            data.messages.forEach(message => {
                const timeString = new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                
                const messageElement = document.createElement('div');
                messageElement.className = 'message received';
                messageElement.innerHTML = `
                    <div class="message-content">${message.content}</div>
                    <div class="message-time">${timeString}</div>
                `;
                chatContainer.appendChild(messageElement);
            });
            
            chatContainer.scrollTop = chatContainer.scrollHeight;
            
            // Mark messages as read
            if (data.messages.some(m => !m.is_read)) {
                fetch('mark_messages_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        conversation_id: <?= $selected_conversation ? $selected_conversation['user_id'] : 'null' ?>
                    })
                });
            }
        }
        
        // Check again after delay
        setTimeout(checkForNewMessages, 3000);
    })
    .catch(error => {
        console.error('Error checking for messages:', error);
        setTimeout(checkForNewMessages, 3000);
    });
}

// Start checking for new messages if in a conversation
<?php if ($selected_conversation): ?>
    document.addEventListener('DOMContentLoaded', function() {
        // Scroll to bottom of chat
        const chatContainer = document.getElementById('chatContainer');
        chatContainer.scrollTop = chatContainer.scrollHeight;
        
        // Start polling for new messages
        checkForNewMessages();
        
        // Focus message input
        document.getElementById('messageText').focus();
    });
<?php endif; ?>

// Handle pressing Enter to send message
document.getElementById('messageText')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage(<?= $selected_conversation ? $selected_conversation['user_id'] : 'null' ?>);
    }
});
</script>

<?php
include_once 'includes/footer1.php';
?>