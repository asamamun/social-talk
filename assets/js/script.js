// socialTalk.js
(function () {
    // socialTalk object to encapsulate all functionality
    const socialTalk = {
        // === Post-Related Functions ===
        // Redirect to create_post.php for post creation
        openCreatePost: function () {
            window.location.href = 'create_post.php';
        },

        // Redirect to create_post.php for image upload
        openImageUpload: function () {
            window.location.href = 'create_post.php';
            // Future: Add query param to pre-open file input, e.g., 'create_post.php?type=image'
        },

        // Toggle like on a post
        toggleLike: function (button) {
            button.classList.toggle('liked');
            const likeText = button.classList.contains('liked')
                ? '<i class="fas fa-thumbs-up me-2"></i>Liked'
                : '<i class="fas fa-thumbs-up me-2"></i>Like';
            button.innerHTML = likeText;
            // Future: Send like status to backend
        },

        // Focus comment input for a post
        focusComment: function (button) {
            const postCard = button.closest('.post-card');
            const commentInput = postCard?.querySelector('.comment-section input');
            if (commentInput) {
                commentInput.focus();
            } else {
                console.error('Comment input not found');
            }
        },

        // Share a post
        sharePost: function () {
            alert('Post shared successfully!');
            // Future: Implement sharing logic (e.g., copy link or share to feed)
        },

        // Toggle comments section visibility
        toggleComments: function (btn) {
            const card = btn.closest('.post-card');
            const comments = card.querySelector('.comment-section');
            comments.classList.toggle('hidden');
        },

        // Add a comment to a post
        addComment: function () {
            const comment = prompt('Enter your comment:');
            if (comment) {
                alert(`Comment added: ${socialTalk.sanitizeInput(comment)}`);
                // Future: Append comment to post via backend
            }
        },

        // Edit a post
        editPost: function (link) {
            const postContent = link.closest('.card-body').querySelector('p').textContent;
            const newContent = prompt('Edit post:', postContent);
            if (newContent) {
                link.closest('.card-body').querySelector('p').textContent = socialTalk.sanitizeInput(newContent);
                // Future: Send updated content to backend
            }
        },

        // Delete a post
        deletePost: function (link) {
            if (confirm('Are you sure you want to delete this post?')) {
                link.closest('.post-card').remove();
                // Future: Send delete request to backend
            }
        },

        // === Profile-Related Functions ===
        // Show profile page (placeholder)
        showProfile: function () {
            window.location.href = 'user-profile.php';
        },

        // Show settings page (placeholder)
        showSettings: function () {
            window.location.href = 'settings.php';
        },

        // Logout user
        logout: function () {
            if (confirm('Are you sure you want to log out?')) {
                console.log('Logging out...');
                window.location.href = 'login.php'; // Adjust to your login page
            }
        },

        // Update cover photo (placeholder)
        updateCoverPhoto: function () {
            alert('Feature not implemented: Upload a new cover photo.');
            // Future: Implement file upload logic
        },

        // Edit profile (placeholder)
        editProfile: function () {
            window.location.href = 'edit-about.php';
        },

        // === Friend-Related Functions ===
        // Send friend request
        sendFriendRequest: function (button) {
            button.innerHTML = '<i class="fas fa-user-clock me-1"></i> Request Sent';
            button.classList.remove('btn-primary');
            button.classList.add('btn-secondary');
            button.disabled = true;
            // Future: Send request to backend
        },

        // Accept friend request
        acceptFriendRequest: function (btn) {
            const card = btn.closest('.friend-request-card');
            if (card) {
                card.remove();
                alert('Friend request accepted!');
                socialTalk.checkNoRequests();
                // Future: Send acceptance to backend
            }
        },

        // Decline friend request
        declineFriendRequest: function (btn) {
            const card = btn.closest('.friend-request-card');
            if (card) {
                card.remove();
                alert('Friend request declined.');
                socialTalk.checkNoRequests();
                // Future: Send decline to backend
            }
        },

        // Check if there are no friend requests
        checkNoRequests: function () {
            const container = document.getElementById('friendRequestsContainer');
            const noRequestsMessage = document.getElementById('noRequestsMessage');
            if (container && noRequestsMessage && container.children.length === 0) {
                noRequestsMessage.classList.remove('hidden');
            }
        },

        // === Notification-Related Functions ===
        // Handle notification click
        handleNotificationClick: function (type, id, element) {
            element.classList.remove('unread');
            socialTalk.checkNoNotifications();
            if (type === 'post') {
                alert(`Navigating to post ID ${id}`);
                // Future: window.location.href = `post.php?id=${id}`;
            } else if (type === 'friend-request') {
                alert(`Navigating to friend request for ${id}`);
                window.location.href = 'friend-request.php';
            } else if (type === 'profile') {
                alert(`Navigating to profile of ${id}`);
                // Future: window.location.href = `user-profile.php?user=${id}`;
            }
        },

        // Mark all notifications as read
        markAllAsRead: function () {
            const notifications = document.querySelectorAll('.notification-item.unread');
            notifications.forEach((notification) => {
                notification.classList.remove('unread');
            });
            socialTalk.checkNoNotifications();
        },

        // Clear all notifications
        clearAllNotifications: function () {
            const container = document.getElementById('notificationsContainer');
            const notifications = container.querySelectorAll('.notification-item');
            notifications.forEach((notification) => {
                notification.classList.add('remove');
                setTimeout(() => notification.remove(), 300);
            });
            setTimeout(socialTalk.checkNoNotifications, 300);
        },

        // Check if there are no notifications
        checkNoNotifications: function () {
            const container = document.getElementById('notificationsContainer');
            const noNotificationsMessage = document.getElementById('noNotificationsMessage');
            if (container && noNotificationsMessage && container.children.length === 0) {
                noNotificationsMessage.classList.remove('hidden');
                noNotificationsMessage.focus();
            }
        },

        // === Chat-Related Functions ===
        // Open chat with a friend
        openChat: function (name, element) {
            document.querySelectorAll('.conversation-item').forEach((item) => {
                item.classList.remove('active');
            });
            element.classList.add('active');

            const chatHeader = document.getElementById('chatHeader');
            const chatName = document.getElementById('chatName');
            const chatProfilePic = document.getElementById('chatProfilePic');
            const chatContainer = document.getElementById('chatContainer');
            const messageInput = document.getElementById('messageInput');

            if (chatName && chatProfilePic && chatHeader && messageInput) {
                chatName.textContent = name;
                chatProfilePic.src = element.querySelector('.profile-pic').src;
                chatProfilePic.alt = `Profile picture of ${name}`;
                chatHeader.classList.remove('hidden');
                messageInput.classList.remove('hidden');
            }

            if (chatContainer) {
                chatContainer.innerHTML = '';
                const sampleMessages = {
                    'Sarah Johnson': [
                        { sender: 'Sarah Johnson', text: 'Hey! How was your weekend?', time: '2:30 PM' },
                        { sender: 'You', text: 'It was great, thanks for asking! How about yours?', time: '2:32 PM' },
                    ],
                    'Mike Chen': [
                        { sender: 'Mike Chen', text: 'Thanks for the advice!', time: 'Yesterday' },
                        { sender: 'You', text: 'No problem! Glad I could help.', time: 'Yesterday' },
                    ],
                    'Emma Wilson': [
                        { sender: 'Emma Wilson', text: "Let's catch up soon!", time: 'Yesterday' },
                        { sender: 'You', text: 'Definitely, how about this weekend?', time: 'Yesterday' },
                    ],
                };

                (sampleMessages[name] || []).forEach((msg) => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `message-bubble ${msg.sender === 'You' ? 'message-sent' : 'message-received'}`;
                    messageDiv.innerHTML = `
                        <p class="mb-0">${msg.text}</p>
                        <small class="text-muted">${msg.time}</small>
                    `;
                    chatContainer.appendChild(messageDiv);
                });

                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        },

        // Send a chat message
        sendMessage: function () {
            const input = document.querySelector('#messageInput input');
            const chatContainer = document.getElementById('chatContainer');
            if (!input || !chatContainer) return;

            const text = socialTalk.sanitizeInput(input.value.trim());
            if (!text) return;

            const messageDiv = document.createElement('div');
            messageDiv.className = 'message-bubble message-sent';
            messageDiv.innerHTML = `
                <p class="mb-0">${text}</p>
                <small class="text-muted">Just now</small>
            `;
            chatContainer.appendChild(messageDiv);
            chatContainer.scrollTop = chatContainer.scrollHeight;
            input.value = '';
        },

        // === Edit About Functions ===
        // Update profile completion
        updateProfileCompletion: function () {
            const fields = [
                'firstName', 'lastName', 'dateOfBirth', 'phoneNumber',
                'bio', 'addressLine1', 'city', 'state', 'postalCode', 'country',
            ];

            let filledFields = 0;
            let totalFields = fields.length + 4; // +2 for gender, blood group, +2 for photos

            fields.forEach((fieldId) => {
                if (document.getElementById(fieldId)?.value.trim()) {
                    filledFields++;
                }
            });

            if (document.getElementById('genderInput')?.value) filledFields++;
            if (document.getElementById('bloodGroupInput')?.value) filledFields++;
            if (
                document.getElementById('profilePicInput')?.files.length > 0 ||
                document.getElementById('profilePicPreview')?.src !==
                    'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face'
            )
                filledFields++;
            if (
                document.getElementById('coverPhotoInput')?.files.length > 0 ||
                document.getElementById('coverPhotoPreview')?.src !==
                    'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&h=300&fit=crop'
            )
                filledFields++;

            const percentage = Math.round((filledFields / totalFields) * 100);
            const completionPercent = document.getElementById('completionPercent');
            const completionBar = document.getElementById('completionBar');

            if (completionPercent && completionBar) {
                completionPercent.textContent = percentage + '%';
                completionBar.style.width = percentage + '%';

                if (percentage < 50) {
                    completionBar.className = 'progress-bar bg-danger';
                } else if (percentage < 80) {
                    completionBar.className = 'progress-bar bg-warning';
                } else {
                    completionBar.className = 'progress-bar bg-success';
                }
            }
        },

        // Remove cover photo
        removeCoverPhoto: function () {
            if (confirm('Are you sure you want to remove your cover photo?')) {
                const coverPhotoPreview = document.getElementById('coverPhotoPreview');
                const coverPhotoInput = document.getElementById('coverPhotoInput');
                if (coverPhotoPreview && coverPhotoInput) {
                    coverPhotoPreview.src =
                        'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&h=300&fit=crop';
                    coverPhotoInput.value = '';
                    socialTalk.updateProfileCompletion();
                }
            }
        },

        // === Utility Functions ===
        // Sanitize input to prevent XSS
        sanitizeInput: function (input) {
            const div = document.createElement('div');
            div.textContent = input;
            return div.innerHTML.replace(/[<>&]/g, '');
        },
    };

    // === Event Listeners ===
    // Profile navigation
    document.querySelectorAll('.profile-nav .nav-link').forEach((link) => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            const section = this.getAttribute('data-section');

            if (href.startsWith('#') && section) {
                e.preventDefault();
                document.querySelectorAll('.profile-nav .nav-link').forEach((navLink) => {
                    navLink.classList.remove('active');
                });
                this.classList.add('active');

                document.querySelectorAll('#content-area > div').forEach((sectionDiv) => {
                    sectionDiv.classList.add('d-none');
                });
                document.querySelector(`#${section}-section`)?.classList.remove('d-none');
            }
        });
    });

    // Set active navigation link based on current page
    const currentPage = window.location.pathname.split('/').pop();
    document.querySelectorAll('.profile-nav .nav-link').forEach((link) => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });

    // Friend card click handler
    document.querySelectorAll('.friend-card').forEach((card) => {
        card.addEventListener('click', function (e) {
            if (e.target.tagName !== 'BUTTON') {
                console.log('Friend card clicked:', this.querySelector('h6')?.textContent);
                // Future: Navigate to friend's profile
            }
        });
    });

    // Friend search input
    const searchInput = document.querySelector('.search-card .form-control');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            console.log('Search query:', this.value);
            // Future: Filter or fetch search results
        });
    }

    // Photo grid click handler
    document.querySelectorAll('.photo-grid img').forEach((img) => {
        img.addEventListener('click', function () {
            console.log('Photo clicked:', this.src);
            // Future: Open modal or lightbox
        });
    });

    // Dark mode toggle
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', function () {
            document.body.classList.toggle('dark-mode', this.checked);
        });
    }

    // Cover photo input
    const coverPhotoInput = document.getElementById('coverPhotoInput');
    if (coverPhotoInput) {
        coverPhotoInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('Cover photo must be less than 5MB');
                    return;
                }
                if (!file.type.match(/^image\/(jpeg|jpg|png|gif)$/)) {
                    alert('Please select a valid image file (JPG, PNG, or GIF)');
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('coverPhotoPreview').src = e.target.result;
                    socialTalk.updateProfileCompletion();
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Profile picture input
    const profilePicInput = document.getElementById('profilePicInput');
    if (profilePicInput) {
        profilePicInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('Profile picture must be less than 2MB');
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('profilePicPreview').src = e.target.result;
                    socialTalk.updateProfileCompletion();
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Blood group selection
    document.querySelectorAll('.blood-group-option').forEach((option) => {
        option.addEventListener('click', function () {
            document.querySelectorAll('.blood-group-option').forEach((opt) => opt.classList.remove('selected'));
            this.classList.add('selected');
            document.getElementById('bloodGroupInput').value = this.dataset.value;
            socialTalk.updateProfileCompletion();
        });
    });

  
    // Bio character counter
    const bioInput = document.getElementById('bio');
    if (bioInput) {
        bioInput.addEventListener('input', function () {
            const count = this.value.length;
            const bioCount = document.getElementById('bioCount');
            if (bioCount) {
                bioCount.textContent = count;
                bioCount.style.color = count > 450 ? '#dc3545' : '#6c757d';
            }
            socialTalk.updateProfileCompletion();
        });
    }

    // Profile form submission start
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const completion = parseInt(document.getElementById('completionPercent')?.textContent) || 0;
            if (completion < 100) {
                alert('Please complete your profile before submitting.');
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Saving...';
            }

            const formData = new FormData(this);
/*             console.log(formData.getAll('bio'));
            return; */
            fetch('update_profile.php', {
                method: 'POST',
                body: formData,//all form values including files
            })
                .then((response) => response.json())
                .then((data) => {
                    console.log(data);
                    if (data.status === 'success') {
                       swal.fire({
                           icon: 'success',
                           title: data.message,
                           showConfirmButton: false,
                           timer: 1500
                       })
                    } else {
                        // alert(data || 'Something went wrong. Please try again.');
                        swal.fire({
                            icon: 'error',
                            title: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        })
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Save Changes';
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    // alert('Network error. Please try again later.');
                    swal.fire({
                        icon: 'error',
                        title: 'Network error. Please try again later.',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Save Changes';
                    }
                });
        });
    }
    // Profile form submission end

    // Initialize tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
        new bootstrap.Tooltip(el);
    });

    // Expose socialTalk to global scope
    window.socialTalk = socialTalk;
})();





