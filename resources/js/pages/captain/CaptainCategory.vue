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
import CreateCategory from '@/components/captain/CreateCategory.vue';
import EditCategory from '@/components/captain/EditCategory.vue';
import Confirmation from '@/components/Confirmation.vue';
import { useToast } from '@/components/ui/toast/use-toast';

interface Category {
  id: number;
  name: string;
  description: string;
  group_name: 'Beginning Cash Balance' | 'Receipts' | 'Expenditures' | 'MOOE';
  position: number;
  status: 'active' | 'inactive';
  created_at: string;
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Categories',
    href: route('captain.categories'),
  },
];

const props = defineProps<{
  categories: {
    data: Category[];
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
const selectedCategory = ref<Category | null>(null);
const { toast } = useToast();
const form = useForm({});

const handleCloseCreate = () => {
  showCreateDialog.value = false;
};

const handleEdit = (category: Category) => {
  selectedCategory.value = category;
  showEditModal.value = true;
};

const handleDelete = (category: Category) => {
  selectedCategory.value = category;
  showDeleteConfirmation.value = true;
};

const confirmDelete = () => {
  if (selectedCategory.value) {
    form.delete(route('captain.categories.destroy', selectedCategory.value.id), {
      onSuccess: () => {
        showDeleteConfirmation.value = false;
        selectedCategory.value = null;
        toast({
          title: "Success",
          description: "Category deleted successfully",
          variant: "success",
        });
      },
      onError: () => {
        toast({
          title: "Error",
          description: "Failed to delete category",
          variant: "destructive",
        });
      }
    });
  }
};

watch(searchQuery, (query) => {
  router.get(
    route('captain.categories'),
    { search: query },
    { preserveState: true, replace: true }
  );
});

const handlePageChange = (page: number) => {
  router.get(
    route('captain.categories'),
    { page, search: searchQuery.value },
    { preserveState: true }
  );
};
</script>

<template>
  <Head title="Categories" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-3 p-6">
      <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-2 flex-1">
          <div class="relative w-full sm:w-[300px]">
            <SearchIcon class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input
              v-model="searchQuery"
              placeholder="Search categories..."
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
          Add Category
        </Button>
      </div>

      <CreateCategory 
        v-if="showCreateDialog"
        :show="showCreateDialog"
        @close="handleCloseCreate"
      />

      <EditCategory
        v-if="selectedCategory"
        :show="showEditModal"
        :category="selectedCategory"
        @close="showEditModal = false"
      />

      <Confirmation
        :show="showDeleteConfirmation"
        title="Delete Category"
        description="Are you sure you want to delete this category? This action cannot be undone."
        @confirm="confirmDelete"
        @cancel="showDeleteConfirmation = false"
      />

      <div class="rounded-md border overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Name</TableHead>
              <TableHead>Description</TableHead>
              <TableHead>Group Name</TableHead>
              <TableHead>Position</TableHead>
              <TableHead>Status</TableHead>
              <TableHead class="hidden lg:table-cell">Created At</TableHead>
              <TableHead class="w-[100px]">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="categories.data.length">
              <TableRow
                v-for="category in categories.data"
                :key="category.id"
                class="cursor-pointer hover:bg-muted/50 transition-colors"
              >
                <TableCell class="font-medium">{{ category.name }}</TableCell>
                <TableCell>{{ category.description }}</TableCell>
                <TableCell>{{ category.group_name }}</TableCell>
                <TableCell class="text-center">{{ category.position }}</TableCell>
                <TableCell>
                  <Badge
                    :variant="category.status === 'active' ? 'success' : 'destructive'"
                    class="capitalize"
                  >
                    {{ category.status }}
                  </Badge>
                </TableCell>
                <TableCell class="hidden lg:table-cell text-muted-foreground">
                  {{ new Date(category.created_at).toLocaleDateString() }}
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
                      <DropdownMenuItem @click="handleEdit(category)">
                        <Pencil class="mr-2 h-4 w-4" />
                        <span>Edit</span>
                      </DropdownMenuItem>
                      <DropdownMenuItem @click="handleDelete(category)" class="text-destructive">
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
                <TableCell colspan="7" class="h-32">
                  <div class="flex flex-col items-center justify-center gap-4">
                    <InboxIcon class="h-8 w-8 text-muted-foreground/50" />
                    <div class="flex flex-col items-center gap-1">
                      <span class="text-muted-foreground font-medium">No categories found</span>
                      <span class="text-sm text-muted-foreground/70">Categories will appear here once created</span>
                    </div>
                  </div>
                </TableCell>
              </TableRow>
            </template>
          </TableBody>
        </Table>

        <DataTablePagination
          v-if="categories.data.length > 0"
          :current-page="categories.current_page"
          :total-pages="categories.last_page"
          :on-page-change="handlePageChange"
        />
      </div>
    </div>
  </AppLayout>
</template>