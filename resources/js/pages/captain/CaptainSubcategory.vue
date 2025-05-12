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
import CreateSubcategory from '@/components/captain/CreateSubcategory.vue';
import EditSubcategory from '@/components/captain/EditSubcategory.vue';
import Confirmation from '@/components/Confirmation.vue';
import { useToast } from '@/components/ui/toast/use-toast';

interface Category {
  id: number;
  name: string;
}

interface Subcategory {
  id: number;
  category_id: number;
  name: string;
  description: string;
  status: 'active' | 'inactive';
  created_at: string;
  category: {
    name: string;
  };
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Subcategories',
    href: route('captain.subcategories'),
  },
];

const props = defineProps<{
  subcategories: {
    data: Subcategory[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  categories: { data: Category[] };
  search?: string;
}>();

const searchQuery = ref(props.search ?? '');
const showCreateDialog = ref(false);
const showEditModal = ref(false);
const showDeleteConfirmation = ref(false);
const selectedSubcategory = ref<Subcategory | null>(null);
const { toast } = useToast();

const form = useForm({});

const handleCloseCreate = () => {
  showCreateDialog.value = false;
};

const handleEdit = (subcategory: Subcategory) => {
  selectedSubcategory.value = subcategory;
  showEditModal.value = true;
};

const handleDelete = (subcategory: Subcategory) => {
  selectedSubcategory.value = subcategory;
  showDeleteConfirmation.value = true;
};

const confirmDelete = () => {
  if (selectedSubcategory.value) {
    form.delete(route('captain.subcategories.destroy', selectedSubcategory.value.id), {
      onSuccess: () => {
        showDeleteConfirmation.value = false;
        selectedSubcategory.value = null;
        toast({
          title: "Success",
          description: "Subcategory deleted successfully",
          variant: "success",
        });
      },
      onError: () => {
        toast({
          title: "Error",
          description: "Failed to delete subcategory",
          variant: "destructive",
        });
      }
    });
  }
};

watch(searchQuery, (query) => {
  router.get(
    route('captain.subcategories'),
    { search: query },
    { preserveState: true, replace: true }
  );
});

const handlePageChange = (page: number) => {
  router.get(
    route('captain.subcategories'),
    { page, search: searchQuery.value },
    { preserveState: true }
  );
};
</script>

<template>
  <Head title="Subcategories" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-3 p-6">
      <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-2 flex-1">
          <div class="relative w-full sm:w-[300px]">
            <SearchIcon class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input
              v-model="searchQuery"
              placeholder="Search subcategories..."
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
          Add Subcategory
        </Button>
      </div>

      <CreateSubcategory 
        v-if="showCreateDialog"
        :show="showCreateDialog"
        :categories="categories.data"
        @close="handleCloseCreate"
      />

      <EditSubcategory
        v-if="selectedSubcategory"
        :show="showEditModal"
        :subcategory="selectedSubcategory"
        :categories="categories.data"
        @close="showEditModal = false"
      />

      <Confirmation
        :show="showDeleteConfirmation"
        title="Delete Subcategory"
        description="Are you sure you want to delete this subcategory? This action cannot be undone."
        @confirm="confirmDelete"
        @cancel="showDeleteConfirmation = false"
      />

      <div class="rounded-md border overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Name</TableHead>
              <TableHead>Parent Category</TableHead>
              <TableHead>Description</TableHead>
              <TableHead>Status</TableHead>
              <TableHead class="hidden lg:table-cell">Created At</TableHead>
              <TableHead class="w-[100px]">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="subcategories.data.length">
              <TableRow
                v-for="subcategory in subcategories.data"
                :key="subcategory.id"
                class="cursor-pointer hover:bg-muted/50 transition-colors"
              >
                <TableCell class="font-medium">{{ subcategory.name }}</TableCell>
                <TableCell>
                  <Badge variant="secondary">
                    {{ subcategory.category.name }}
                  </Badge>
                </TableCell>
                <TableCell>{{ subcategory.description }}</TableCell>
                <TableCell>
                  <Badge
                    :variant="subcategory.status === 'active' ? 'success' : 'destructive'"
                    class="capitalize"
                  >
                    {{ subcategory.status }}
                  </Badge>
                </TableCell>
                <TableCell class="hidden lg:table-cell text-muted-foreground">
                  {{ new Date(subcategory.created_at).toLocaleDateString() }}
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
                      <DropdownMenuItem @click="handleEdit(subcategory)">
                        <Pencil class="mr-2 h-4 w-4" />
                        <span>Edit</span>
                      </DropdownMenuItem>
                      <DropdownMenuItem @click="handleDelete(subcategory)" class="text-destructive">
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
                      <span class="text-muted-foreground font-medium">No subcategories found</span>
                      <span class="text-sm text-muted-foreground/70">Subcategories will appear here once created</span>
                    </div>
                  </div>
                </TableCell>
              </TableRow>
            </template>
          </TableBody>
        </Table>

        <DataTablePagination
          v-if="subcategories.data.length > 0"
          :current-page="subcategories.current_page"
          :total-pages="subcategories.last_page"
          :on-page-change="handlePageChange"
        />
      </div>
    </div>
  </AppLayout>
</template>