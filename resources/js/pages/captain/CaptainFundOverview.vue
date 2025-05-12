<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ChevronRight, ChevronDown, DollarSign, MoreHorizontal, PlusCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Fund Overview',
        href: '/fund-overview',
    },
];

// Fake data for testing
const budgetGroups = ref([
    {
        group_name: 'Beginning Cash Balance',
        categories: [
            {
                id: 1,
                name: 'Cash on Hand',
                subcategories: [
                    {
                        id: 11,
                        name: 'Petty Cash',
                        proposedBudget: 10000,
                        janJun: 5000,
                        julDec: 5000,
                        january: 1000,
                        february: 800,
                        march: 900,
                        april: 700,
                        may: 800,
                        june: 800,
                        july: 900,
                        august: 800,
                        september: 900,
                        october: 800,
                        november: 900,
                        december: 900,
                        ytd: 10000,
                        balance: 0,
                    },
                ],
            },
        ],
    },
    {
        group_name: 'Receipts',
        categories: [
            {
                id: 2,
                name: 'Donations',
                subcategories: [
                    {
                        id: 21,
                        name: 'Alumni Donations',
                        proposedBudget: 20000,
                        janJun: 10000,
                        julDec: 10000,
                        january: 2000,
                        february: 1500,
                        march: 1800,
                        april: 1700,
                        may: 1500,
                        june: 1500,
                        july: 1700,
                        august: 1500,
                        september: 1700,
                        october: 1500,
                        november: 1700,
                        december: 1700,
                        ytd: 20000,
                        balance: 0,
                    },
                ],
            },
        ],
    },
    {
        group_name: 'Expenditures',
        categories: [
            {
                id: 3,
                name: 'Salaries',
                subcategories: [
                    {
                        id: 31,
                        name: 'Teaching Staff',
                        proposedBudget: 30000,
                        janJun: 15000,
                        julDec: 15000,
                        january: 2500,
                        february: 2500,
                        march: 2500,
                        april: 2500,
                        may: 2500,
                        june: 2500,
                        july: 2500,
                        august: 2500,
                        september: 2500,
                        october: 2500,
                        november: 2500,
                        december: 2500,
                        ytd: 30000,
                        balance: 0,
                    },
                ],
            },
        ],
    },
    {
        group_name: 'MOOE',
        categories: [
            {
                id: 4,
                name: 'Utilities',
                subcategories: [
                    {
                        id: 41,
                        name: 'Electricity',
                        proposedBudget: 12000,
                        janJun: 6000,
                        julDec: 6000,
                        january: 1000,
                        february: 1000,
                        march: 1000,
                        april: 1000,
                        may: 1000,
                        june: 1000,
                        july: 1000,
                        august: 1000,
                        september: 1000,
                        october: 1000,
                        november: 1000,
                        december: 1000,
                        ytd: 12000,
                        balance: 0,
                    },
                ],
            },
        ],
    },
]);

const expandedFirstHalf = ref(false);
const expandedSecondHalf = ref(false);

// Track expanded categories for retractable feature
const expandedCategories = ref<Set<number>>(new Set());

function toggleCategory(categoryId: number) {
    if (expandedCategories.value.has(categoryId)) {
        expandedCategories.value.delete(categoryId);
    } else {
        expandedCategories.value.add(categoryId);
    }
    // Force reactivity
    expandedCategories.value = new Set(expandedCategories.value);
}

