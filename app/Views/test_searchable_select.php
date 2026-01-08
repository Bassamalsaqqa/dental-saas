<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Searchable Select Test Page</h1>
        <p class="text-gray-600 mt-1">Test the searchable select functionality across different data types</p>
    </div>

    <div class="space-y-8">
        <!-- Patient Search Test -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Patient Search</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Patients</label>
                    <select id="patientSearch" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            data-searchable-select 
                            data-search-url="<?= base_url('api/search/patients') ?>"
                            data-placeholder="Search patients by name, phone, or ID...">
                        <option value="">Select a patient</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Selected Value</label>
                    <input type="text" id="patientValue" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" readonly placeholder="Selected patient ID will appear here">
                </div>
            </div>
        </div>

        <!-- Medication Search Test -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Medication Search</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Medications</label>
                    <select id="medicationSearch" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            data-searchable-select 
                            data-search-url="<?= base_url('api/search/medications') ?>"
                            data-placeholder="Search medications...">
                        <option value="">Select a medication</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Selected Value</label>
                    <input type="text" id="medicationValue" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" readonly placeholder="Selected medication will appear here">
                </div>
            </div>
        </div>

        <!-- Treatment Type Search Test -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Treatment Type Search</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Treatment Types</label>
                    <select id="treatmentTypeSearch" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            data-searchable-select 
                            data-search-url="<?= base_url('api/search/treatment-types') ?>"
                            data-placeholder="Search treatment types...">
                        <option value="">Select a treatment type</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Selected Value</label>
                    <input type="text" id="treatmentTypeValue" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" readonly placeholder="Selected treatment type will appear here">
                </div>
            </div>
        </div>

        <!-- Department Search Test -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Department Search</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Departments</label>
                    <select id="departmentSearch" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            data-searchable-select 
                            data-search-url="<?= base_url('api/search/departments') ?>"
                            data-placeholder="Search departments...">
                        <option value="">Select a department</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Selected Value</label>
                    <input type="text" id="departmentValue" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" readonly placeholder="Selected department will appear here">
                </div>
            </div>
        </div>

        <!-- Role Search Test -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Role Search</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Roles</label>
                    <select id="roleSearch" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            data-searchable-select 
                            data-search-url="<?= base_url('api/search/roles') ?>"
                            data-placeholder="Search roles...">
                        <option value="">Select a role</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Selected Value</label>
                    <input type="text" id="roleValue" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" readonly placeholder="Selected role will appear here">
                </div>
            </div>
        </div>

        <!-- Test Results -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Test Results</h3>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-medium text-gray-700 mb-2">Selected Values:</h4>
                <ul id="testResults" class="space-y-1 text-sm text-gray-600">
                    <li>No selections made yet</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update test results when selections change
    function updateTestResults() {
        const results = [];
        
        const patientValue = document.getElementById('patientSearch').value;
        if (patientValue) {
            results.push(`Patient: ${patientValue}`);
        }
        
        const medicationValue = document.getElementById('medicationSearch').value;
        if (medicationValue) {
            results.push(`Medication: ${medicationValue}`);
        }
        
        const treatmentTypeValue = document.getElementById('treatmentTypeSearch').value;
        if (treatmentTypeValue) {
            results.push(`Treatment Type: ${treatmentTypeValue}`);
        }
        
        const departmentValue = document.getElementById('departmentSearch').value;
        if (departmentValue) {
            results.push(`Department: ${departmentValue}`);
        }
        
        const roleValue = document.getElementById('roleSearch').value;
        if (roleValue) {
            results.push(`Role: ${roleValue}`);
        }
        
        const resultsList = document.getElementById('testResults');
        if (results.length > 0) {
            resultsList.innerHTML = results.map(result => `<li>${result}</li>`).join('');
        } else {
            resultsList.innerHTML = '<li>No selections made yet</li>';
        }
    }
    
    // Add event listeners to all searchable selects
    const searchableSelects = document.querySelectorAll('[data-searchable-select]');
    searchableSelects.forEach(select => {
        select.addEventListener('change', updateTestResults);
    });
    
    // Update individual value displays
    document.getElementById('patientSearch').addEventListener('change', function() {
        document.getElementById('patientValue').value = this.value;
    });
    
    document.getElementById('medicationSearch').addEventListener('change', function() {
        document.getElementById('medicationValue').value = this.value;
    });
    
    document.getElementById('treatmentTypeSearch').addEventListener('change', function() {
        document.getElementById('treatmentTypeValue').value = this.value;
    });
    
    document.getElementById('departmentSearch').addEventListener('change', function() {
        document.getElementById('departmentValue').value = this.value;
    });
    
    document.getElementById('roleSearch').addEventListener('change', function() {
        document.getElementById('roleValue').value = this.value;
    });
});
</script>
<?= $this->endSection() ?>