document.getElementById('media').addEventListener('change', function (event) {
    const selectedImagesContainer = document.getElementById('selectedImages');
    selectedImagesContainer.innerHTML = ''; // Clear previous previews

    const files = event.target.files;

    if (files.length === 0) return;

    Array.from(files).forEach(file => {
        if (!file.type.startsWith('image/')) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'img-thumbnail me-2 mb-2'; // Bootstrap styling
            img.style.maxWidth = '100px';
            img.style.maxHeight = '100px';
            selectedImagesContainer.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
});



//post actions for index.php
/* function toggleLike(postId) {
    fetch('apis/like.php', {
        method: 'POST',
        body: JSON.stringify({ post_id: postId, action: 'like' }),      
    })
    .then(response => response.json())
    .then(data => {
        console.log(data);
        const likeBtn = document.querySelector(`[data-post-id="${postId}"] .like-btn`);
        const likeCount = likeBtn.querySelector('.like-count');
        
        if (data.status === 'liked') {
            likeBtn.classList.add('liked');
            likeCount.textContent = parseInt(likeCount.textContent) + 1;
        } else {
            likeBtn.classList.remove('liked');
            likeCount.textContent = Math.max(0, parseInt(likeCount.textContent) - 1);
        }
        
        // Update the summary text
        const summaryText = document.querySelector(`[data-post-id="${postId}"] .text-muted`);
        const newLikeCount = likeCount.textContent;
        const commentCount = document.querySelector(`[data-post-id="${postId}"] .comment-count`).textContent;
        summaryText.textContent = `${newLikeCount} likes · ${commentCount} comments`;
    })
    .catch(error => console.error('Error:', error));
} */
function toggleLike(postId) {
    // alert(postId);
    fetch('apis/like.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest' // Add this header
        },
        body: JSON.stringify({ post_id: postId, action: 'like' }),
    })
    .then(response => {
        // Check if response is OK        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log(data);
        if (!data.success) {
            console.error('Server error:', data.message);
            return;
        }
        const likeBtn = document.querySelector(`[data-post-id="${postId}"] .like-btn`);
        const likeCount = likeBtn.querySelector('.like-count');
        console.log(likeBtn);
        console.log(likeCount);

        if (data.status === 'liked') {
            likeBtn.classList.add('liked');
            likeCount.textContent = parseInt(likeCount.textContent) + 1;
        } else {
            likeBtn.classList.remove('liked');
            likeCount.textContent = Math.max(0, parseInt(likeCount.textContent) - 1);
        }

        // Update summary text
        const summaryText = document.querySelector(`[data-post-id="${postId}"] .likecomment`);
        const newLikeCount = likeCount.textContent;
        const commentCount = document.querySelector(`[data-post-id="${postId}"] .comment-count`).textContent;
        summaryText.textContent = `${newLikeCount} likes · ${commentCount} comments`;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to process like. Please try again.');
    });
}

