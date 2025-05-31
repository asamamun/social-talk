// Sidebar Toggle
document.getElementById('sidebarCollapse').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.toggle('active');
    document.querySelector('.content').classList.toggle('active');
});

// Section Navigation and Dynamic Loading
const sectionContainer = document.getElementById('section-container');
let currentCharts = []; // To keep track of charts for destruction

function loadSection(section) {
    fetch(`${section}.html`)
        .then(response => response.text())
        .then(html => {
            sectionContainer.innerHTML = html;

            // Destroy existing charts to avoid memory leaks
            currentCharts.forEach(chart => chart.destroy());
            currentCharts = [];

            // Initialize charts based on the section
            if (section === 'dashboard') {
                currentCharts.push(
                    initActivityChart(document.getElementById('activityChart')),
                    initUserDistributionChart(document.getElementById('userDistributionChart'))
                );
            } else if (section === 'analytics') {
                currentCharts.push(
                    initEngagementChart(document.getElementById('engagementChart')),
                    initContentTypeChart(document.getElementById('contentTypeChart'))
                );
            } else if (section === 'users') {
                // Load the Add User Modal dynamically
                fetch('add-user-modal.html')
                    .then(response => response.text())
                    .then(modalHtml => {
                        // Append the modal to the body
                        document.body.insertAdjacentHTML('beforeend', modalHtml);

                        // Re-attach event listeners for the Add User Form
                        const addUserForm = document.getElementById('addUserForm');
                        if (addUserForm) {
                            addUserForm.addEventListener('submit', function(e) {
                                e.preventDefault();
                                const username = document.getElementById('username').value;
                                const email = document.getElementById('email').value;
                                const password = document.getElementById('password').value;
                                const role = document.getElementById('role').value;

                                alert(`Creating user: ${username} (${email}) with role ${role}`);
                                // Future: Send AJAX request to create user

                                // Clear form and close modal
                                this.reset();
                                const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
                                modal.hide();
                            });
                        }
                    })
                    .catch(error => console.error('Error loading add-user-modal:', error));
            }
            // No additional initialization needed for reports, posts, or settings sections
        })
        .catch(error => console.error(`Error loading ${section}:`, error));
}

document.querySelectorAll('.sidebar ul li a').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const section = this.getAttribute('data-section');

        // Update active sidebar link
        document.querySelectorAll('.sidebar li').forEach(item => item.classList.remove('active'));
        this.parentElement.classList.add('active');

        // Load the section
        loadSection(section);
    });
});

// Load the default section (dashboard) on page load
loadSection('dashboard');

// User Actions
function editUser(userId) {
    alert(`Editing user ID: ${userId}`);
    // Future: Open a modal with user details
}

function banUser(userId) {
    if (confirm(`Ban user ID ${userId}?`)) {
        alert(`User ID ${userId} banned`);
        // Future: Send AJAX request to update users.status = 'banned'
    }
}

function unbanUser(userId) {
    if (confirm(`Unban user ID ${userId}?`)) {
        alert(`User ID ${userId} unbanned`);
        // Future: Send AJAX request to update users.status = 'active'
    }
}

function deleteUser(userId) {
    if (confirm(`Delete user ID ${userId}?`)) {
        alert(`User ID ${userId} deleted`);
        // Future: Send AJAX request to update users.status = 'deleted'
    }
}

function restoreUser(userId) {
    if (confirm(`Restore user ID ${userId}?`)) {
        alert(`User ID ${userId} restored`);
        // Future: Send AJAX request to update users.status = 'active'
    }
}

// Post Actions
function viewPost(postId) {
    alert(`Viewing post ID: ${postId}`);
    // Future: Redirect to post view or open modal
}

function deletePost(postId) {
    if (confirm(`Delete post ID ${postId}?`)) {
        alert(`Post ID ${postId} deleted`);
        // Future: Send AJAX request to delete post
    }
}

function restorePost(postId) {
    if (confirm(`Restore post ID ${postId}?`)) {
        alert(`Post ID ${postId} restored`);
        // Future: Send AJAX request to restore post
    }
}

// Report Actions
function reviewReport(reportId) {
    alert(`Reviewing report ID: ${reportId}`);
    // Future: Open a modal or redirect to a detailed report review page
}

function approveReport(reportId) {
    if (confirm(`Approve report ID ${reportId} and take action?`)) {
        alert(`Report ID ${reportId} approved`);
        // Future: Send AJAX request to update report status and remove reported content
    }
}

function rejectReport(reportId) {
    if (confirm(`Reject report ID ${reportId}?`)) {
        alert(`Report ID ${reportId} rejected`);
        // Future: Send AJAX request to update report status
    }
}

function reopenReport(reportId) {
    if (confirm(`Reopen report ID ${reportId}?`)) {
        alert(`Report ID ${reportId} reopened`);
        // Future: Send AJAX request to update report status
    }
}

// Logout Handler
document.getElementById('logout').addEventListener('click', function(e) {
    e.preventDefault();
    if (confirm('Are you sure you want to log out?')) {
        alert('Logging out...');
        // Future: Clear session and redirect to login page
        window.location.href = 'index.html';
    }
});