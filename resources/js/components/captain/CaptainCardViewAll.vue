<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import { Eye, InboxIcon } from 'lucide-vue-next';
import { useStatusConfig } from '@/composables/useStatusConfig';
import { router } from '@inertiajs/vue3';
import { type Request } from '@/types/request';
import DataTablePagination from '@/components/DataTablePagination.vue';

const props = defineProps<{
  requests: {
    data: Request[];
    meta: {
      current_page: number;
      last_page: number;
      per_page: number;
      total: number;
    };
    links: {
      first: string;
      last: string;
      prev: string | null;
      next: string | null;
    };
  };
}>();

const emit = defineEmits<{
  (e: 'pageChange', page: number): void;
}>();

const handlePageChange = (page: number) => {
  emit('pageChange', page);
};

const { getStatusConfig } = useStatusConfig();

const viewRequest = (id: number) => {
  router.visit(route('captain.requests.view', { id }));
};
</script>

<template>
  <div class="rounded-md border overflow-x-auto p-4">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <template v-if="requests.data.length">
        <Card 
          v-for="request in requests.data" 
          :key="request.id" 
          class="relative overflow-hidden hover:shadow-lg duration-300 cursor-pointer hover:bg-muted/50 transition-colors"
          @click="viewRequest(request.id)"
        >
          <CardHeader class="pb-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <CardTitle class="text-lg font-semibold">{{ request.title }}</CardTitle>
                <span class="text-sm text-muted-foreground">#{{ request.id }}</span>
              </div>
            </div>
          </CardHeader>

          <CardContent class="space-y-6">
            <div class="space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-muted-foreground">Progress</span>
                <span class="font-medium">{{ request.progress }}%</span>
              </div>
              <Progress :value="request.progress" class="h-2" />
            </div>

            <div class="grid grid-cols-4 gap-2 w-full text-xs">
              <Badge 
                variant="outline" 
                :class="[
                  'flex-1 justify-center',
                  request.stages.form ? 'bg-primary/10 border-primary' : 'opacity-50'
                ]"
              >
                Form
              </Badge>
              <Badge 
                variant="outline" 
                :class="[
                  'flex-1 justify-center',
                  request.stages.quotation ? 'bg-primary/10 border-primary' : 'opacity-50'
                ]"
              >
                Quotation
              </Badge>
              <Badge 
                variant="outline" 
                :class="[
                  'flex-1 justify-center',
                  request.stages.purchaseRequest ? 'bg-primary/10 border-primary' : 'opacity-50'
                ]"
              >
                PR
              </Badge>
              <Badge 
                variant="outline" 
                :class="[
                  'flex-1 justify-center',
                  request.stages.purchaseOrder ? 'bg-primary/10 border-primary' : 'opacity-50'
                ]"
              >
                PO
              </Badge>
            </div>

            <!-- Basic Info -->
            <div class="grid gap-3">
              <div class="flex justify-between items-center">
                <span class="text-sm text-muted-foreground">Category:</span>
                <Badge variant="secondary" class="capitalize">{{ request.category }}</Badge>
              </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-muted-foreground">Status:</span>
                  <Badge 
                    :variant="getStatusConfig(request.status).variant"
                    class="flex items-center gap-1"
                  >
                    <component 
                      :is="getStatusConfig(request.status).icon" 
                      class="h-3 w-3"
                      :class="getStatusConfig(request.status).class" 
                    />
                    {{ request.status }}
                  </Badge>
                </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-muted-foreground">Created By:</span>
                <span class="text-sm font-medium">{{ request.created_by }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-muted-foreground">Created At:</span>
                <span class="text-sm">{{ request.created_at }}</span>
              </div>
            </div>
          </CardContent>
        </Card>
      </template>
      <template v-else>
        <Card class="col-span-full">
          <CardContent class="h-32 flex flex-col items-center justify-center gap-4">
            <InboxIcon class="h-8 w-8 text-muted-foreground/50" />
            <div class="flex flex-col items-center gap-1">
              <span class="text-muted-foreground font-medium">No requests found</span>
              <span class="text-sm text-muted-foreground/70">Requests will appear here once created</span>
            </div>
          </CardContent>
        </Card>
      </template>
    </div>
        <DataTablePagination
      v-if="requests.data.length > 0"
      :current-page="requests.meta.current_page"
      :total-pages="requests.meta.last_page"
      :on-page-change="handlePageChange"
    />
  </div>
</template>