function toggleComments(postId) {
    const commentsSection = document.getElementById(`comments-${postId}`);
    if (commentsSection.style.display === 'none') {
        commentsSection.style.display = 'block';
        loadComments(postId);
    } else {
        commentsSection.style.display = 'none';
    }
}

function handleCommentSubmit(event, postId) {
    if (event.key === 'Enter') {
        const comment = event.target.value.trim();
        if (comment) {
            addComment(postId, comment);
            event.target.value = '';
        }
    }
}

function addComment(postId, comment) {
    // console.log(postId, comment);
    fetch('apis/comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add_comment&post_id=${postId}&comment=${encodeURIComponent(comment)}`
    })
    .then(response => response.json())
    .then(data => {
        console.log(data);
        if (data.status === 'success') {
            loadComments(postId);
            // Update comment count
            const commentCount = document.querySelector(`[data-post-id="${postId}"] .comment-count`);
            const currentCount = parseInt(commentCount.textContent);
            commentCount.textContent = currentCount + 1;
            
            // Update summary text
            const summaryText = document.querySelector(`[data-post-id="${postId}"] .likecomment`);
            const likeCount = document.querySelector(`[data-post-id="${postId}"] .like-count`).textContent;
            summaryText.textContent = `${likeCount} likes · ${currentCount + 1} comments`;
        }
    })
    .catch(error => console.error('Error:', error));
}

