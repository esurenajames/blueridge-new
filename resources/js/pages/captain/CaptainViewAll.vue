<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { LayoutGrid, LayoutList } from 'lucide-vue-next';
import { ref } from 'vue';
import CaptainTableView from './CaptainTableViewAll.vue';
import CaptainCardView from './CaptainCardViewAll.vue';

const isTableView = ref(true);

const props = defineProps<{
  requests: Array<{
    id: number;
    title: string;
    category: string;
    status: 'pending' | 'approved' | 'declined' | 'voided' | 'completed' | 'returned';
    created_at: string;
    created_by: string;
    progress: number;
    stages: {
      form: boolean;
      quotation: boolean;
      purchaseRequest: boolean;
      purchaseOrder: boolean;
    };
  }>;
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
</script>

<template>
  <Head title="All Requests" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between">
          <CardTitle>All Requests</CardTitle>
          <div class="flex items-center space-x-2">
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
        </CardHeader>
        <CardContent>
          <CaptainTableView 
            v-if="isTableView" 
            :requests="requests" 
          />
          <CaptainCardView 
            v-else 
            :requests="requests" 
          />
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>