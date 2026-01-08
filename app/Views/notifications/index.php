<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="px-4 sm:px-6 lg:px-8 py-6">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
            <div class="space-y-2">
                <h1 class="text-3xl lg:text-4xl font-bold text-gray-900">Notifications</h1>
                <p class="text-gray-600 text-base lg:text-lg">Stay updated with your latest activities and alerts</p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                <button onclick="markAllAsRead()" class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                    <i class="fas fa-check-double mr-2"></i>Mark All as Read
                </button>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="space-y-4">
        <?php if (empty($notifications)): ?>
            <!-- Empty State -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bell text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No notifications yet</h3>
                <p class="text-gray-600">You're all caught up! New notifications will appear here.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 <?= !$notification['is_read'] ? 'border-l-4 border-l-blue-500' : '' ?>">
                    <div class="p-6">
                        <div class="flex items-start space-x-4">
                            <!-- Notification Icon -->
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-<?= $notification['color'] ?>-500 to-<?= $notification['color'] ?>-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                                    <i class="<?= $notification['icon'] ?> text-lg"></i>
                                </div>
                            </div>
                            
                            <!-- Notification Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                            <?= $notification['title'] ?>
                                            <?php if (!$notification['is_read']): ?>
                                                <span class="inline-block w-2 h-2 bg-blue-500 rounded-full ml-2"></span>
                                            <?php endif; ?>
                                        </h3>
                                        <p class="text-gray-600 mb-3 leading-relaxed"><?= $notification['message'] ?></p>
                                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                                            <span class="flex items-center">
                                                <i class="fas fa-clock mr-1"></i>
                                                <?= date('M j, Y g:i A', strtotime($notification['created_at'])) ?>
                                            </span>
                                            <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">
                                                <?= ucfirst($notification['type']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="flex items-center space-x-2 ml-4">
                                        <?php if (!$notification['is_read']): ?>
                                            <button onclick="markAsRead(<?= $notification['id'] ?>)" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200" title="Mark as read">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button onclick="deleteNotification(<?= $notification['id'] ?>)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Load More Button -->
    <?php if (!empty($notifications)): ?>
        <div class="mt-8 text-center">
            <button class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                <i class="fas fa-chevron-down mr-2"></i>Load More Notifications
            </button>
        </div>
    <?php endif; ?>
</div>

<script>
function markAsRead(id) {
    fetch('<?= base_url('notifications/mark-read') ?>/' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the unread indicator
            const notification = document.querySelector(`[onclick="markAsRead(${id})"]`).closest('.bg-white');
            const unreadDot = notification.querySelector('.bg-blue-500');
            if (unreadDot) {
                unreadDot.remove();
            }
            
            // Remove the mark as read button
            const markButton = notification.querySelector(`[onclick="markAsRead(${id})"]`);
            if (markButton) {
                markButton.remove();
            }
            
            // Remove border-left indicator
            notification.classList.remove('border-l-4', 'border-l-blue-500');
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function markAllAsRead() {
    fetch('<?= base_url('notifications/mark-read') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the page to update all notifications
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}

function deleteNotification(id) {
    if (confirm('Are you sure you want to delete this notification?')) {
        fetch('<?= base_url('notifications/delete') ?>/' + id, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the notification from the DOM
                const notification = document.querySelector(`[onclick="deleteNotification(${id})"]`).closest('.bg-white');
                notification.remove();
            }
        })
        .catch(error => {
            console.error('Error deleting notification:', error);
        });
    }
}
</script>
<?= $this->endSection() ?>
