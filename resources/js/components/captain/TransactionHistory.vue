<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ref, watch, watchEffect } from 'vue';
import { useInfiniteScroll } from '@vueuse/core';
import { Badge } from '@/components/ui/badge';

interface Transaction {
  id: number;
  date: string;
  type: string;
  amount: string;
  remarks: string | null;
  processed_by: string;
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
                <TableHead>Remarks</TableHead>
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
                      :variant="transaction.type === 'profit' ? 'success' : transaction.type === 'expenses' ? 'destructive' : 'secondary'"
                      class="capitalize"
                    >
                      {{ transaction.type }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-right tabular-nums">
                    <span :class="{
                      'text-green-600': transaction.type === 'profit',
                      'text-red-600': transaction.type === 'expenses',
                      'text-blue-600': transaction.type === 'proposed budget'
                    }">
                      ₱{{ formatAmount(transaction.amount) }}
                    </span>
                  </TableCell>
                  <TableCell class="text-gray-600">{{ transaction.remarks || '-' }}</TableCell>
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
    </DialogContent>
  </Dialog>
</template>