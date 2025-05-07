<script setup lang="ts">
import { ref, computed } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Badge } from '@/components/ui/badge';
import { Building, Check, ChevronDown, Mail, Phone, MapPin } from 'lucide-vue-next';

interface Item {
  name: string;
  description: string;
  price: number;
  quantity: number;
}

interface Company {
  id: number;
  companyName: string;
  contactPerson: string;
  address: string;
  contactNumber: string;
  email: string;
  items: Item[];
}

const props = defineProps<{
  show: boolean;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'approve', companyId: number): void;
}>();

const selectedCompany = ref<number | null>(null);
const expandedItems = ref<Set<number>>(new Set());

const toggleExpand = (companyId: number) => {
  expandedItems.value.has(companyId) 
    ? expandedItems.value.delete(companyId)
    : expandedItems.value.add(companyId);
};

const getCompanyTotal = (company: Company) => {
  return company.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
};

const lowestBidder = computed(() => {
  return companies.reduce((lowest, current) => {
    const currentTotal = getCompanyTotal(current);
    const lowestTotal = lowest ? getCompanyTotal(lowest) : Infinity;
    return currentTotal < lowestTotal ? current : lowest;
  }, null as Company | null);
});

// Dummy data for demonstration
const companies = [
  {
    id: 1,
    companyName: 'Company A',
    contactPerson: 'John Doe',
    address: '123 Main St',
    contactNumber: '123-456-7890',
    email: 'company.a@example.com',
    items: [
      { name: 'Item 1', description: 'Description 1', price: 100, quantity: 2 },
      { name: 'Item 2', description: 'Description 2', price: 200, quantity: 1 },
    ]
  },
  {
    id: 2,
    companyName: 'Company B',
    contactPerson: 'Jane Smith',
    address: '456 Oak St',
    contactNumber: '098-765-4321',
    email: 'company.b@example.com',
    items: [
      { name: 'Item 1', description: 'Description 1', price: 150, quantity: 2 },
      { name: 'Item 2', description: 'Description 2', price: 250, quantity: 1 },
    ]
  },
  {
    id: 3,
    companyName: 'Company C',
    contactPerson: 'Bob Wilson',
    address: '789 Pine St',
    contactNumber: '456-789-0123',
    email: 'company.c@example.com',
    items: [
      { name: 'Item 1', description: 'Description 1', price: 120, quantity: 2 },
      { name: 'Item 2', description: 'Description 2', price: 220, quantity: 1 },
    ]
  }
];
</script>

<template>
  <Dialog :open="show" @update:open="emit('close')">
    <DialogContent class="sm:max-w-[1000px] h-[88vh] flex flex-col">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          Approve Quotation
        </DialogTitle>
        <DialogDescription>
          Select a company quotation to approve
        </DialogDescription>
      </DialogHeader>

      <ScrollArea class="flex-1">
        <div class="px-1">
          <Table>
            <TableHeader>
              <TableRow class="hover:bg-transparent">
                <TableHead class="w-[250px]">Company</TableHead>
                <TableHead class="w-[180px]">Contact Person</TableHead>
                <TableHead>Contact Information</TableHead>
                <TableHead class="text-right w-[200px]">Total Amount</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <template v-for="company in companies" :key="company.id">
                <TableRow 
                  class="cursor-pointer transition-colors"
                  :class="{ 'bg-blue-100 dark:bg-blue-500/10': selectedCompany === company.id }"
                  @click="() => selectedCompany = company.id"
                >
                  <TableCell class="font-medium">
                    <div class="flex items-center gap-2">
                      <Building class="w-4 h-4" />
                      <span>{{ company.companyName }}</span>
                    </div>
                  </TableCell>
                  <TableCell>
                    <span class="font-medium text-sm">{{ company.contactPerson }}</span>
                  </TableCell>
                  <TableCell>
                    <div class="space-y-1">
                      <div class="flex items-center gap-2 text-sm text-muted-foreground">
                        <Mail class="w-3.5 h-3.5" />
                        {{ company.email }}
                      </div>
                      <div class="flex items-center gap-2 text-sm text-muted-foreground">
                        <Phone class="w-3.5 h-3.5" />
                        {{ company.contactNumber }}
                      </div>
                      <div class="flex items-center gap-2 text-sm text-muted-foreground">
                        <MapPin class="w-3.5 h-3.5" />
                        {{ company.address }}
                      </div>
                    </div>
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="flex items-center justify-end gap-2">
                      <span class="text-lg font-medium">
                        ₱{{ getCompanyTotal(company).toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}
                      </span>
                      <button 
                        class="p-1 hover:bg-muted rounded-full"
                        @click.stop="toggleExpand(company.id)"
                      >
                        <ChevronDown 
                          class="w-4 h-4 transition-transform duration-200"
                          :class="{ 'rotate-180': expandedItems.has(company.id) }"
                        />
                      </button>
                    </div>
                  </TableCell>
                </TableRow>

                <TableRow 
                  v-if="expandedItems.has(company.id)"
                  class="hover:bg-transparent"
                  :class="{ 'bg-blue-50 dark:bg-blue-500/10': selectedCompany === company.id }"
                >
                  <TableCell colspan="4" class="p-0">
                    <div class="bg-muted/30 px-4 py-3">
                      <Table>
                        <TableHeader>
                          <TableRow class="hover:bg-transparent">
                            <TableHead class="w-[200px]">Item</TableHead>
                            <TableHead>Description</TableHead>
                            <TableHead class="text-right w-[150px]">Unit Price</TableHead>
                            <TableHead class="text-right w-[100px]">Quantity</TableHead>
                            <TableHead class="text-right w-[150px]">Subtotal</TableHead>
                          </TableRow>
                        </TableHeader>
                        <TableBody>
                          <TableRow 
                            v-for="(item, index) in company.items" 
                            :key="index"
                            class="transition-colors hover:bg-muted/50"
                          >
                            <TableCell class="font-medium">{{ item.name }}</TableCell>
                            <TableCell>{{ item.description }}</TableCell>
                            <TableCell class="text-right">
                              ₱{{ item.price.toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}
                            </TableCell>
                            <TableCell class="text-right">{{ item.quantity }}</TableCell>
                            <TableCell class="text-right font-medium">
                              ₱{{ (item.price * item.quantity).toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}
                            </TableCell>
                          </TableRow>
                          <TableRow class="hover:bg-transparent border-t-2">
                            <TableCell colspan="4" class="text-right font-medium">Total Amount:</TableCell>
                            <TableCell class="text-right">
                              <span class="text-lg font-medium">
                                ₱{{ getCompanyTotal(company).toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}
                              </span>
                            </TableCell>
                          </TableRow>
                        </TableBody>
                      </Table>
                    </div>
                  </TableCell>
                </TableRow>
              </template>
            </TableBody>
          </Table>
        </div>
      </ScrollArea>

      <div class="flex justify-between items-center gap-2 pt-4 border-t">
        <div class="text-sm text-muted-foreground">
          {{ selectedCompany ? 'Company selected for approval' : 'Select a company to approve' }}
        </div>
        <div class="flex gap-2">
          <Button
            type="button"
            variant="outline"
            @click="emit('close')"
          >
            Cancel
          </Button>
          <Button
            type="button"
            :disabled="!selectedCompany"
            @click="selectedCompany && emit('approve', selectedCompany)"
          >
            Approve Selected
          </Button>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>