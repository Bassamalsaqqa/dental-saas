<?= $this->extend('layouts/main_auth') ?>

<?= $this->section('content') ?>
<div class="px-4 sm:px-6 lg:px-8 py-6">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
            <div class="space-y-2">
                <h1 class="text-3xl lg:text-4xl font-bold text-gray-900">Examination Calendar</h1>
                <p class="text-gray-600 text-base lg:text-lg">View and manage examination schedules</p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                <a href="<?= base_url('examination') ?>" class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Examinations
                </a>
                <a href="<?= base_url('examination/create') ?>" class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                    <i class="fas fa-plus mr-2"></i>New Examination
                </a>
            </div>
        </div>
    </div>

    <!-- Calendar Container -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="p-6">
            <!-- Loading Indicator -->
            <div id="loading-indicator" class="hidden flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="ml-3 text-gray-600">Loading calendar events...</span>
            </div>

            <!-- Calendar -->
            <div id="calendar" class="calendar-container"></div>
        </div>
    </div>
</div>

<!-- Examination Details Modal -->
<div id="examinationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Examination Details</h3>
                <button onclick="closeExaminationModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="examinationDetails" class="space-y-3">
                <!-- Details will be populated here -->
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button onclick="closeExaminationModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Close
                </button>
                <button id="editExaminationBtn" onclick="editExamination()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Edit Examination
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-container {
    min-height: 600px;
}

.fc-event {
    cursor: pointer;
    border-radius: 4px;
    padding: 2px 4px;
    font-size: 12px;
}

.fc-event:hover {
    opacity: 0.8;
}

.fc-event.scheduled {
    background-color: #3b82f6;
    border-color: #3b82f6;
}

.fc-event.completed {
    background-color: #10b981;
    border-color: #10b981;
}

.fc-event.cancelled {
    background-color: #ef4444;
    border-color: #ef4444;
}

.fc-event.rescheduled {
    background-color: #f59e0b;
    border-color: #f59e0b;
}
</style>

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
            
            fetch(`<?= base_url('examination/calendar-events') ?>?start=${startDate}&end=${endDate}`, {
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
                // Hide loading indicator
                if (loadingIndicator) {
                    loadingIndicator.classList.add('hidden');
                }
                
                if (data.error) {
                    console.error('Calendar events error:', data.error);
                    failureCallback(data.error);
                } else {
                    // Add CSS classes based on status
                    const events = data.map(event => ({
                        ...event,
                        className: `examination-event ${event.status}`
                    }));
                    
                    successCallback(events);
                }
            })
            .catch(error => {
                console.error('Error fetching calendar events:', error);
                
                // Hide loading indicator
                if (loadingIndicator) {
                    loadingIndicator.classList.add('hidden');
                }
                
                failureCallback(error);
            });
        },
        eventClick: function(info) {
            showExaminationDetails(info.event);
        },
        eventDidMount: function(info) {
            // Add tooltip
            info.el.title = `${info.event.title} - ${info.event.extendedProps.examination_type || 'Examination'}`;
        },
        height: 'auto',
        aspectRatio: 1.8,
        dayMaxEvents: true,
        moreLinkClick: 'popover',
        eventDisplay: 'block'
    });

    calendar.render();
});

function showExaminationDetails(event) {
    const modal = document.getElementById('examinationModal');
    const detailsContainer = document.getElementById('examinationDetails');
    const editBtn = document.getElementById('editExaminationBtn');
    
    // Populate details
    detailsContainer.innerHTML = `
        <div class="space-y-3">
            <div>
                <label class="text-sm font-medium text-gray-600">Patient</label>
                <p class="text-gray-900">${event.title}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600">Date & Time</label>
                <p class="text-gray-900">${event.start.toLocaleDateString()} at ${event.start.toLocaleTimeString()}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600">Status</label>
                <span class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-${getStatusColor(event.extendedProps.status)}-100 text-${getStatusColor(event.extendedProps.status)}-800">
                    ${event.extendedProps.status || 'Scheduled'}
                </span>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600">Examination Type</label>
                <p class="text-gray-900">${event.extendedProps.examination_type || 'General Examination'}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600">Duration</label>
                <p class="text-gray-900">${event.extendedProps.duration || 30} minutes</p>
            </div>
        </div>
    `;
    
    // Set edit button action
    editBtn.onclick = () => {
        window.location.href = `<?= base_url('examination/edit') ?>/${event.id}`;
    };
    
    modal.classList.remove('hidden');
}

function closeExaminationModal() {
    document.getElementById('examinationModal').classList.add('hidden');
}

function editExamination() {
    // This will be handled by the onclick event set in showExaminationDetails
}

function getStatusColor(status) {
    switch(status) {
        case 'completed': return 'green';
        case 'cancelled': return 'red';
        case 'rescheduled': return 'yellow';
        default: return 'blue';
    }
}
</script>
<?= $this->endSection() ?>
