// socialNet.js
(function () {
    // SocialNet object to encapsulate all functionality
    const socialNet = {
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
                alert(`Comment added: ${socialNet.sanitizeInput(comment)}`);
                // Future: Append comment to post via backend
            }
        },

        // Edit a post
        editPost: function (link) {
            const postContent = link.closest('.card-body').querySelector('p').textContent;
            const newContent = prompt('Edit post:', postContent);
            if (newContent) {
                link.closest('.card-body').querySelector('p').textContent = socialNet.sanitizeInput(newContent);
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
                socialNet.checkNoRequests();
                // Future: Send acceptance to backend
            }
        },

        // Decline friend request
        declineFriendRequest: function (btn) {
            const card = btn.closest('.friend-request-card');
            if (card) {
                card.remove();
                alert('Friend request declined.');
                socialNet.checkNoRequests();
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
            socialNet.checkNoNotifications();
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
            socialNet.checkNoNotifications();
        },

        // Clear all notifications
        clearAllNotifications: function () {
            const container = document.getElementById('notificationsContainer');
            const notifications = container.querySelectorAll('.notification-item');
            notifications.forEach((notification) => {
                notification.classList.add('remove');
                setTimeout(() => notification.remove(), 300);
            });
            setTimeout(socialNet.checkNoNotifications, 300);
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

            const text = socialNet.sanitizeInput(input.value.trim());
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
                    socialNet.updateProfileCompletion();
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
                    socialNet.updateProfileCompletion();
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
                    socialNet.updateProfileCompletion();
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
            socialNet.updateProfileCompletion();
        });
    });

    // Gender selection
    document.querySelectorAll('.gender-option').forEach((option) => {
        option.addEventListener('click', function () {
            document.querySelectorAll('.gender-option').forEach((opt) => opt.classList.remove('selected'));
            this.classList.add('selected');
            document.getElementById('genderInput').value = this.dataset.value;
            socialNet.updateProfileCompletion();
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
            socialNet.updateProfileCompletion();
        });
    }

    // Profile form submission
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
            fetch('update_profile.php', {
                method: 'POST',
                body: formData,
            })
                .then((response) => response.text())
                .then((data) => {
                    if (data === 'success') {
                        alert('Profile updated successfully!');
                    } else {
                        alert(data || 'Something went wrong. Please try again.');
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Save Changes';
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('Network error. Please try again later.');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Save Changes';
                    }
                });
        });
    }

    // Initialize tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
        new bootstrap.Tooltip(el);
    });

    // Expose socialNet to global scope
    window.socialNet = socialNet;
})();