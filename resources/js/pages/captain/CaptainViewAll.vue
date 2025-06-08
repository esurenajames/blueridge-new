<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Request } from '@/types/request';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { LayoutGrid, LayoutList, Search, ChevronDownIcon, CalendarIcon } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';
import CaptainCardView from '@/components/captain/CaptainCardViewAll.vue';
import CaptainTableView from '@/components/captain/CaptainTableViewAll.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { router } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { RangeCalendar } from '@/components/ui/range-calendar';
import type { DateRange } from 'reka-ui';
import { onClickOutside } from '@vueuse/core';
import { ref as vueRef } from 'vue';

const isTableView = ref(true);
const searchQuery = ref('');
const typeFilter = ref('');
const statusFilter = ref('');
const dateRange = ref<DateRange>({ from: undefined, to: undefined });
const calendarOpen = ref(false);
const calendarRef = vueRef<HTMLElement | null>(null);
onClickOutside(calendarRef, () => { calendarOpen.value = false });

const typeOptions = [
  { label: 'All Progress', value: '' },
  { label: 'Request Form', value: 'Request Form' },
  { label: 'Quotation', value: 'Quotation' },
  { label: 'Purchase Request', value: 'Purchase Request' },
  { label: 'Purchase Order', value: 'Purchase Order' },
];

const statusOptions = [
  { label: 'All Status', value: '' },
  { label: 'Draft', value: 'draft' },
  { label: 'Pending', value: 'pending' },
  { label: 'Approved', value: 'approved' },
  { label: 'Declined', value: 'declined' },
  { label: 'Returned', value: 'returned' },
  { label: 'Voided', value: 'voided' },
];

const props = defineProps<{
  requests: {
    data: Request[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  search?: string;
  type?: string;
  status?: string;
  date_from?: string;
  date_to?: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Requests',
    href: route('captain.requests'),
  },
];

const goToPage = (page: number) => {
  router.get(
    route('captain.requests'),
    {
      page,
      search: searchQuery.value,
      type: typeFilter.value,
      status: statusFilter.value,
      date_from: dateRange.value.from ? format(dateRange.value.from, 'yyyy-MM-dd') : '',
      date_to: dateRange.value.to ? format(dateRange.value.to, 'yyyy-MM-dd') : '',
    },
    { preserveState: true, replace: true }
  );
};

watch([searchQuery, typeFilter, statusFilter, dateRange], ([query, type, status, dates]) => {
  router.get(
    route('captain.requests'),
    {
      search: query,
      type: type,
      status: status,
      date_from: dates.from ? format(dates.from, 'yyyy-MM-dd') : '',
      date_to: dates.to ? format(dates.to, 'yyyy-MM-dd') : '',
    },
    { preserveState: true, replace: true }
  );
});
</script>

<template>
  <Head title="All Requests" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-3 p-6">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="relative w-full sm:max-w-md">
          <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" />
          <Input 
            v-model="searchQuery"
            type="search"
            placeholder="Search requests..."
            class="pl-10 w-full"
          />
        </div>
        <div class="flex items-center self-start sm:self-auto space-x-2">
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="outline" class="w-[180px] justify-between">
                {{ typeFilter ? typeOptions.find(t => t.value === typeFilter)?.label : 'All Progress' }}
                <ChevronDownIcon class="ml-2 h-4 w-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-[180px]">
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
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="outline" class="w-[150px] justify-between">
                {{ statusFilter ? statusOptions.find(s => s.value === statusFilter)?.label : 'All Status' }}
                <ChevronDownIcon class="ml-2 h-4 w-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-[150px]">
              <DropdownMenuItem
                v-for="option in statusOptions"
                :key="option.value"
                @click="statusFilter = option.value"
                :class="{'bg-accent': statusFilter === option.value}"
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
          <Button
            variant="outline"
            size="icon"
            :class="{ 'bg-accent': isTableView }"
            @click="isTableView = true"
          >
            <LayoutList class="h-4 w-4" />
          </Button>
          <Button
            variant="outline"
            size="icon"
            :class="{ 'bg-accent': !isTableView }"
            @click="isTableView = false"
          >
            <LayoutGrid class="h-4 w-4" />
          </Button>
        </div>
      </div>

      <CaptainTableView 
        v-if="isTableView" 
        :requests="props.requests"
        @page-change="goToPage"
      />
      <CaptainCardView
        v-else
        :requests="props.requests"
        @page-change="goToPage"
      />
      
    </div>
  </AppLayout>
</template>