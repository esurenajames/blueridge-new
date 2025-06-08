<script setup lang="ts">
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Plus, Search, ChevronDownIcon } from 'lucide-vue-next';
import UsersTable from '@/components/admin/UsersTable.vue';
import CreateUser from '@/components/admin/CreateUser.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
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
  search?: string;
  type?: string;
  status?: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'User Management',
        href: '/users',
    },
];

const searchQuery = ref(props.search ?? '');
const typeFilter = ref(props.type ?? '');
const statusFilter = ref(props.status ?? '');

const typeOptions = [
  { label: 'All Roles', value: '' },
  { label: 'Admin', value: 'admin' },
  { label: 'Captain', value: 'captain' },
  { label: 'Treasurer', value: 'treasurer' },
  { label: 'Barangay Official', value: 'official' },
];

const statusOptions = [
  { label: 'All Status', value: '' },
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' },
];

watch([searchQuery, typeFilter, statusFilter], ([query, type, status]) => {
  router.get(
    route('admin.users'),
    { search: query, type: type, status: status },
    { preserveState: true, replace: true }
  );
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
                <div class="flex items-center gap-2">
                  <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                      <Button variant="outline" class="w-[200px] justify-between">
                        {{ typeFilter ? typeOptions.find(t => t.value === typeFilter)?.label : 'All Roles' }}
                        <ChevronDownIcon class="ml-2 h-4 w-4" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent class="w-[200px]">
                      <DropdownMenuItem
                        v-for="option in typeOptions"
                        :key="option.value"
                        @click="typeFilter = option.value"
                        :class="{'bg-accent': typeFilter === option.value}"
                      >
                        <span>
                          {{ option.label }}
                        </span>
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                  <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                      <Button variant="outline" class="w-[200px] justify-between">
                        {{ statusFilter ? statusOptions.find(s => s.value === statusFilter)?.label : 'All Status' }}
                        <ChevronDownIcon class="ml-2 h-4 w-4" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent class="w-[200px]">
                      <DropdownMenuItem
                        v-for="option in statusOptions"
                        :key="option.value"
                        @click="statusFilter = option.value"
                        :class="{'bg-accent': statusFilter === option.value}"
                      >
                        <span>
                          {{ option.label }}
                        </span>
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                  <Button class="w-full sm:w-auto" @click="handleCreateUser">
                      <Plus class="mr-2 h-4 w-4" />
                      Create New User
                  </Button>
                </div>
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