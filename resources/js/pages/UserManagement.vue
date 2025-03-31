<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Plus, Search, Pencil, Trash2 } from 'lucide-vue-next';

const users = [
    {
        id: 1,
        name: 'John Doe',
        email: 'john@example.com',
        role: 'admin',
        status: 'active',
        createdAt: '2024-03-31',
    },
    // ... more mock data for testing
];

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'User Management',
        href: '/user',
    },
];

</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Search and Filters -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="relative w-full sm:max-w-md">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" />
                    <Input 
                        type="search"
                        placeholder="Search users..."
                        class="pl-10 w-full"
                    />
                </div>
                <Button variant="default" class="w-full sm:w-auto shadow-sm">
                    <Plus class="mr-2 h-4 w-4" />
                    Create New User
                </Button>
            </div>

            <!-- Users Table -->
            <div class="rounded-lg border bg-white shadow-sm dark:bg-gray-900 overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="min-w-[150px]">Name</TableHead>
                            <TableHead class="min-w-[200px]">Email</TableHead>
                            <TableHead class="min-w-[100px]">Role</TableHead>
                            <TableHead class="min-w-[100px]">Status</TableHead>
                            <TableHead class="min-w-[120px]">Created At</TableHead>
                            <TableHead class="min-w-[100px]">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in users" :key="user.id">
                            <TableCell class="font-medium">{{ user.name }}</TableCell>
                            <TableCell>{{ user.email }}</TableCell>
                            <TableCell>
                                <span class="capitalize inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                    {{ user.role }}
                                </span>
                            </TableCell>
                            <TableCell>
                                <span 
                                    class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                    :class="{
                                        'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400': user.status === 'active',
                                        'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400': user.status === 'inactive'
                                    }"
                                >
                                    {{ user.status }}
                                </span>
                            </TableCell>
                            <TableCell class="text-gray-500 dark:text-gray-400">
                                {{ new Date(user.createdAt).toLocaleDateString() }}
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <Button 
                                        variant="outline" 
                                        size="icon"
                                        class="h-8 w-8"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button 
                                        variant="outline" 
                                        size="icon"
                                        class="h-8 w-8 text-red-500 hover:text-red-600 hover:border-red-600"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>