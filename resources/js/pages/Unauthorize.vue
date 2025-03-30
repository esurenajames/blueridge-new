<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);

const dashboardRoute = computed(() => {
    switch (user.value.role) {
        case 'admin':
            return 'admin.dashboard';
        case 'captain':
            return 'captain.dashboard';
        case 'secretary':
            return 'secretary.dashboard';
        case 'treasurer':
            return 'treasurer.dashboard';
        default:
            return 'dashboard';
    }
});
</script>

<template>
    <Head title="Unauthorized Access" />
    
    <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-blue-50 to-blue-100 p-6 dark:from-gray-900 dark:to-gray-800">
        <div class="w-full max-w-md text-center">
            <div class="mb-8">
                <svg 
                    class="mx-auto h-16 w-16 text-blue-500" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path 
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    />
                </svg>
            </div>
            
            <h1 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white">
                Access Denied
            </h1>

            <p class="mb-8 text-lg text-gray-600 dark:text-gray-300">
                You don't have permission to access this page. Please contact your administrator for assistance.
            </p>
            
            <div class="space-x-4">
                <Link
                    :href="route(dashboardRoute)"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
                >
                    Return to Dashboard
                </Link>
            </div>
        </div>
    </div>
</template>