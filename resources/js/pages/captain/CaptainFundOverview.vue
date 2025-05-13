<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type BudgetGroup, type Subcategory } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ChevronRight, ChevronDown, MoreHorizontal, PlusCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useToast } from '@/components/ui/toast/use-toast';
import AddProfit from '@/components/captain/AddProfit.vue';
import TransactionHistory from '@/components/captain/TransactionHistory.vue';

export interface Subcategory {
  id: number;
  name: string;
  proposedBudget: string;
  janJun: number;
  julDec: number;
  january: string;
  february: string;
  march: string;
  april: string;
  may: string;
  june: string;
  july: string;
  august: string;
  september: string;
  october: string;
  november: string;
  december: string;
  ytd: number;
  balance: string;
  profit: string;
}

export interface Category {
  id: number;
  name: string;
  position: number;
  subcategories: Subcategory[];
}

export interface BudgetGroup {
  group_name: 'Beginning Cash Balance' | 'Receipts' | 'Expenditures' | 'MOOE';
  categories: Category[];
}

const props = defineProps<{
  budgetGroups: {
    group_name: 'Beginning Cash Balance' | 'Receipts' | 'Expenditures' | 'MOOE';
    categories: Category[];
  }[]
}>();

const { toast } = useToast();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Fund Overview',
    href: '/fund-overview',
  },
];

const expandedFirstHalf = ref(false);
const expandedSecondHalf = ref(false);
const expandedCategories = ref<Set<number>>(new Set());
const showAddProfitDialog = ref(false);
const showTransactionHistory = ref(false);
const selectedSubcategory = ref<Subcategory | null>(null);

function toggleCategory(categoryId: number) {
  if (expandedCategories.value.has(categoryId)) {
    expandedCategories.value.delete(categoryId);
  } else {
    expandedCategories.value.add(categoryId);
  }
  expandedCategories.value = new Set(expandedCategories.value);
}

const handleAddProfit = (subcategory: Subcategory) => {
  selectedSubcategory.value = subcategory;
  showAddProfitDialog.value = true;
};

const handleCloseAddProfit = () => {
  showAddProfitDialog.value = false;
  selectedSubcategory.value = null;
};

const handleShowTransactions = (subcategory: Subcategory) => {
  if (!subcategory?.transactions?.length) {
    toast({
      title: "No transactions",
      description: `No transaction history found for ${subcategory.name}`,
      variant: "default"
    });
    return;
  }
  
  selectedSubcategory.value = subcategory;
  showTransactionHistory.value = true;
};

const handleCloseTransactions = () => {
  showTransactionHistory.value = false;
  selectedSubcategory.value = null;
};

const totals = computed(() => {
  let total = {
    proposedBudget: 0,
    janJun: 0,
    julDec: 0,
    ytd: 0,
    balance: 0,
    profit: 0
  };

  props.budgetGroups.forEach(group => {
    group.categories.forEach(category => {
      category.subcategories.forEach(sub => {
        total.proposedBudget += parseFloat(sub.proposedBudget) || 0;
        total.janJun += sub.janJun || 0;
        total.julDec += sub.julDec || 0;
        total.ytd += sub.ytd || 0;
        total.balance += parseFloat(sub.balance) || 0;
        total.profit += parseFloat(sub.profit) || 0;
      });
    });
  });

  return total;
});
</script>

