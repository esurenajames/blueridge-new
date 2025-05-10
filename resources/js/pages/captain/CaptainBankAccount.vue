<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { InboxIcon, MoreHorizontal, Pencil, Trash2, PlusIcon, SearchIcon } from 'lucide-vue-next';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import DataTablePagination from '@/components/DataTablePagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ref, watch } from 'vue';
import CreateBank from '@/components/captain/CreateBank.vue';
import EditBank from '@/components/captain/EditBank.vue';
import Confirmation from '@/components/Confirmation.vue';

interface BankAccount {
  id: number;
  name: string;
  bank: string;
  account_number: string;
  status: 'active' | 'inactive';
  created_at: string;
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Bank Accounts',
    href: route('captain.bank-accounts'),
  },
];

const props = defineProps<{
  accounts: {
    data: BankAccount[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  search?: string;
}>();

const searchQuery = ref(props.search ?? '');
const showCreateDialog = ref(false);
const showEditModal = ref(false);
const showDeleteConfirmation = ref(false);
const selectedBank = ref<BankAccount | null>(null);

const form = useForm({});

const handleCloseCreate = () => {
  showCreateDialog.value = false;
};

const handleEdit = (account: BankAccount) => {
  selectedBank.value = account;
  showEditModal.value = true;
};

const handleDelete = (account: BankAccount) => {
  selectedBank.value = account;
  showDeleteConfirmation.value = true;
};

const confirmDelete = () => {
  if (selectedBank.value) {
    form.delete(route('captain.bank-accounts.destroy', selectedBank.value.id), {
      onSuccess: () => {
        showDeleteConfirmation.value = false;
        selectedBank.value = null;
      },
    });
  }
};

watch(searchQuery, (query) => {
  router.get(
    route('captain.bank-accounts'),
    { search: query },
    { preserveState: true, replace: true }
  );
});

const handlePageChange = (page: number) => {
  router.get(
    route('captain.bank-accounts'),
    { page, search: searchQuery.value },
    { preserveState: true }
  );
};
</script>

<template>
  <Head title="Bank Accounts" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-3 p-6">
      <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-2 flex-1">
          <div class="relative w-full sm:w-[300px]">
            <SearchIcon class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input
              v-model="searchQuery"
              placeholder="Search bank accounts..."
              class="pl-8"
            />
          </div>
        </div>
        <Button
          variant="default"
          class="flex items-center gap-2"
          @click="showCreateDialog = true"
        >
          <PlusIcon class="h-4 w-4" />
          Add Bank Account
        </Button>
      </div>

      <CreateBank 
        v-if="showCreateDialog"
        :show="showCreateDialog"
        @close="handleCloseCreate"
      />

      <EditBank
        v-if="selectedBank"
        :show="showEditModal"
        :bank="selectedBank"
        @close="showEditModal = false"
      />

      <Confirmation
        :show="showDeleteConfirmation"
        title="Delete Bank Account"
        description="Are you sure you want to delete this bank account? This action cannot be undone."
        @confirm="confirmDelete"
        @cancel="showDeleteConfirmation = false"
      />

      <div class="rounded-md border overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Name</TableHead>
              <TableHead>Bank</TableHead>
              <TableHead>Account Number</TableHead>
              <TableHead>Status</TableHead>
              <TableHead class="hidden lg:table-cell">Created At</TableHead>
              <TableHead class="w-[100px]">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="accounts.data.length">
              <TableRow
                v-for="account in accounts.data"
                :key="account.id"
                class="cursor-pointer hover:bg-muted/50 transition-colors"
              >
                <TableCell class="font-medium">{{ account.name }}</TableCell>
                <TableCell>{{ account.bank }}</TableCell>
                <TableCell>{{ account.account_number }}</TableCell>
                <TableCell>
                  <Badge
                    :variant="account.status === 'active' ? 'success' : 'destructive'"
                    class="capitalize"
                  >
                    {{ account.status }}
                  </Badge>
                </TableCell>
                <TableCell class="hidden lg:table-cell text-muted-foreground">
                  {{ new Date(account.created_at).toLocaleDateString() }}
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
                      <DropdownMenuItem @click="handleEdit(account)">
                        <Pencil class="mr-2 h-4 w-4" />
                        <span>Edit</span>
                      </DropdownMenuItem>
                      <DropdownMenuItem @click="handleDelete(account)" class="text-destructive">
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
                <TableCell colspan="6" class="h-32">
                  <div class="flex flex-col items-center justify-center gap-4">
                    <InboxIcon class="h-8 w-8 text-muted-foreground/50" />
                    <div class="flex flex-col items-center gap-1">
                      <span class="text-muted-foreground font-medium">No bank accounts found</span>
                      <span class="text-sm text-muted-foreground/70">Bank accounts will appear here once created</span>
                    </div>
                  </div>
                </TableCell>
              </TableRow>
            </template>
          </TableBody>
        </Table>

        <DataTablePagination
          v-if="accounts.data.length > 0"
          :current-page="accounts.current_page"
          :total-pages="accounts.last_page"
          :on-page-change="handlePageChange"
        />
      </div>
    </div>
  </AppLayout>
</template>