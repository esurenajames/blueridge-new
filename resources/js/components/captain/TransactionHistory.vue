<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ref, watch } from 'vue';
import { useInfiniteScroll } from '@vueuse/core';
import { Badge } from '@/components/ui/badge';

interface Transaction {
  id: number;
  date: string;
  type: string;
  amount: string;
  remarks: string | null;
  processed_by: string;
  files?: Array<{
    id: number;
    name: string;
    path: string;
    size: number;
    file_type: string;
    url: string;
  }>;
}

const props = defineProps<{
  show: boolean;
  transactions?: Transaction[];
  subcategoryName?: string;
}>();

const emit = defineEmits(['close']);

const PAGE_SIZE = 10;
const displayedTransactions = ref<Transaction[]>([]);
const currentPage = ref(1);
const hasMore = ref(true);
const isLoading = ref(false);
const el = ref<HTMLElement | null>(null);

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

// Initialize transactions when dialog opens
watch(() => props.show, (newVal) => {
  if (newVal && props.transactions?.length) {
    // Reset state
    displayedTransactions.value = [];
    currentPage.value = 1;
    hasMore.value = true;
    
    // Load initial batch
    const initialItems = props.transactions.slice(0, PAGE_SIZE);
    displayedTransactions.value = initialItems;
    hasMore.value = props.transactions.length > PAGE_SIZE;
  }
}, { immediate: true });

const loadMore = async () => {
  if (!props.transactions || isLoading.value || !hasMore.value) return;

  try {
    isLoading.value = true;

    const start = displayedTransactions.value.length;
    const end = start + PAGE_SIZE;
    const newItems = props.transactions.slice(start, end);

    if (newItems.length > 0) {
      displayedTransactions.value = [...displayedTransactions.value, ...newItems];
      hasMore.value = displayedTransactions.value.length < props.transactions.length;
    } else {
      hasMore.value = false;
    }
  } finally {
    setTimeout(() => {
      isLoading.value = false;
    }, 300);
  }
};

// Configure infinite scroll
useInfiniteScroll(
  el,
  () => {
    if (!isLoading.value && hasMore.value) {
      loadMore();
    }
  },
  { 
    distance: 10,
    throttle: 500
  }
);

const formatAmount = (amount: string) => {
  return parseFloat(amount).toLocaleString(undefined, { 
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
};
</script>

<template>
  <Dialog :open="show" @update:open="emit('close')">
    <DialogContent class="sm:max-w-[800px] max-h-[80vh]">
      <DialogHeader>
        <DialogTitle>Transaction History</DialogTitle>
        <DialogDescription class="text-gray-500">
          Viewing transactions for {{ subcategoryName || 'selected subcategory' }}
        </DialogDescription>
      </DialogHeader>
      
      <div class="mt-4 overflow-hidden flex flex-col max-h-[calc(80vh-120px)]">
        <div 
          ref="el" 
          class="overflow-y-auto flex-1 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent"
          style="max-height: 500px;" 
        >
          <Table>
            <TableHeader class="sticky top-0 z-10">
              <TableRow>
                <TableHead>Date</TableHead>
                <TableHead>Type</TableHead>
                <TableHead class="text-right">Amount</TableHead>
                <TableHead>Receipt</TableHead>
                <TableHead>Processed By</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <template v-if="displayedTransactions.length > 0">
                <TableRow 
                  v-for="transaction in displayedTransactions" 
                  :key="transaction.id"
                >
                  <TableCell>
                    {{ new Date(transaction.date).toLocaleDateString('en-US', {
                      year: 'numeric',
                      month: 'short',
                      day: 'numeric'
                    }) }}
                  </TableCell>
                  <TableCell>
                    <Badge
                      :variant="transaction.type === 'income' ? 'success' : transaction.type === 'expenses' ? 'destructive' : 'primary'"
                      class="capitalize"
                    >
                      {{ transaction.type }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-right tabular-nums">
                    <span :class="{
                      'text-green-600': transaction.type === 'income',
                      'text-red-600': transaction.type === 'expenses',
                      'text-blue-600': transaction.type === 'proposed budget'
                    }">
                      ₱{{ formatAmount(transaction.amount) }}
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
                  <TableCell>{{ transaction.processed_by }}</TableCell>
                </TableRow>
              </template>
              <template v-else>
                <TableRow>
                  <TableCell colspan="5" class="text-center py-8 text-gray-500">
                    <div class="flex flex-col items-center gap-2">
                      <span>No transactions found</span>
                      <span class="text-sm">Transaction history will appear here</span>
                    </div>
                  </TableCell>
                </TableRow>
              </template>
            </TableBody>
          </Table>

          <!-- Loading indicator -->
          <div v-if="isLoading" class="py-4 text-center text-gray-500">
            <div class="flex items-center justify-center gap-2">
              <div class="h-4 w-4 animate-spin rounded-full border-2 border-primary border-t-transparent"></div>
              <span class="text-sm text-gray-500 text-center">Loading more...</span>
            </div>
          </div>

          <!-- End of list indicator -->
          <div v-if="!hasMore && displayedTransactions.length > 0" class="py-4 text-center text-gray-500">
            <span class="text-sm text-gray-500 text-center">No more transactions to load</span>
          </div>
        </div>
        
        <div v-if="displayedTransactions.length > 0" class="py-3 px-4 border-t">
          <p class="text-sm text-gray-500 text-center">
            Showing {{ displayedTransactions.length }} out of {{ props.transactions?.length || 0 }} transactions
          </p>
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
    </DialogContent>
  </Dialog>
</template>