const totals = computed(() => {
    let total = {
        proposedBudget: 0,
        janJun: 0,
        julDec: 0,
        ytd: 0,
        balance: 0
    };

    budgetGroups.value.forEach(group => {
        group.categories.forEach(category => {
            category.subcategories.forEach(sub => {
                total.proposedBudget += sub.proposedBudget || 0;
                total.janJun += sub.janJun || 0;
                total.julDec += sub.julDec || 0;
                total.ytd += sub.ytd || 0;
                total.balance += sub.balance || 0;
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
                            <TableHead class="min-w-[200px] font-semibold">Object of Expenditure</TableHead>
                            <TableHead class="min-w-[150px] font-semibold">Proposed Budget</TableHead>
                            <TableHead class="min-w-[120px]">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold">1st Half</span>
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
                            <TableHead v-if="expandedFirstHalf" v-for="month in ['January', 'February', 'March', 'April', 'May', 'June']" 
                                :key="month" class="min-w-[100px] font-semibold"
                            >
                                {{ month }}
                            </TableHead>
                            <TableHead class="min-w-[120px]">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold">2nd Half</span>
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
                            <TableHead v-if="expandedSecondHalf" v-for="month in ['July', 'August', 'September', 'October', 'November', 'December']" 
                                :key="month" class="min-w-[100px] font-semibold"
                            >
                                {{ month }}
                            </TableHead>
                            <TableHead class="min-w-[120px] font-semibold">YTD</TableHead>
                            <TableHead class="min-w-[120px] font-semibold">Balance</TableHead>
                            <TableHead class="min-w-[80px] text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <template v-if="budgetGroups && budgetGroups.length">
                            <template v-for="group in budgetGroups" :key="group.group_name">
                                <!-- Group Row -->
                                <TableRow>
                                    <TableCell :colspan="18" class="font-semibold bg-primary/5 text-primary">
                                        {{ group.group_name }}
                                    </TableCell>
                                </TableRow>
                                <template v-for="category in group.categories" :key="category.id">
                                    <!-- Category Row -->
                                    <TableRow class="hover:bg-gray-50/50 transition-colors cursor-pointer" @click="toggleCategory(category.id)">
                                        <TableCell :colspan="18" class="pl-6 bg-gray-50/50">
                                            <div class="flex items-center">
                                                <ChevronRight v-if="!expandedCategories.has(category.id)" 
                                                    class="h-4 w-4 mr-2 transition-transform duration-200" 
                                                />
                                                <ChevronDown v-else class="h-4 w-4 mr-2 transition-transform duration-200" />
                                                {{ category.name }}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                    <!-- Subcategory Rows -->
                                    <template v-if="expandedCategories.has(category.id)">
                                        <template v-for="sub in category.subcategories" :key="sub.id">
                                            <TableRow class="hover:bg-gray-50/30 transition-colors">
                                                <TableCell class="pl-12 text-gray-600">{{ sub.name }}</TableCell>
                                                <TableCell class="font-medium tabular-nums">{{ sub.proposedBudget?.toLocaleString() ?? '-' }}</TableCell>
                                                <TableCell class="tabular-nums">{{ sub.janJun?.toLocaleString() ?? '-' }}</TableCell>
                                                <template v-if="expandedFirstHalf">
                                                    <TableCell v-for="month in ['january', 'february', 'march', 'april', 'may', 'june']" 
                                                        :key="month" class="tabular-nums text-gray-600"
                                                    >
                                                        {{ sub[month]?.toLocaleString() ?? '-' }}
                                                    </TableCell>
                                                </template>
                                                <TableCell class="tabular-nums">{{ sub.julDec?.toLocaleString() ?? '-' }}</TableCell>
                                                <template v-if="expandedSecondHalf">
                                                    <TableCell v-for="month in ['july', 'august', 'september', 'october', 'november', 'december']" 
                                                        :key="month" class="tabular-nums text-gray-600"
                                                    >
                                                        {{ sub[month]?.toLocaleString() ?? '-' }}
                                                    </TableCell>
                                                </template>
                                                <TableCell class="tabular-nums font-medium">{{ sub.ytd?.toLocaleString() ?? '-' }}</TableCell>
                                                <TableCell class="tabular-nums">{{ sub.balance?.toLocaleString() ?? '-' }}</TableCell>
                                                <TableCell class="text-right p-2">
                                                    <DropdownMenu>
                                                        <DropdownMenuTrigger asChild>
                                                            <Button variant="ghost" size="icon">
                                                                <MoreHorizontal class="h-4 w-4" />
                                                                <span class="sr-only">Open menu</span>
                                                            </Button>
                                                        </DropdownMenuTrigger>
                                                        <DropdownMenuContent align="end">
                                                            <DropdownMenuItem>
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
    </AppLayout>
</template>

<style scoped>
.tabular-nums {
    font-variant-numeric: tabular-nums;
}
</style>