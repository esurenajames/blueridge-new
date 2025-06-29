<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Input } from '@/components/ui/input';
import { Search, Phone, Mail, MapPin, Building2 } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import DataTablePagination from '@/components/DataTablePagination.vue'; // <-- Use your pagination component

interface Company {
  id: number;
  company_name: string;
  contact_person: string;
  address: string;
  contact_number: string;
  email: string;
}

const props = defineProps<{
  show: boolean;
}>();

const emit = defineEmits<{
  'update:show': [value: boolean];
  'select-company': [company: Company];
}>();

const companies = ref<Company[]>([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const loading = ref(false);
const searchQuery = ref('');

async function fetchCompanies(page = 1) {
  loading.value = true;
  try {
    const response = await fetch(`/companies/fetch?page=${page}`);
    const data = await response.json();
    companies.value = data.data;
    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      total: data.total,
    };
  } catch (e) {
    companies.value = [];
  }
  loading.value = false;
}

watch(
  () => props.show,
  (newVal) => {
    if (newVal) {
      fetchCompanies(pagination.value.current_page);
    }
  }
);

const filteredCompanies = computed(() => {
  if (!searchQuery.value) return companies.value;
  const query = searchQuery.value.toLowerCase();
  return companies.value.filter(company =>
    company.company_name.toLowerCase().includes(query) ||
    company.contact_person.toLowerCase().includes(query) ||
    company.email.toLowerCase().includes(query) ||
    company.address.toLowerCase().includes(query)
  );
});

const selectCompany = (company: Company) => {
  emit('select-company', company);
  emit('update:show', false);
};

const goToPage = (page: number) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchCompanies(page);
  }
};
</script>

<template>
  <Dialog :open="show" @update:open="(value) => emit('update:show', value)">
    <DialogContent class="max-w-5xl h-[80vh] flex flex-col overflow-hidden">
      <DialogHeader class="pb-6 flex-none">
        <DialogTitle class="flex items-center gap-2">
          <Building2 class="h-5 w-5" />
          Existing Companies
        </DialogTitle>
        <DialogDescription>
          Select from a list of existing companies to add to your project
        </DialogDescription>
      </DialogHeader>

      <!-- Search Bar -->
      <div class="flex-none mb-4">
        <div class="relative">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input
            v-model="searchQuery"
            placeholder="Search companies by name, contact person, email, or address..."
            class="pl-10"
          />
        </div>
      </div>

      <!-- Companies Table -->
      <div class="flex-1 overflow-hidden">
        <ScrollArea class="h-full">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Company</TableHead>
                <TableHead>Contact Person</TableHead>
                <TableHead>Contact Information</TableHead>
                <TableHead>Address</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow 
                v-for="company in filteredCompanies" 
                :key="company.id"
                class="hover:bg-muted/50 cursor-pointer"
                @click="selectCompany(company)"
              >
                <TableCell class="font-medium">
                  <div class="flex items-center gap-2">
                    <Building2 class="h-4 w-4 text-muted-foreground" />
                    {{ company.company_name }}
                  </div>
                </TableCell>
                <TableCell>{{ company.contact_person }}</TableCell>
                <TableCell>
                  <div class="space-y-1">
                    <div class="flex items-center gap-1 text-sm">
                      <Phone class="h-3 w-3 text-muted-foreground" />
                      {{ company.contact_number }}
                    </div>
                    <div class="flex items-center gap-1 text-sm">
                      <Mail class="h-3 w-3 text-muted-foreground" />
                      {{ company.email }}
                    </div>
                  </div>
                </TableCell>
                <TableCell>
                  <div class="flex items-start gap-1 max-w-xs">
                    <MapPin class="h-3 w-3 text-muted-foreground mt-0.5 flex-shrink-0" />
                    <span class="text-sm text-muted-foreground">{{ company.address }}</span>
                  </div>
                </TableCell>
              </TableRow>
              <TableRow v-if="filteredCompanies.length === 0">
                <TableCell colspan="7" class="text-center py-8 text-muted-foreground">
                  <span v-if="loading">Loading companies...</span>
                  <span v-else>No companies found matching your search criteria</span>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </ScrollArea>
      </div>

      <!-- Pagination Controls -->
      <div class="flex-none pt-4 border-t flex items-center justify-between">
        <p class="text-sm text-muted-foreground">
          Showing {{ filteredCompanies.length }} of {{ pagination.total }} companies
        </p>
        <DataTablePagination
          v-if="pagination.total > 0"
          :current-page="pagination.current_page"
          :total-pages="pagination.last_page"
          :on-page-change="goToPage"
        />
      </div>
    </DialogContent>
  </Dialog>
</template>