<template>
  <Head title="Fund Overview" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-3 p-6">
      <div class="rounded-lg border shadow-sm overflow-x-auto bg-white">
        <Table>
          <TableHeader class="bg-gray-50 sticky top-0 z-10">
            <TableRow>
              <TableHead class="min-w-[200px]">Object of Expenditure</TableHead>
              <TableHead class="min-w-[150px] text-right">Proposed Budget</TableHead>
              <TableHead class="min-w-[120px] text-right">
                <div class="flex items-center justify-end gap-2">
                  <span>1st Half</span>
                  <Button 
                    variant="ghost" 
                    size="icon"
                    class="hover:bg-primary/10" 
                    @click="expandedFirstHalf = !expandedFirstHalf"
                  >
                    <ChevronRight v-if="!expandedFirstHalf" class="h-4 w-4 transition-transform duration-200" />
                    <ChevronDown v-else class="h-4 w-4 transition-transform duration-200" />
                  </Button>
                </div>
              </TableHead>
              <TableHead 
                v-if="expandedFirstHalf" 
                v-for="month in ['January', 'February', 'March', 'April', 'May', 'June']" 
                :key="month" 
                class="min-w-[100px] text-right"
              >
                {{ month }}
              </TableHead>
              <TableHead class="min-w-[120px] text-right">
                <div class="flex items-center justify-end gap-2">
                  <span>2nd Half</span>
                  <Button 
                    variant="ghost" 
                    size="icon"
                    class="hover:bg-primary/10" 
                    @click="expandedSecondHalf = !expandedSecondHalf"
                  >
                    <ChevronRight v-if="!expandedSecondHalf" class="h-4 w-4 transition-transform duration-200" />
                    <ChevronDown v-else class="h-4 w-4 transition-transform duration-200" />
                  </Button>
                </div>
              </TableHead>
              <TableHead 
                v-if="expandedSecondHalf" 
                v-for="month in ['July', 'August', 'September', 'October', 'November', 'December']" 
                :key="month" 
                class="min-w-[100px] text-right"
              >
                {{ month }}
              </TableHead>
              <TableHead class="min-w-[120px] text-right">YTD</TableHead>
              <TableHead class="min-w-[120px] text-right">Balance</TableHead>
              <TableHead class="min-w-[120px] text-right">Profit</TableHead>
              <TableHead class="min-w-[80px] text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <template v-if="budgetGroups && budgetGroups.length">
              <template v-for="group in budgetGroups" :key="group.group_name">
                <TableRow>
                  <TableCell :colspan="18" class="bg-primary/5 text-primary">
                    {{ group.group_name }}
                  </TableCell>
                </TableRow>
                <template v-for="category in group.categories" :key="category.id">
                  <TableRow 
                    class="hover:bg-gray-50/50 transition-colors cursor-pointer" 
                    @click="toggleCategory(category.id)"
                  >
                    <TableCell :colspan="18" class="pl-6 bg-gray-50/50">
                      <div class="flex items-center">
                        <ChevronRight 
                          v-if="!expandedCategories.has(category.id)" 
                          class="h-4 w-4 mr-2 transition-transform duration-200" 
                        />
                        <ChevronDown 
                          v-else 
                          class="h-4 w-4 mr-2 transition-transform duration-200" 
                        />
                        {{ category.name }}
                      </div>
                    </TableCell>
                  </TableRow>
                  <template v-if="expandedCategories.has(category.id)">
                    <TableRow 
                      v-for="sub in category.subcategories" 
                      :key="sub.id"
                      class="hover:bg-gray-50/30 transition-colors"
                    >
                      <TableCell 
                        class="pl-12 text-gray-600 cursor-pointer hover:bg-gray-100" 
                        @click.stop="handleShowTransactions(sub)"
                      >
                        <div class="flex items-center gap-2">
                          <span>{{ sub.name }}</span>
                          <span class="text-xs text-gray-400">(Click to view transactions)</span>
                        </div>
                      </TableCell>
                      <TableCell class="font-medium tabular-nums text-right">
                        {{ parseFloat(sub.proposedBudget).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                      </TableCell>
                      <TableCell class="tabular-nums text-right">
                        {{ sub.janJun.toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                      </TableCell>
                      <template v-if="expandedFirstHalf">
                        <TableCell 
                          v-for="month in ['january', 'february', 'march', 'april', 'may', 'june']" 
                          :key="month"
                          class="tabular-nums text-gray-600 text-right"
                        >
                          {{ parseFloat(sub[month]).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                        </TableCell>
                      </template>
                      <TableCell class="tabular-nums text-right">
                        {{ sub.julDec.toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                      </TableCell>
                      <template v-if="expandedSecondHalf">
                        <TableCell 
                          v-for="month in ['july', 'august', 'september', 'october', 'november', 'december']" 
                          :key="month"
                          class="tabular-nums text-gray-600 text-right"
                        >
                          {{ parseFloat(sub[month]).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                        </TableCell>
                      </template>
                      <TableCell class="tabular-nums text-right font-medium">
                        {{ sub.ytd.toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                      </TableCell>
                      <TableCell class="tabular-nums text-right">
                        {{ parseFloat(sub.balance).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                      </TableCell>
                      <TableCell class="tabular-nums text-right text-green-600">
                        {{ parseFloat(sub.profit).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                      </TableCell>
                      <TableCell class="text-right p-2">
                        <DropdownMenu>
                          <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="icon">
                              <MoreHorizontal class="h-4 w-4" />
                              <span class="sr-only">Open menu</span>
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <DropdownMenuItem @click="handleAddProfit(sub)">
                              <PlusCircle class="mr-2 h-4 w-4 text-green-500" />
                              <span>Add Profit</span>
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </TableCell>
                    </TableRow>
                  </template>
                </template>
              </template>
            </template>
            <template v-else>
              <TableRow>
                <TableCell :colspan="18" class="h-32">
                  <div class="flex flex-col items-center justify-center gap-4">
                    <div class="flex flex-col items-center gap-1">
                      <span class="text-muted-foreground font-medium">No budget items found</span>
                      <span class="text-sm text-muted-foreground/70">Budget items will appear here once added</span>
                    </div>
                  </div>
                </TableCell>
              </TableRow>
            </template>
          </TableBody>
        </Table>
      </div>
    </div>

    <AddProfit
      :show="showAddProfitDialog"
      :subcategory-id="selectedSubcategory?.id"
      :budget-id="selectedSubcategory?.budget_id"
      @close="handleCloseAddProfit"
    />

    <TransactionHistory
      :show="showTransactionHistory"
      :transactions="selectedSubcategory?.transactions"
      :subcategory-name="selectedSubcategory?.name"
      @close="handleCloseTransactions"
    />

  </AppLayout>
</template>
