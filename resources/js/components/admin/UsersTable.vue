<script setup lang="ts">
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { MoreHorizontal, ChevronUp, ChevronDown } from 'lucide-vue-next';
import { Pagination, PaginationList, PaginationListItem, PaginationEllipsis, PaginationFirst, PaginationLast, PaginationNext, PaginationPrev } from '@/components/ui/pagination';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pencil, Trash2, KeyRound } from 'lucide-vue-next';
import EditUser from './EditUser.vue';
import DeleteUser from './DeleteUser.vue';

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

const sortKey = ref<keyof User | null>(null);
const sortOrder = ref<'asc' | 'desc'>('asc');
const currentPage = ref(1);
const itemsPerPage = ref(10);

const sortedUsers = computed(() => {
  if (!sortKey.value) return props.users;
  
  return [...props.users].sort((a, b) => {
    const valueA = a[sortKey.value!];
    const valueB = b[sortKey.value!];

    if (typeof valueA === 'string' && typeof valueB === 'string') {
      return sortOrder.value === 'asc' ? valueA.localeCompare(valueB) : valueB.localeCompare(valueA);
    }
    if (typeof valueA === 'number' && typeof valueB === 'number') {
      return sortOrder.value === 'asc' ? valueA - valueB : valueB - valueA;
    }
    return 0;
  });
});

const showEditDialog = ref(false);
const showDeleteDialog = ref(false);
const selectedUser = ref<User | null>(null);

const handleEdit = (user: User) => {
  selectedUser.value = user;
  showEditDialog.value = true;
};

const handleCloseEdit = () => {
  showEditDialog.value = false;
  selectedUser.value = null;
};

const handleDelete = (user: User) => {
  selectedUser.value = user;
  showDeleteDialog.value = true;
};

const handleCloseDelete = () => {
  showDeleteDialog.value = false;
  selectedUser.value = null;
};

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return sortedUsers.value.slice(start, end);
});

const totalPages = computed(() => Math.ceil(props.users.length / itemsPerPage.value));

const sortBy = (key: keyof User) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = key;
    sortOrder.value = 'asc';
  }
};

const goToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

const displayRole = (role: string) => {
    if (role.toLowerCase() === 'official') {
        return 'Barangay Official';
    }
    return role;
};
</script>