function loadComments(postId) {
    fetch('apis/comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=load_comments&post_id=${postId}`
    })
    .then(response => response.json())
    .then(data => {
        console.log(data);
        if (data.status === 'success') {
            const commentsList = document.getElementById(`comments-list-${postId}`);
            commentsList.innerHTML = '';
            
            data.comments.forEach(comment => {
                const commentHtml = `
                    <div class="comment-item d-flex">
                        <img src="${comment.profile_picture || 'assets/default-avatar.png'}" 
                             alt="Profile" class="profile-img me-2">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <strong class="small">${comment.username}</strong>
                                <small class="text-muted">${timeAgoJS(comment.created_at)}</small>
                            </div>
                            <p class="mb-0 small">${comment.content}</p>
                        </div>
                    </div>
                `;
                commentsList.innerHTML += commentHtml;
            });
        }
    })
    .catch(error => console.error('Error:', error));
}

function timeAgoJS(datetime) {
    const time = Math.floor((new Date() - new Date(datetime)) / 1000);
    
    if (time < 60) return 'just now';
    if (time < 3600) return Math.floor(time/60) + ' minutes ago';
    if (time < 86400) return Math.floor(time/3600) + ' hours ago';
    if (time < 2592000) return Math.floor(time/86400) + ' days ago';
    if (time < 31536000) return Math.floor(time/2592000) + ' months ago';
    return Math.floor(time/31536000) + ' years ago';
}

//post actions for index.php end
// Handle privacy dropdown selection
document.querySelectorAll('.dropdown-item[data-value]').forEach(item => {
    item.addEventListener('click', function() {
        const value = this.getAttribute('data-value');
        const text = this.textContent.trim();
        const icon = this.querySelector('i').outerHTML;
        
        document.getElementById('selectedPrivacy').value = value;
        document.getElementById('privacyDropdown').innerHTML = `${icon} ${text}`;
        
        // Update active state
        document.querySelectorAll('.dropdown-item').forEach(i => i.classList.remove('active'));
        this.classList.add('active');
    });
});
// Handle privacy dropdown selection
// edit profile js start
