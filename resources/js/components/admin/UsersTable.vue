<script setup lang="ts">
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { MoreHorizontal } from 'lucide-vue-next';
import { Pagination, PaginationList, PaginationListItem, PaginationEllipsis, PaginationFirst, PaginationLast, PaginationNext, PaginationPrev } from '@/components/ui/pagination';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pencil, Trash2, KeyRound } from 'lucide-vue-next';
import EditUser from './EditUser.vue';
import DeleteUser from './DeleteUser.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import GenerateKey from './GenerateKey.vue';
import { FileX } from 'lucide-vue-next'; 
import { getDisplayRole } from '@/utils/roles';
import { router } from '@inertiajs/vue3';
import DataTablePagination from '@/components/DataTablePagination.vue';

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  status: 'active' | 'inactive';
  createdAt: string;
}

const props = defineProps<{
  users: {
    data: User[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}>();

const { getInitials } = useInitials();

const sortKey = ref<keyof User | null>(null);
const sortOrder = ref<'asc' | 'desc'>('asc');

const sortedUsers = computed(() => {
  if (!sortKey.value) return props.users.data;
  return [...props.users.data].sort((a, b) => {
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

const paginatedUsers = computed(() => sortedUsers.value);

const totalPages = computed(() => props.users.last_page);
const currentPage = computed(() => props.users.current_page);

const showEditDialog = ref(false);
const showDeleteDialog = ref(false);
const showGenerateKeyDialog = ref(false);
const selectedUser = ref<User | null>(null);

const handleEdit = (user: User) => {
  selectedUser.value = user;
  showEditDialog.value = true;
};

const handleDelete = (user: User) => {
  selectedUser.value = user;
  showDeleteDialog.value = true;
};

const handleGenerateKey = (user: User) => {
  selectedUser.value = user;
  showGenerateKeyDialog.value = true;
};

const handleCloseEdit = () => {
  showEditDialog.value = false;
  selectedUser.value = null;
};

const handleCloseDelete = () => {
  showDeleteDialog.value = false;
  selectedUser.value = null;
};

const handleCloseGenerateKey = () => {
  showGenerateKeyDialog.value = false;
  selectedUser.value = null;
};

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
    router.get(route(route().current() as string), { page }, { preserveScroll: true, preserveState: true });
  }
};

const getRoleBadgeVariant = (role: string) => {
  switch (role.toLowerCase()) {
    case 'admin':
      return 'default';
    case 'official':
      return 'primary';
    default:
      return 'primary';
  }
};

const getStatusBadgeVariant = (status: string) => {
  return status === 'active' ? 'primary' : 'destructive';
};
</script>

<template>
    <div class="rounded-md border overflow-x-auto">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="cursor-pointer min-w-[100px]">
                        <div class="flex items-center">
                            <span>Name</span>
                        </div>
                    </TableHead>
                    <TableHead class="cursor-pointer min-w-[100px] hidden sm:table-cell">
                        <div class="flex items-center">
                            <span>Email</span>
                        </div>
                    </TableHead>
                    <TableHead class="cursor-pointer min-w-[100px]">
                        <div class="flex items-center">
                            <span>Role</span>
                        </div>
                    </TableHead>
                    <TableHead class="cursor-pointer min-w-[100px] hidden md:table-cell">
                        <div class="flex items-center">
                            <span>Status</span>
                        </div>
                    </TableHead>
                    <TableHead class="cursor-pointer min-w-[100px] hidden lg:table-cell">
                        <div class="flex items-center">
                            <span>Created At</span>
                        </div>
                    </TableHead>
                    <TableHead class="w-[100px]">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <template v-if="paginatedUsers.length > 0">
                    <TableRow v-for="user in paginatedUsers" :key="user.id">
                        <TableCell class="font-medium">
                            <div class="flex flex-col sm:hidden">
                                <div class="flex items-center gap-2">
                                    <Avatar class="h-8 w-8">
                                        <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" />
                                        <AvatarFallback>{{ getInitials(user.name) }}</AvatarFallback>
                                    </Avatar>
                                    <div>
                                        <span>{{ user.name }}</span>
                                        <span class="text-sm text-muted-foreground block">{{ user.email }}</span>
                                    </div>
                                </div>
                            </div>
                            <!-- Existing desktop view -->
                            <div class="hidden sm:flex items-center gap-2">
                                <Avatar class="h-8 w-8">
                                    <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" />
                                    <AvatarFallback>{{ getInitials(user.name) }}</AvatarFallback>
                                </Avatar>
                                <span>{{ user.name }}</span>
                            </div>
                        </TableCell>
                        <TableCell class="hidden sm:table-cell">{{ user.email }}</TableCell>
                        <TableCell>
                            <Badge :variant="getRoleBadgeVariant(user.role)" class="capitalize">
                                {{ getDisplayRole(user.role) }}
                            </Badge>
                        </TableCell>
                        <TableCell>
                            <Badge :variant="getStatusBadgeVariant(user.status)" class="capitalize">
                                {{ user.status }}
                            </Badge>
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
                                    <DropdownMenuItem @click="handleGenerateKey(user)">
                                        <KeyRound class="mr-2 h-4 w-4" />
                                        <span>Generate Key</span>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="handleDelete(user)" class="text-destructive">
                                        <Trash2 class="mr-2 h-4 w-4" />
                                        <span>Delete</span>
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>
                </template>
                <template v-else>
                    <TableRow>
                        <TableCell colspan="6">
                            <div class="flex flex-col items-center justify-center py-8 gap-3">
                                <FileX class="h-12 w-12 text-muted-foreground" />
                                <p class="text-lg font-medium text-muted-foreground">No users found</p>
                                <p class="text-sm text-muted-foreground">Try adjusting your search criteria</p>
                            </div>
                        </TableCell>
                    </TableRow>
                </template>
            </TableBody>
        </Table>

        <DataTablePagination
            v-if="users.data.length > 0"
            :current-page="users.current_page"
            :total-pages="users.last_page"
            :on-page-change="goToPage"
        />

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
        <GenerateKey 
            v-if="selectedUser"
            :user="selectedUser"
            :show="showGenerateKeyDialog"
            @close="handleCloseGenerateKey"
        />
    </div>
</template>