<template>
    <div class="rounded-md border overflow-x-auto">
        <Table>
            <TableCaption class="pb-2">
                A list of all users in the system.
            </TableCaption>
            <TableHeader>
                <TableRow>
                    <TableHead @click="sortBy('name')" class="cursor-pointer min-w-[120px]">
                        <div class="flex items-center">
                            <span>Name</span>
                            <ChevronUp v-if="sortKey === 'name' && sortOrder === 'asc'" class="h-4 w-4 ml-1" />
                            <ChevronDown v-if="sortKey === 'name' && sortOrder === 'desc'" class="h-4 w-4 ml-1" />
                        </div>
                    </TableHead>
                    <TableHead @click="sortBy('email')" class="cursor-pointer min-w-[180px] hidden sm:table-cell">
                        <div class="flex items-center">
                            <span>Email</span>
                            <ChevronUp v-if="sortKey === 'email' && sortOrder === 'asc'" class="h-4 w-4 ml-1" />
                            <ChevronDown v-if="sortKey === 'email' && sortOrder === 'desc'" class="h-4 w-4 ml-1" />
                        </div>
                    </TableHead>
                    <TableHead @click="sortBy('role')" class="cursor-pointer min-w-[100px]">
                        <div class="flex items-center">
                            <span>Role</span>
                            <ChevronUp v-if="sortKey === 'role' && sortOrder === 'asc'" class="h-4 w-4 ml-1" />
                            <ChevronDown v-if="sortKey === 'role' && sortOrder === 'desc'" class="h-4 w-4 ml-1" />
                        </div>
                    </TableHead>
                    <TableHead @click="sortBy('status')" class="cursor-pointer min-w-[90px] hidden md:table-cell">
                        <div class="flex items-center">
                            <span>Status</span>
                            <ChevronUp v-if="sortKey === 'status' && sortOrder === 'asc'" class="h-4 w-4 ml-1" />
                            <ChevronDown v-if="sortKey === 'status' && sortOrder === 'desc'" class="h-4 w-4 ml-1" />
                        </div>
                    </TableHead>
                    <TableHead @click="sortBy('createdAt')" class="cursor-pointer min-w-[110px] hidden lg:table-cell">
                        <div class="flex items-center">
                            <span>Created At</span>
                            <ChevronUp v-if="sortKey === 'createdAt' && sortOrder === 'asc'" class="h-4 w-4 ml-1" />
                            <ChevronDown v-if="sortKey === 'createdAt' && sortOrder === 'desc'" class="h-4 w-4 ml-1" />
                        </div>
                    </TableHead>
                    <TableHead class="w-[60px]">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="user in paginatedUsers" :key="user.id">
                    <TableCell class="font-medium">
                        <div class="flex flex-col sm:hidden">
                            <span>{{ user.name }}</span>
                            <span class="text-sm text-muted-foreground">{{ user.email }}</span>
                        </div>
                        <span class="hidden sm:block">{{ user.name }}</span>
                    </TableCell>
                    <TableCell class="hidden sm:table-cell">{{ user.email }}</TableCell>
                   <TableCell>
                        <span 
                            class="capitalize inline-flex items-center rounded-md bg-blue-700/20 px-2 py-1 text-xs font-medium text-blue-500 ring-1 ring-inset ring-blue-700/40 dark:bg-blue-900/40 dark:text-blue-300">
                            {{ displayRole(user.role) }}
                        </span>
                    </TableCell>

                    <TableCell>
                        <span 
                            class="capitalize inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset"
                            :class="{
                                'bg-green-700 text-green-500 ring-green-600 dark:bg-green-900 dark:text-green-300': user.status === 'active',
                                'bg-red-700 text-red-500 ring-red-600 dark:bg-red-900 dark:text-red-300': user.status === 'inactive'
                            }"
                        >
                            {{ user.status }}
                        </span>
                    </TableCell>
                    <TableCell class="hidden lg:table-cell text-muted-foreground">
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
                                <DropdownMenuItem @click="handleEdit(user)">
                                    <Pencil class="mr-2 h-4 w-4" />
                                    <span>Edit</span>
                                </DropdownMenuItem>
                                <DropdownMenuItem>
                                    <KeyRound class="mr-2 h-4 w-4" />
                                    <span>Generate Pass</span>
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="handleDelete(user)" class="text-destructive">
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    <span>Delete</span>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>       
        <div class="flex justify-center pb-2">
            <Pagination>
                <PaginationList class="flex items-center space-x-1">
                    <PaginationFirst @click="goToPage(1)" :disabled="currentPage === 1" />
                    <PaginationPrev @click="goToPage(currentPage - 1)" :disabled="currentPage === 1" />
                    
                    <div class="flex items-center space-x-1 px-1">
                        <template v-for="page in totalPages" :key="page">
                            <PaginationListItem
                                :value="String(page)"
                                :disabled="false"
                                @click="goToPage(page)"
                                :active="currentPage === page"
                            >
                                {{ page }}
                            </PaginationListItem>
                        </template>
                    </div>

                    <PaginationNext @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages" />
                    <PaginationLast @click="goToPage(totalPages)" :disabled="currentPage === totalPages" />
                </PaginationList>
            </Pagination>
        </div>
        <EditUser 
            v-if="selectedUser"
            :user="selectedUser"
            :show="showEditDialog"
            @close="handleCloseEdit"
        />
        <DeleteUser 
            v-if="selectedUser"
            :user="selectedUser"
            :show="showDeleteDialog"
            @close="handleCloseDelete"
        />
    </div>
</template>