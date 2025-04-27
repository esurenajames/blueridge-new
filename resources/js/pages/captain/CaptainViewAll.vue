<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { type Request } from '@/types/request';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { LayoutGrid, LayoutList, Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';
import CaptainCardView from '@/components/captain/CaptainCardViewAll.vue';
import CaptainTableView from '@/components/captain/CaptainTableViewAll.vue';
import { router } from '@inertiajs/vue3';

const isTableView = ref(true);
const searchQuery = ref('');

const props = defineProps<{
  requests: {
    data: Request[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: route('captain.dashboard'),
  },
  {
    title: 'Requests',
    href: route('captain.requests'),
  },
];

const goToPage = (page: number) => {
  router.get(route('captain.requests'), { page, search: searchQuery.value }, { preserveState: true, replace: true });
};

watch(searchQuery, (val) => {
  router.get(route('captain.requests'), { search: val }, { preserveState: true, replace: true });
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