<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { MoreHorizontal } from 'lucide-vue-next'
import { computed } from 'vue';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Plus, Search, Pencil, Trash2 } from 'lucide-vue-next';

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  status: 'active' | 'inactive';
  createdAt: string;
}

const page = usePage();
const users = computed(() => page.props.users as User[]);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'User Management',
        href: '/user',
    },
];

// ...existing code...
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
                <Button class="w-full sm:w-auto">
                    <Plus class="mr-2 h-4 w-4" />
                    Create New User
                </Button>
            </div>

            <!-- Users Table -->
           <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[150px]">Name</TableHead>
                            <TableHead class="w-[200px]">Email</TableHead>
                            <TableHead class="w-[100px]">Role</TableHead>
                            <TableHead class="w-[100px]">Status</TableHead>
                            <TableHead class="w-[120px]">Created At</TableHead>
                            <TableHead class="w-[100px]">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in users" :key="user.id">
                            <TableCell class="font-medium">{{ user.name }}</TableCell>
                            <TableCell>{{ user.email }}</TableCell>
                            <TableCell>
                                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-900/30 dark:text-blue-400">
                                    {{ user.role }}
                                </span>
                            </TableCell>
                            <TableCell>
                                <span 
                                    class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                    :class="{
                                        'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-900/30 dark:text-green-400': user.status === 'active',
                                        'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-900/30 dark:text-red-400': user.status === 'inactive'
                                    }"
                                >
                                    {{ user.status }}
                                </span>
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ new Date(user.createdAt).toLocaleDateString() }}
                            </TableCell>
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button variant="ghost" size="icon">
                                            <MoreHorizontal class="h-4 w-4" />
                                            <span class="sr-only">Open menu</span>
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem>
                                            <Pencil class="mr-2 h-4 w-4" />
                                            <span>Edit</span>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem class="text-destructive">
                                            <Trash2 class="mr-2 h-4 w-4" />
                                            <span>Delete</span>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>