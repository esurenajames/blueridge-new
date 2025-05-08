<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { 
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem
} from '@/components/ui/dropdown-menu';
import { ScrollArea } from '@/components/ui/scroll-area';
import { ChevronDown, Check, MoreHorizontal, XCircle, RotateCcw } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import type { Quotation } from '@/types';

const props = defineProps<{
  show: boolean;
  quotation: Quotation;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'approve', companyId: number): void;
  (e: 'decline', companyId: number): void;
  (e: 'return', companyId: number): void;
}>();

const selectedCompany = ref<number | null>(null);
const expandedItems = ref<Set<number>>(new Set());

const toggleExpand = (index: number) => {
  expandedItems.value.has(index) 
    ? expandedItems.value.delete(index)
    : expandedItems.value.add(index);
};

const formatNumber = (num: number) => {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};

const calculateTotal = (items: Quotation['details'][0]['items']) => {
  return items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
};

const selectedCompanyName = computed(() => {
  if (!selectedCompany.value) return '';
  const company = props.quotation.details.find(d => d.company.id === selectedCompany.value);
  return company?.company.company_name ?? '';
});

type ActionType = 'decline' | 'return';

const handleAction = (action: ActionType) => {
  if (!selectedCompany.value) return;
  
  const actionMessages = {
    decline: {
      title: 'Decline Quotation',
      message: 'Are you sure you want to decline this quotation?'
    },
    return: {
      title: 'Return Quotation',
      message: 'Are you sure you want to return this quotation for revision?'
    }
  };

  // // Here you could add confirmation dialog if needed
  // emit(action, selectedCompany.value);
};
</script>

<template>
  <Dialog :open="show" @update:open="emit('close')">
    <DialogContent class="max-w-4xl h-[80vh] flex flex-col overflow-hidden">
      <DialogHeader class="pb-6 flex-none">
        <DialogTitle>Approve Quotation</DialogTitle>
        <DialogDescription>
          Select a company quotation to approve
        </DialogDescription>
      </DialogHeader>

      <ScrollArea class="flex-1 px-1 pb-6 h-full">
        <div class="space-y-6 flex flex-col">
          <div 
            v-for="(detail, index) in quotation.details" 
            :key="index" 
            class="space-y-4 p-4 bg-muted rounded-md hover:bg-muted/80 cursor-pointer transition-colors relative overflow-hidden"
            :class="{ 'border-t-4 border-blue-500': selectedCompany === detail.company.id }"
          >
            <div 
              class="flex items-center justify-between"
              @click="toggleExpand(index)"
            >
              <div class="space-y-2">
                <div class="flex items-center gap-4">
                  <div 
                    class="h-5 w-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors cursor-pointer"
                    :class="selectedCompany === detail.company.id ? 'bg-primary border-primary' : 'border-muted-foreground'"
                    @click.stop="selectedCompany = selectedCompany === detail.company.id ? null : detail.company.id"
                  >
                    <Check 
                      v-if="selectedCompany === detail.company.id"
                      class="h-4 w-4 text-primary-foreground"
                    />
                  </div>
                  <div class="flex items-center gap-3">
                    <h4 class="text-base font-medium">{{ detail.company.company_name }}</h4>
                    <div v-if="selectedCompany === detail.company.id" class="text-sm flex items-center gap-2">
                      <span class="px-2 py-0.5 bg-primary/10 rounded-md text-xs font-medium text-primary">
                        Selected
                      </span>
                    </div>
                  </div>
                </div>
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
                class="w-4 h-4 transition-transform duration-200 text-muted-foreground pointer-events-none"
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

            <!-- Show total when collapsed -->
            <div v-if="!expandedItems.has(index)" class="text-right text-sm font-medium">
              Total Amount: ₱{{ formatNumber(calculateTotal(detail.items)) }}
            </div>
          </div>
        </div>
      </ScrollArea>

      <div class="flex items-center justify-between pt-4 border-t">
        <div class="text-sm flex items-center gap-2">
          <span class="text-muted-foreground">Selected Company:</span>
          <span class="px-2 py-1 bg-primary/10 rounded-md font-medium">
            {{ selectedCompanyName || 'None' }}
          </span>
        </div>
        <div class="flex items-center gap-2">
          <Button
            variant="outline"
            @click="emit('close')"
          >
            Cancel
          </Button>
          <Button
            variant="default"
            :disabled="!selectedCompany"
            @click="selectedCompany && emit('approve', selectedCompany)"
          >
            Approve Selected
          </Button>
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="outline" size="icon">
                <MoreHorizontal class="h-4 w-4" />
                <span class="sr-only">More actions</span>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-42">
              <DropdownMenuItem
                class="text-destructive"
                @click="handleAction('decline')"
              >
                <XCircle class="h-4 w-4 mr-2" />
                Decline Quotation
              </DropdownMenuItem>
              <DropdownMenuItem
                @click="handleAction('return')"
              >
                <RotateCcw class="h-4 w-4 mr-2" />
                Return Quotation
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>