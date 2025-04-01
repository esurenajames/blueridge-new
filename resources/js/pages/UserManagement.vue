<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Plus, Search } from 'lucide-vue-next';
import UsersTable from '@/components/admin/UsersTable.vue';

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  status: 'active' | 'inactive';
  createdAt: string;
}

const props = defineProps<{
  users: User[];
}>();
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="flex flex-col gap-6 p-6">
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
            <UsersTable :users="users" />
        </div>
    </AppLayout>
</template>