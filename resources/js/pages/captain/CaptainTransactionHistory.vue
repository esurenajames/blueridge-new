<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { InboxIcon, SearchIcon, CalendarIcon, ChevronDownIcon } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTablePagination from '@/components/DataTablePagination.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { format } from 'date-fns';
import type { DateRange } from 'reka-ui'
import { RangeCalendar } from '@/components/ui/range-calendar'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger, DropdownMenuItem } from '@/components/ui/dropdown-menu'

interface TransactionFile {
  id: number;
  name: string;
  path: string;
  size: number;
  file_type: string;
  url: string;
}

interface Transaction {
  id: number;
  request_name?: string | null;
  subcategory_name?: string | null;
  transaction_date: string;
  type: string;
  total_amount: string | number;
  processed_by?: string | number;
  files?: TransactionFile[];
}

interface TransactionsPaginated {
  data: Transaction[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

const props = defineProps<{
  transactions: TransactionsPaginated;
  search?: string;
  type?: string;
  date_from?: string;
  date_to?: string;
}>();

const breadcrumbs = [
  {
    title: 'Transaction History',
    href: '#',
  },
];

const transactions = computed(() => props.transactions.data);

const searchQuery = ref(props.search ?? '');
const typeFilter = ref(props.type ?? '');
const dateRange = ref<DateRange>({
  from: props.date_from ? new Date(props.date_from) : undefined,
  to: props.date_to ? new Date(props.date_to) : undefined
});

watch([searchQuery, typeFilter, dateRange], ([search, type, dates]) => {
  router.get(
    route('captain.transactions'),
    {
      search,
      type,
      date_from: dates.from ? format(dates.from, 'yyyy-MM-dd') : '',
      date_to: dates.to ? format(dates.to, 'yyyy-MM-dd') : '',
    },
    { preserveState: true, replace: true }
  );
});

const handlePageChange = (page: number) => {
  router.get(
    route('captain.transactions'),
    {
      page,
      search: searchQuery.value,
      type: typeFilter.value,
      date_from: dateRange.value.from ? format(dateRange.value.from, 'yyyy-MM-dd') : '',
      date_to: dateRange.value.to ? format(dateRange.value.to, 'yyyy-MM-dd') : '',
    },
    { preserveState: true }
  );
};

const formatAmount = (amount: string | number | undefined | null) => {
  const num = typeof amount === 'string' ? parseFloat(amount) : amount;
  if (typeof num !== 'number' || isNaN(num)) return '0.00';
  return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const typeOptions = [
  { value: '', label: 'All Types' },
  { value: 'expenses', label: 'Expenses' },
  { value: 'income', label: 'Income' },
  { value: 'proposed budget', label: 'Proposed Budget' },
];

// For calendar dropdown
import { onClickOutside } from '@vueuse/core'
import { ref as vueRef } from 'vue'
const calendarOpen = ref(false)
const calendarRef = vueRef<HTMLElement | null>(null)
onClickOutside(calendarRef, () => { calendarOpen.value = false })

// Preview dialog state
const showPreviewDialog = ref(false);
const selectedPreview = ref<{ url: string; type: string } | null>(null);

function openPreview(file: { url: string; file_type: string }) {
  selectedPreview.value = { url: file.url, type: file.file_type === 'pdf' ? 'application/pdf' : 'image' };
  showPreviewDialog.value = true;
}

function closePreview() {
  selectedPreview.value = null;
  showPreviewDialog.value = false;
}
</script>

<template>
  <Head title="Transaction History" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-3 p-6">
      <div class="flex flex-col lg:flex-row items-start justify-between gap-4">
        <!-- Left Section: Search -->
        <div class="flex flex-col gap-4 w-full lg:w-auto">
          <div class="relative w-full sm:w-[300px]">
            <SearchIcon class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input
              v-model="searchQuery"
              placeholder="Search transactions..."
              class="pl-8"
            />
          </div>
        </div>

        <!-- Right Section: Type Filter Dropdown and Date Range -->
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto items-center">
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="outline" class="w-[200px] justify-between">
                {{ typeFilter ? typeOptions.find(t => t.value === typeFilter)?.label : 'All Types' }}
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

          <!-- Date Range Picker Dropdown -->
          <div class="relative w-full sm:w-[300px]" ref="calendarRef">
            <Button
              variant="outline"
              class="w-full justify-between"
              @click="calendarOpen = !calendarOpen"
            >
              <span class="flex items-center gap-2">
                <CalendarIcon class="h-4 w-4" />
                <span>
                  {{
                    dateRange.from && dateRange.to
                      ? `${format(dateRange.from, 'MMM d, yyyy')} - ${format(dateRange.to, 'MMM d, yyyy')}`
                      : 'Select Date Range'
                  }}
                </span>
              </span>
            </Button>
            <div
              v-if="calendarOpen"
              class="absolute z-20 mt-2 w-full bg-gray-50 dark:bg-black border rounded-md shadow-lg p-4"
            >
              <RangeCalendar
                v-model="dateRange"
                class="rounded-md"
                :number-of-months="1"
                @update:model-value="val => { 
                  dateRange.from = val.start; 
                  dateRange.to = val.end; 
                  calendarOpen = false; 
                }"
              />
              <div class="flex justify-end mt-2">
                <Button size="sm" variant="ghost" @click="dateRange.from = undefined; dateRange.to = undefined; calendarOpen = false">
                  Clear
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="rounded-md border overflow-x-auto mt-2">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Date</TableHead>
              <TableHead>Request Name</TableHead>
              <TableHead>Subcategory</TableHead>
              <TableHead>Type</TableHead>
              <TableHead class="text-right">Amount</TableHead>
              <TableHead>Files</TableHead>
              <TableHead>Processed By</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="transactions.length">
              <TableRow v-for="transaction in transactions" :key="transaction.id">
                <TableCell>
                  {{ new Date(transaction.transaction_date).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                  }) }}
                </TableCell>
                <TableCell>
                  {{ transaction.request_name ?? '-' }}
                </TableCell>
                <TableCell>
                  {{ transaction.subcategory_name ?? '-' }}
                </TableCell>
                <TableCell>
                  <Badge
                    :variant="transaction.type === 'income' ? 'success' : transaction.type === 'expenses' ? 'destructive' : 'secondary'"
                    class="capitalize"
                  >
                    {{ transaction.type }}
                  </Badge>
                </TableCell>
                <TableCell class="text-right tabular-nums">
                  <span
                    :class="{
                      'text-green-600': transaction.type === 'income',
                      'text-red-600': transaction.type === 'expenses',
                      'text-blue-600': transaction.type === 'proposed budget'
                    }"
                  >
                    ₱{{ formatAmount(transaction.total_amount) }}
                  </span>
                </TableCell>
                <TableCell>
                  <template v-if="transaction.files && transaction.files.length">
                    <template v-if="transaction.files[0].file_type === 'image'">
                      <img
                        :src="transaction.files[0].url"
                        alt="Receipt"
                        class="w-12 h-12 object-cover rounded border cursor-pointer transition-transform hover:scale-105"
                        @click="openPreview(transaction.files[0])"
                      />
                    </template>
                    <template v-else-if="transaction.files[0].file_type === 'pdf'">
                      <a
                        :href="transaction.files[0].url"
                        target="_blank"
                        class="inline-flex items-center gap-1 text-blue-600 hover:underline"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        PDF
                      </a>
                    </template>
                  </template>
                  <template v-else>
                    <span class="text-xs text-gray-400">No file</span>
                  </template>
                </TableCell>
                <TableCell>{{ transaction.processed_by ?? '-' }}</TableCell>
              </TableRow>
            </template>
            <template v-else>
              <TableRow>
                <TableCell colspan="7" class="h-32">
                  <div class="flex flex-col items-center justify-center gap-4">
                    <InboxIcon class="h-8 w-8 text-muted-foreground/50" />
                    <div class="flex flex-col items-center gap-1">
                      <span class="text-muted-foreground font-medium">No transactions found</span>
                      <span class="text-sm text-muted-foreground/70">Transaction history will appear here</span>
                    </div>
                  </div>
                </TableCell>
              </TableRow>
            </template>
          </TableBody>
        </Table>

        <DataTablePagination
          v-if="props.transactions.data.length > 0"
          :current-page="props.transactions.current_page"
          :total-pages="props.transactions.last_page"
          :on-page-change="handlePageChange"
        />
      </div>
    </div>

    <Dialog v-model:open="showPreviewDialog" @update:open="closePreview">
      <DialogContent class="sm:max-w-[900px] sm:max-h-[80vh]">
        <div class="relative w-full h-full max-h-[60vh] overflow-auto">
          <img 
            v-if="selectedPreview?.type === 'image'"
            :src="selectedPreview?.url" 
            class="w-full h-auto"
            alt="Receipt preview" 
          />
          <iframe
            v-else-if="selectedPreview?.type === 'application/pdf'"
            :src="selectedPreview?.url"
            class="w-full h-full min-h-[500px]"
            type="application/pdf"
          ></iframe>
        </div>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>