<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-6 py-8">
<div class="space-y-8">
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-6 lg:space-y-0">
        <div>
            <h1 class="text-4xl font-bold text-gray-800 mb-3">Appointment Calendar</h1>
            <p class="text-gray-600 text-lg">View and manage appointments in calendar format</p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
            <a href="<?= base_url('appointment') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-list mr-2"></i>List View
            </a>
            <a href="<?= base_url('appointment/create') ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-plus mr-2"></i>New Appointment
            </a>
        </div>
    </div>

    <!-- Calendar Container -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Calendar View</h3>
                    <p class="text-sm text-gray-600">Click on any date to view or create appointments</p>
                </div>
                <div class="flex items-center space-x-2">
                    <div id="loading-indicator" class="hidden flex items-center space-x-2 text-blue-600">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                        <span class="text-sm">Loading appointments...</span>
                    </div>
                </div>
            </div>
            
            <div id="calendar" class="w-full" style="height: 600px;"></div>
        </div>
    </div>

    <!-- Legend -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-lg transition-all duration-300">
        <div class="p-6">
            <div class="mb-4">
                <h3 class="text-lg font-bold text-gray-800 mb-2">Status Legend</h3>
                <p class="text-sm text-gray-600">Appointment status color coding</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-blue-500 rounded"></div>
                    <span class="text-sm text-gray-600">Scheduled</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-green-500 rounded"></div>
                    <span class="text-sm text-gray-600">Confirmed</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-gray-500 rounded"></div>
                    <span class="text-sm text-gray-600">Completed</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-red-500 rounded"></div>
                    <span class="text-sm text-gray-600">Cancelled</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                    <span class="text-sm text-gray-600">No Show</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const loadingIndicator = document.getElementById('loading-indicator');
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: function(info, successCallback, failureCallback) {
            // Show loading indicator
            if (loadingIndicator) {
                loadingIndicator.classList.remove('hidden');
            }
            
            const startDate = info.startStr;
            const endDate = info.endStr;
            
            fetch(`<?= base_url('appointment/calendar-events') ?>?start=${startDate}&end=${endDate}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Calendar events loaded:', data);
                
                const events = data.map(event => ({
                    id: event.id,
                    title: event.title,
                    start: event.start,
                    end: event.end,
                    backgroundColor: getStatusColor(event.status),
                    borderColor: getStatusColor(event.status),
                    textColor: '#ffffff',
                    url: `<?= base_url('appointment') ?>/${event.id}`,
                    extendedProps: {
                        status: event.status,
                        patient_id: event.patient_id
                    }
                }));
                
                successCallback(events);
            })
            .catch(error => {
                console.error('Error loading calendar events:', error);
                
                // Show error message
                showError('Failed to load appointments. Please try again.');
                
                failureCallback(error);
            })
            .finally(() => {
                // Hide loading indicator
                if (loadingIndicator) {
                    loadingIndicator.classList.add('hidden');
                }
            });
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            window.location.href = info.event.url;
        },
        dateClick: function(info) {
            const date = info.dateStr;
            window.location.href = `<?= base_url('appointment/create') ?>?date=${date}`;
        },
        eventDidMount: function(info) {
            // Add tooltip with appointment details
            const event = info.event;
            const tooltip = `${event.title}\nStatus: ${event.extendedProps.status}\nClick to view details`;
            info.el.setAttribute('title', tooltip);
        },
        height: 600,
        aspectRatio: 1.8,
        dayMaxEvents: 3,
        moreLinkClick: 'popover',
        eventDisplay: 'block',
        nowIndicator: true,
        businessHours: {
            daysOfWeek: <?= json_encode(array_map(function($day) {
                $dayMap = [
                    'monday' => 1,
                    'tuesday' => 2,
                    'wednesday' => 3,
                    'thursday' => 4,
                    'friday' => 5,
                    'saturday' => 6,
                    'sunday' => 0
                ];
                return $dayMap[strtolower($day)] ?? 1;
            }, $workingHours['days'])) ?>,
            startTime: '<?= $workingHours['start'] ?>',
            endTime: '<?= $workingHours['end'] ?>'
        },
        slotMinTime: '<?= date('H:i:s', strtotime($workingHours['start']) - 3600) ?>',
        slotMaxTime: '<?= date('H:i:s', strtotime($workingHours['end']) + 3600) ?>'
    });
    
    calendar.render();
    
    function getStatusColor(status) {
        const colors = {
            'scheduled': '#3b82f6',
            'confirmed': '#10b981',
            'completed': '#6b7280',
            'cancelled': '#ef4444',
            'no_show': '#f59e0b'
        };
        return colors[status] || '#6b7280';
    }
    
    function showError(message) {
        // Create error notification
        const errorDiv = document.createElement('div');
        errorDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        errorDiv.innerHTML = `
            <div class="flex items-center space-x-2">
                <i class="fas fa-exclamation-triangle"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(errorDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 5000);
    }
});
</script>
</div>
</div>
<?= $this->endSection() ?>
