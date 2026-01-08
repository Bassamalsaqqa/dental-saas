<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gradient-to-br from-neutral-50 to-white">
    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-blue-700 to-dental-800">
        <div class="absolute inset-0 bg-black/20"></div>
        <div class="relative px-6 py-24 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-4xl text-center">
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl">
                    Modern Dental Management System
                </h1>
                <p class="mt-6 text-lg leading-8 text-dental-100">
                    Professional, clean, and modern design built with TailwindCSS and shadcn/ui components
                </p>
                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <button class="btn inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors btn-lg" data-button="primary">
                        <i class="fas fa-play mr-2"></i>
                        View Demo
                    </button>
                    <button class="btn btn-outline btn-lg" data-button="outline">
                        <i class="fas fa-download mr-2"></i>
                        Download
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-neutral-900 sm:text-4xl">
                    Modern Design Features
                </h2>
                <p class="mt-6 text-lg leading-8 text-neutral-600">
                    Built with the latest technologies and design principles for a professional dental practice management system.
                </p>
            </div>
            
            <!-- Feature Cards -->
            <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
                <div class="grid max-w-xl grid-cols-1 gap-8 lg:max-w-none lg:grid-cols-3">
                    <!-- Feature 1 -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-xl transition-all duration-300" data-card="elevated">
                        <div class="p-8">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-palette text-white text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-neutral-900 mb-4">Modern Design</h3>
                            <p class="text-neutral-600">
                                Clean, professional interface with modern design principles and smooth animations.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-xl transition-all duration-300" data-card="elevated">
                        <div class="p-8">
                            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-mobile-alt text-white text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-neutral-900 mb-4">Responsive Design</h3>
                            <p class="text-neutral-600">
                                Fully responsive design that works perfectly on all devices and screen sizes.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm group hover:shadow-xl transition-all duration-300" data-card="elevated">
                        <div class="p-8">
                            <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-universal-access text-white text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-neutral-900 mb-4">Accessibility</h3>
                            <p class="text-neutral-600">
                                WCAG 2.1 AA compliant with full keyboard navigation and screen reader support.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Component Showcase -->
    <div class="py-24 sm:py-32 bg-neutral-50">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-neutral-900 sm:text-4xl">
                    Component Showcase
                </h2>
                <p class="mt-6 text-lg leading-8 text-neutral-600">
                    Explore the modern components and design elements used throughout the system.
                </p>
            </div>

            <!-- Buttons Section -->
            <div class="mt-16">
                <h3 class="text-2xl font-semibold text-neutral-900 mb-8">Button Components</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <button class="btn inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors" data-button="primary">Primary</button>
                    <button class="btn inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors" data-button="secondary">Secondary</button>
                    <button class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors" data-button="dental">Dental</button>
                    <button class="btn btn-outline" data-button="outline">Outline</button>
                    <button class="btn btn-ghost" data-button="ghost">Ghost</button>
                    <button class="btn btn-success" data-button="success">Success</button>
                    <button class="btn btn-warning" data-button="warning">Warning</button>
                    <button class="btn btn-error" data-button="error">Error</button>
                </div>
            </div>

            <!-- Cards Section -->
            <div class="mt-16">
                <h3 class="text-2xl font-semibold text-neutral-900 mb-8">Card Components</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm" data-card="default">
                        <div class="p-6">
                            <h4 class="text-lg font-semibold text-neutral-900 mb-2">Default Card</h4>
                            <p class="text-neutral-600">This is a default card component with clean styling.</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm" data-card="elevated">
                        <div class="p-6">
                            <h4 class="text-lg font-semibold text-neutral-900 mb-2">Elevated Card</h4>
                            <p class="text-neutral-600">This card has enhanced shadow and elevation.</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm" data-card="success">
                        <div class="p-6">
                            <h4 class="text-lg font-semibold text-neutral-900 mb-2">Success Card</h4>
                            <p class="text-neutral-600">This card has success-themed styling.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Components -->
            <div class="mt-16">
                <h3 class="text-2xl font-semibold text-neutral-900 mb-8">Form Components</h3>
                <div class="max-w-md">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter your email" data-input>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <input type="password" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter your password" data-input>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                            <textarea class="form-textarea" rows="3" placeholder="Enter your message" data-input></textarea>
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" class="form-checkbox" data-input>
                            <label class="text-sm text-neutral-600">Remember me</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Components -->
            <div class="mt-16">
                <h3 class="text-2xl font-semibold text-neutral-900 mb-8">Alert Components</h3>
                <div class="space-y-4">
                    <div class="alert alert-success" data-alert="success">
                        <i class="fas fa-check-circle mr-2"></i>
                        <div class="alert-content">
                            <div class="alert-message">This is a success alert message.</div>
                        </div>
                    </div>
                    <div class="alert alert-warning" data-alert="warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <div class="alert-content">
                            <div class="alert-message">This is a warning alert message.</div>
                        </div>
                    </div>
                    <div class="alert alert-error" data-alert="destructive">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <div class="alert-content">
                            <div class="alert-message">This is an error alert message.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Component -->
            <div class="mt-16">
                <h3 class="text-2xl font-semibold text-neutral-900 mb-8">Table Component</h3>
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm" data-card="elevated">
                    <div class="overflow-x-auto" data-table="sortable">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th data-sortable="name">Name</th>
                                    <th data-sortable="email">Email</th>
                                    <th data-sortable="role">Role</th>
                                    <th data-sortable="status">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td data-sort="name">Dr. John Smith</td>
                                    <td data-sort="email">john@dentalcare.com</td>
                                    <td data-sort="role">Administrator</td>
                                    <td data-sort="status">
                                        <span class="badge badge-success">Active</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-sort="name">Dr. Jane Doe</td>
                                    <td data-sort="email">jane@dentalcare.com</td>
                                    <td data-sort="role">Dentist</td>
                                    <td data-sort="status">
                                        <span class="badge badge-success">Active</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-sort="name">Mike Johnson</td>
                                    <td data-sort="email">mike@dentalcare.com</td>
                                    <td data-sort="role">Assistant</td>
                                    <td data-sort="status">
                                        <span class="badge badge-warning">Pending</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-neutral-900 sm:text-4xl">
                    Ready to Get Started?
                </h2>
                <p class="mt-6 text-lg leading-8 text-neutral-600">
                    Experience the modern dental management system with professional design and functionality.
                </p>
                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <button class="btn inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors btn-lg" data-button="primary">
                        <i class="fas fa-rocket mr-2"></i>
                        Get Started
                    </button>
                    <button class="btn btn-outline btn-lg" data-button="outline">
                        <i class="fas fa-info-circle mr-2"></i>
                        Learn More
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Demo functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers for demo buttons
    const demoButtons = document.querySelectorAll('[data-button]');
    demoButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show alert based on button type
            const buttonType = this.getAttribute('data-button');
            const messages = {
                'primary': 'Primary button clicked!',
                'secondary': 'Secondary button clicked!',
                'dental': 'Dental button clicked!',
                'outline': 'Outline button clicked!',
                'ghost': 'Ghost button clicked!',
                'success': 'Success button clicked!',
                'warning': 'Warning button clicked!',
                'error': 'Error button clicked!'
            };
            
            if (window.AlertManager) {
                window.AlertManager.success(messages[buttonType] || 'Button clicked!');
            } else {
                alert(messages[buttonType] || 'Button clicked!');
            }
        });
    });

    // Add hover effects to cards
    const cards = document.querySelectorAll('[data-card]');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>
<?= $this->endSection() ?>
