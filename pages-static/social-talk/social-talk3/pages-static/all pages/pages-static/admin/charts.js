// Charts for Dashboard Section
function initActivityChart(canvas) {
    return new Chart(canvas, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Active Users',
                data: [120, 190, 300, 250, 400, 320, 350],
                borderColor: '#1877f2',
                backgroundColor: 'rgba(24, 119, 242, 0.2)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Posts Created',
                data: [80, 100, 150, 120, 180, 140, 160],
                borderColor: '#42b883',
                backgroundColor: 'rgba(66, 184, 131, 0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

function initUserDistributionChart(canvas) {
    return new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Banned', 'Deleted', 'Inactive'],
            datasets: [{
                data: [70, 10, 5, 15],
                backgroundColor: ['#1877f2', '#dc3545', '#6c757d', '#ffc107']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

// Charts for Analytics Section
function initEngagementChart(canvas) {
    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Likes',
                data: [200, 300, 450, 400, 500, 420, 380],
                backgroundColor: '#1877f2'
            }, {
                label: 'Comments',
                data: [100, 150, 200, 180, 220, 190, 170],
                backgroundColor: '#42b883'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

function initContentTypeChart(canvas) {
    return new Chart(canvas, {
        type: 'pie',
        data: {
            labels: ['Text', 'Images', 'Videos', 'Links'],
            datasets: [{
                data: [50, 30, 10, 10],
                backgroundColor: ['#1877f2', '#42b883', '#ffc107', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}