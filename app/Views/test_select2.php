<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Select2 Implementation Test</h1>
        <p class="text-gray-600 mt-2">Testing Select2 integration with various configurations</p>
    </div>

    <!-- Test Form -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Select2 Test Form</h2>
            
            <form class="space-y-6">
                <!-- Basic Searchable Select -->
                <div>
                    <label for="patient_select" class="block text-sm font-medium text-gray-700 mb-2">
                        Patient Selection (Searchable)
                    </label>
                    <select id="patient_select" name="patient_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            data-searchable-select 
                            data-search-url="<?= base_url('api/search/patients') ?>" 
                            data-placeholder="Search patients by name, phone, or ID..."
                            data-allow-clear="true">
                        <option value="">Select a patient...</option>
                        <option value="1">John Doe (123-456-7890)</option>
                        <option value="2">Jane Smith (098-765-4321)</option>
                        <option value="3">Mike Johnson (555-123-4567)</option>
                    </select>
                </div>

                <!-- Regular Select with Select2 -->
                <div>
                    <label for="appointment_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Appointment Type (Regular Select2)
                    </label>
                    <select id="appointment_type" name="appointment_type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select appointment type...</option>
                        <option value="consultation">Consultation</option>
                        <option value="treatment">Treatment</option>
                        <option value="follow_up">Follow-up</option>
                        <option value="emergency">Emergency</option>
                        <option value="cleaning">Cleaning</option>
                        <option value="checkup">Checkup</option>
                        <option value="surgery">Surgery</option>
                        <option value="orthodontics">Orthodontics</option>
                        <option value="cosmetic">Cosmetic</option>
                        <option value="preventive">Preventive Care</option>
                    </select>
                </div>

                <!-- Multi-select Example -->
                <div>
                    <label for="medications" class="block text-sm font-medium text-gray-700 mb-2">
                        Medications (Multi-select)
                    </label>
                    <select id="medications" name="medications[]" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" multiple>
                        <option value="amoxicillin">Amoxicillin</option>
                        <option value="ibuprofen">Ibuprofen</option>
                        <option value="acetaminophen">Acetaminophen</option>
                        <option value="penicillin">Penicillin</option>
                        <option value="doxycycline">Doxycycline</option>
                        <option value="clindamycin">Clindamycin</option>
                        <option value="metronidazole">Metronidazole</option>
                        <option value="prednisone">Prednisone</option>
                    </select>
                </div>

                <!-- Treatment Types with AJAX -->
                <div>
                    <label for="treatment_types" class="block text-sm font-medium text-gray-700 mb-2">
                        Treatment Types (AJAX Search)
                    </label>
                    <select id="treatment_types" name="treatment_type" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            data-searchable-select 
                            data-search-url="<?= base_url('api/search/treatment-types') ?>"
                            data-placeholder="Search treatment types..."
                            data-minimum-input-length="2"
                            data-delay="500">
                        <option value="">Select treatment type...</option>
                    </select>
                </div>

                <!-- Medications with AJAX -->
                <div>
                    <label for="medication_search" class="block text-sm font-medium text-gray-700 mb-2">
                        Medication Search (AJAX)
                    </label>
                    <select id="medication_search" name="medication" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            data-searchable-select 
                            data-search-url="<?= base_url('api/search/medications') ?>"
                            data-placeholder="Search medications..."
                            data-minimum-input-length="1"
                            data-delay="300">
                        <option value="">Search medications...</option>
                    </select>
                </div>

                <!-- Disabled Select -->
                <div>
                    <label for="disabled_select" class="block text-sm font-medium text-gray-700 mb-2">
                        Disabled Select
                    </label>
                    <select id="disabled_select" name="disabled_field" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" disabled>
                        <option value="">This select is disabled</option>
                        <option value="1">Option 1</option>
                        <option value="2">Option 2</option>
                    </select>
                </div>

                <!-- Error State Example -->
                <div>
                    <label for="error_select" class="block text-sm font-medium text-gray-700 mb-2">
                        Error State Example
                    </label>
                    <select id="error_select" name="error_field" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 border-red-500">
                        <option value="">This select has an error state</option>
                        <option value="1">Option 1</option>
                        <option value="2">Option 2</option>
                    </select>
                    <p class="text-red-500 text-sm mt-1">This field has an error</p>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-save mr-2"></i>Test Submit
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Features List -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm mt-8">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Select2 Features Implemented</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <h3 class="font-medium text-gray-700">Core Features</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Searchable dropdowns</li>
                        <li>• AJAX data loading</li>
                        <li>• Multi-select support</li>
                        <li>• Custom styling</li>
                        <li>• Responsive design</li>
                    </ul>
                </div>
                <div class="space-y-2">
                    <h3 class="font-medium text-gray-700">Advanced Features</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Loading states</li>
                        <li>• Error handling</li>
                        <li>• Form validation integration</li>
                        <li>• Dark mode support</li>
                        <li>• Accessibility features</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Additional test functionality
document.addEventListener('DOMContentLoaded', function() {
    // Test form submission
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Form submitted! Check console for selected values.');
            
            // Log selected values
            const formData = new FormData(form);
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
        });
    }
    
    // Test error state styling
    const errorSelect = document.getElementById('error_select');
    if (errorSelect) {
        $(errorSelect).on('select2:open', function() {
            $(this).next('.select2-container').addClass('is-invalid');
        });
    }
});
</script>
<?= $this->endSection() ?>
