<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Fund Overview',
        href: '/fund-overview',
    },
];
</script>

<template>
    <Head title="Fund Overview" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col h-full gap-6 p-4 relative">
            <div class="grid  gap-6 md:grid-cols-3 relative min-h-[300px]">
                <div class="flex rounded-xl relative border border-sidebar-border/70 dark:border-sidebar-border">
                     <div class="flex-1 w-full">
                        <PlaceholderPattern class="h-full" />
                     </div>
                </div>
                <div class="flex rounded-xl relative border border-sidebar-border/70 dark:border-sidebar-border">
                     <div class="flex-1 w-full ">
                        <PlaceholderPattern class="h-full" />
                     </div>
                </div>
                <div class="flex rounded-xl relative border border-sidebar-border/70 dark:border-sidebar-border">
                     <div class="flex-1 w-full">
                        <PlaceholderPattern class="h-full" />
                     </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>