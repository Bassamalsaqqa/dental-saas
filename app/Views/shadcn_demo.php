<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">shadcn/ui Components Demo</h1>
        <p class="text-gray-600">Explore the beautiful and accessible components from shadcn/ui integrated into your dental management system.</p>
    </div>

    <!-- Buttons Section -->
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Buttons</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-700">Variants</h3>
                <div class="space-y-3">
                    <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                        Default
                    </button>
                    <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-10 px-4 py-2">
                        Destructive
                    </button>
                    <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                        Outline
                    </button>
                    <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-secondary text-secondary-foreground hover:bg-secondary/80 h-10 px-4 py-2">
                        Secondary
                    </button>
                    <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                        Ghost
                    </button>
                    <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 text-primary underline-offset-4 hover:underline h-10 px-4 py-2">
                        Link
                    </button>
                </div>
            </div>
            
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-700">Sizes</h3>
                <div class="space-y-3">
                    <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 rounded-md px-3">
                        Small
                    </button>
                    <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                        Default
                    </button>
                    <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-11 rounded-md px-8">
                        Large
                    </button>
                </div>
            </div>
            
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-700">With Icons</h3>
                <div class="space-y-3">
                    <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                        <i class="fas fa-plus mr-2"></i>
                        Add Patient
                    </button>
                    <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                        <i class="fas fa-calendar mr-2"></i>
                        Schedule
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Cards Section -->
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Cards</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <h3 class="text-2xl font-semibold leading-none tracking-tight">Patient Card</h3>
                    <p class="text-sm text-muted-foreground">Manage patient information and records</p>
                </div>
                <div class="p-6 pt-0">
                    <p class="text-sm text-gray-600">This is a basic card component with header and content sections.</p>
                </div>
                <div class="flex items-center p-6 pt-0">
                    <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                        View Details
                    </button>
                </div>
            </div>

            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <h3 class="text-2xl font-semibold leading-none tracking-tight">Appointment</h3>
                    <p class="text-sm text-muted-foreground">Scheduled for tomorrow at 2:00 PM</p>
                </div>
                <div class="p-6 pt-0">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium">Patient:</span>
                            <span class="text-sm">John Doe</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium">Treatment:</span>
                            <span class="text-sm">Cleaning</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <h3 class="text-2xl font-semibold leading-none tracking-tight">Statistics</h3>
                    <p class="text-sm text-muted-foreground">Monthly overview</p>
                </div>
                <div class="p-6 pt-0">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-primary">24</div>
                            <div class="text-xs text-muted-foreground">Patients</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">18</div>
                            <div class="text-xs text-muted-foreground">Appointments</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Form Elements Section -->
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Form Elements</h2>
        <div class="max-w-2xl">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
                <h3 class="text-lg font-semibold mb-4">Patient Registration Form</h3>
                <form class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Full Name
                        </label>
                        <input type="text" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" placeholder="Enter patient's full name">
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Email
                        </label>
                        <input type="email" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" placeholder="patient@example.com">
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Phone Number
                        </label>
                        <input type="tel" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" placeholder="+1 (555) 123-4567">
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Date of Birth
                        </label>
                        <input type="date" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                            Register Patient
                        </button>
                        <button type="button" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Badges Section -->
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Badges</h2>
        <div class="space-y-4">
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-primary text-primary-foreground hover:bg-primary/80">
                    Active
                </span>
                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80">
                    Pending
                </span>
                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-destructive text-destructive-foreground hover:bg-destructive/80">
                    Cancelled
                </span>
                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground">
                    Completed
                </span>
            </div>
        </div>
    </section>

    <!-- Table Section -->
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Data Table</h2>
        <div class="relative w-full overflow-auto">
            <table class="w-full caption-bottom text-sm">
                <thead class="[&_tr]:border-b">
                    <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">
                            Patient Name
                        </th>
                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">
                            Appointment
                        </th>
                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">
                            Status
                        </th>
                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="[&_tr:last-child]:border-0">
                    <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                        <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 font-medium">John Doe</td>
                        <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">Tomorrow 2:00 PM</td>
                        <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-primary text-primary-foreground hover:bg-primary/80">
                                Confirmed
                            </span>
                        </td>
                        <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
                            <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                                View
                            </button>
                        </td>
                    </tr>
                    <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                        <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0 font-medium">Jane Smith</td>
                        <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">Today 10:00 AM</td>
                        <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80">
                                Pending
                            </span>
                        </td>
                        <td class="p-4 align-middle [&:has([role=checkbox])]:pr-0">
                            <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                                View
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Integration Notes -->
    <section class="mb-12">
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">Integration Notes</h3>
            <div class="space-y-3 text-sm text-gray-600">
                <p>• All components are styled using shadcn/ui design system</p>
                <p>• Components are fully accessible with proper ARIA attributes</p>
                <p>• Dark mode support is built-in with CSS variables</p>
                <p>• Components can be easily customized using Tailwind CSS classes</p>
                <p>• JavaScript components are available for interactive functionality</p>
            </div>
        </div>
    </section>
</div>

<script>
// Example of using shadcn/ui components with JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('shadcn/ui components are ready!');
    
    // Example: Add click handlers to buttons
    document.querySelectorAll('button').forEach(button => {
        button.addEventListener('click', function(e) {
            if (this.textContent.includes('Add Patient')) {
                console.log('Add Patient clicked');
                // Add your logic here
            }
        });
    });
});
</script>
<?= $this->endSection() ?>

