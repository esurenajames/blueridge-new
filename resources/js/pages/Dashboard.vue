<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import CreateForm from '../components/barangay-officials/CreateForm.vue';
import MoodTracker from '../components/Moodtracker.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const page = usePage();
const { auth, activeUsers } = page.props;
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col h-full gap-6 p-4 relative">
            <div class="grid  gap-6 md:grid-cols-3 relative">
                <div class="flex rounded-xl relative border border-sidebar-border/70 dark:border-sidebar-border">
                     <div class="flex-1 w-full">
                        <CreateForm 
                          class="h-full"
                          :active-users="activeUsers"
                        />
                     </div>
                </div>
                <div class="flex rounded-xl relative border border-sidebar-border/70 dark:border-sidebar-border">
                     <div class="flex-1 w-full ">
                        <PlaceholderPattern class="h-full" />
                     </div>
                </div>
                <div class="flex rounded-xl relative border border-sidebar-border/70 dark:border-sidebar-border">
                     <div class="flex-1 w-full">
                        <MoodTracker :user-id="auth.user.id" class="h-full" />
                     </div>
                </div>
            </div>
            <div class="flex flex-1 rounded-xl relative border border-sidebar-border/70 dark:border-sidebar-border min-h-[500px] mb-2">
                <div class="flex-1 w-full">
                    <PlaceholderPattern class="h-full" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>