<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';
import { ChevronDown } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import type { Quotation } from '@/types';

const props = defineProps<{
  show: boolean;
  quotation: Quotation;
}>();

const emit = defineEmits<{
  'update:show': [value: boolean];
}>();

const formatNumber = (num: number) => {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};

const calculateTotal = (items: Quotation['details'][0]['items']) => {
  return items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
};

const expandedItems = ref<Set<number>>(new Set());

const toggleExpand = (index: number) => {
  expandedItems.value.has(index) 
    ? expandedItems.value.delete(index)
    : expandedItems.value.add(index);
};

// Computed property for selected company
const selectedDetail = computed(() =>
  props.quotation.details.find(detail => detail.is_selected)
);
</script>

<template>
  <Dialog :open="show" @update:open="(value) => emit('update:show', value)">
    <DialogContent class="max-w-3xl h-[70vh] flex flex-col overflow-hidden">
      <DialogHeader class="pb-6 flex-none">
        <DialogTitle>Quotation Details</DialogTitle>
        <DialogDescription>
          View quotation details from different companies
        </DialogDescription>
      </DialogHeader>

      <ScrollArea class="flex-1 px-1 pb-6 h-full">
        <div class="space-y-6 flex flex-col">
          <div 
            v-for="(detail, index) in quotation.details" 
            :key="index" 
            class="space-y-4 p-4 rounded-md hover:bg-muted/80 cursor-pointer transition-colors"
            :class="detail.is_selected ? 'border border-green-500 bg-green-50 dark:bg-green-900/20' : 'bg-muted'"
            @click="toggleExpand(index)"
          >
            <div class="flex items-center justify-between">
              <div class="space-y-2">
                <div v-if="detail.is_selected" class="font-semibold text-green-700 dark:text-green-300">
                  ** Selected Quotation
                </div>
                <h4 class="text-base font-medium">{{ detail.company.company_name }}</h4>
                <div class="grid grid-cols-2 gap-2 text-sm">
                  <div>
                    <span class="text-muted-foreground">Contact Person:</span>
                    <span class="ml-2">{{ detail.company.contact_person }}</span>
                  </div>
                  <div>
                    <span class="text-muted-foreground">Email:</span>
                    <span class="ml-2">{{ detail.company.email }}</span>
                  </div>
                  <div>
                    <span class="text-muted-foreground">Contact:</span>
                    <span class="ml-2">{{ detail.company.contact_number }}</span>
                  </div>
                  <div>
                    <span class="text-muted-foreground">Address:</span>
                    <span class="ml-2">{{ detail.company.address }}</span>
                  </div>
                </div>
              </div>
              <ChevronDown 
                class="w-4 h-4 transition-transform duration-200 text-muted-foreground"
                :class="{ 'rotate-180': expandedItems.has(index) }"
              />
            </div>

            <!-- Items Table (Expandable) -->
            <div v-if="expandedItems.has(index)" class="relative">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Item</TableHead>
                    <TableHead>Description</TableHead>
                    <TableHead>Quantity</TableHead>
                    <TableHead>Unit Price</TableHead>
                    <TableHead>Total</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="(item, itemIndex) in detail.items" :key="itemIndex">
                    <TableCell>{{ item.item_name }}</TableCell>
                    <TableCell>{{ item.description }}</TableCell>
                    <TableCell>{{ item.quantity }}</TableCell>
                    <TableCell>₱{{ formatNumber(item.price) }}</TableCell>
                    <TableCell>₱{{ formatNumber(item.total) }}</TableCell>
                  </TableRow>
                  <TableRow class="font-medium">
                    <TableCell colSpan="4" class="text-right">Total Amount:</TableCell>
                    <TableCell>₱{{ formatNumber(calculateTotal(detail.items)) }}</TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </div>
        </div>
      </ScrollArea>
    </DialogContent>
  </Dialog>
</template>