<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Plus, Search } from 'lucide-vue-next';
import UsersTable from '@/components/admin/UsersTable.vue';
import CreateUser from '@/components/admin/CreateUser.vue';
import { router } from '@inertiajs/vue3';

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  status: 'active' | 'inactive';
  createdAt: string;
}

const showCreateDialog = ref(false);

const handleCreateUser = () => {
    showCreateDialog.value = true;
};

const handleCloseCreate = () => {
    showCreateDialog.value = false;
};

const props = defineProps<{
  users: {
    data: User[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'User Management',
        href: '/users',
    },
];

const searchQuery = ref('');

const filteredUsers = computed(() => {
    if (!searchQuery.value) return props.users;

    const query = searchQuery.value.toLowerCase();
    return {
        ...props.users,
        data: props.users.data.filter(user =>
            user.name.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query) ||
            user.role.toLowerCase().includes(query)
        ),
        total: props.users.data.filter(user =>
            user.name.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query) ||
            user.role.toLowerCase().includes(query)
        ).length,
    };
});

watch(searchQuery, (query) => {
  router.get(route('admin.users'), { search: query }, { preserveState: true, replace: true });
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-3 p-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="relative w-full sm:max-w-md">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" />
                    <Input 
                        v-model="searchQuery"
                        type="search"
                        placeholder="Search users..."
                        class="pl-10 w-full"
                    />
                </div>
                <Button class="w-full sm:w-auto" @click="handleCreateUser">
                    <Plus class="mr-2 h-4 w-4" />
                    Create New User
                </Button>
            </div>
            <UsersTable :users="props.users" />
        </div>

        <CreateUser 
            v-if="showCreateDialog"
            :show="showCreateDialog"
            @close="handleCloseCreate"
        />
    </AppLayout>
</template>