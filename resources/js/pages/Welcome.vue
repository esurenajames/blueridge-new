<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import welcomeImage from '../../images/welcome.jpg';

const page = usePage();
const user = computed(() => page.props.auth.user);

const dashboardRoute = computed(() => {
    if (!user.value) return 'login';
    
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
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex min-h-screen flex-col items-center bg-gradient-to-br from-blue-50 to-blue-100 p-6 text-gray-800 dark:from-gray-900 dark:to-gray-800 dark:text-gray-100 lg:justify-center lg:p-8">
        <header class="not-has-[nav]:hidden mb-6 w-full max-w-[335px] text-sm lg:max-w-4xl">
            <nav class="flex items-center justify-end gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route(dashboardRoute)"
                    class="inline-block rounded-lg border border-blue-600 bg-blue-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-blue-700 dark:border-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="route('login')"
                        class="inline-block rounded-lg px-5 py-2 text-sm font-medium text-gray-700 transition hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        Log in
                    </Link>
                    <Link
                        :href="route('register')"
                        class="inline-block rounded-lg border border-blue-600 bg-blue-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-blue-700 dark:border-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600"
                    >
                        Register
                    </Link>
                </template>
            </nav>
        </header>

        <div class="flex w-full items-center justify-center lg:grow">
            <main class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-gray-800 lg:max-w-5xl lg:flex-row">
                <div class="flex-1 p-6 lg:p-12">
                    <h1 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white">
                        Barangay Fund & Procurement System
                    </h1>
                    <p class="mb-6 text-lg text-gray-600 dark:text-gray-300">
                        Streamline your barangay's financial management and procurement processes with our comprehensive digital solution.
                    </p>
                    
                    <div class="mb-6 grid gap-2 md:grid-cols-2">
                        <div class="rounded-lg border border-gray-200 p-5 dark:border-gray-700">
                            <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">Fund Management</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Track and manage barangay funds, budgets, and expenditures with real-time monitoring.
                            </p>
                        </div>
                        
                        <div class="rounded-lg border border-gray-200 p-5 dark:border-gray-700">
                            <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">Procurement System</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Simplify procurement processes with digital documentation and approval workflows.
                            </p>
                        </div>
                        
                        <div class="rounded-lg border border-gray-200 p-5 dark:border-gray-700">
                            <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">Transparency</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Generate detailed reports and maintain transparent financial records.
                            </p>
                        </div>
                        
                        <div class="rounded-lg border border-gray-200 p-5 dark:border-gray-700">
                            <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">Compliance</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Stay compliant with government regulations and auditing requirements.
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-center gap-4">
                        <a href="#" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                            Get Started
                            <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="#" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            Learn More
                        </a>
                    </div>
                </div>

                <div class="relative lg:w-1/2">
                    <div class="absolute inset-0">
                        <img :src="welcomeImage" alt="Background" class="h-full w-full object-cover" />
                        <div class="absolute inset-0 bg-gray-700/70"></div>
                    </div>
                    <div class="relative p-6 text-white lg:flex lg:h-full lg:flex-col lg:justify-center lg:p-12">
                        <h2 class="mb-4 text-2xl font-bold">Why Choose Our System?</h2>
                        <ul class="space-y-4">
                            <li class="flex items-center backdrop-blur-sm bg-white/90 p-3 rounded-lg hover:bg-white/80 transition-colors text-blue-600">
                                <svg class="mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="font-medium">Efficient fund tracking and management</span>
                            </li>
                            <li class="flex items-center backdrop-blur-sm bg-white/90 p-3 rounded-lg hover:bg-white/80 transition-colors text-blue-600">
                                <svg class="mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="font-medium">Streamlined procurement process</span>
                            </li>
                            <li class="flex items-center backdrop-blur-sm bg-white/90 p-3 rounded-lg hover:bg-white/80 transition-colors text-blue-600">
                                <svg class="mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="font-medium">Real-time financial monitoring</span>
                            </li>
                            <li class="flex items-center backdrop-blur-sm bg-white/90 p-3 rounded-lg hover:bg-white/80 transition-colors text-blue-600">
                                <svg class="mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="font-medium">Enhanced transparency and accountability</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>