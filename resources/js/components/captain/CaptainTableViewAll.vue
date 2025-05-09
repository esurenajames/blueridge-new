<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
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

function getProgressValue(stages: { form: boolean, quotation: boolean, purchaseRequest: boolean, purchaseOrder: boolean }, progress: string, isCompleted?: boolean): number {
  switch (progress) {
    case 'Form':
      return 0;
    case 'Quotation':
      return 25;
    case 'Purchase Request':
    case 'PR':
      return 50;
    case 'Purchase Order':
    case 'PO':
      return isCompleted ? 100 : 75;
    default:
      return 0;
  }
}
</script>

<template>
  <div class="rounded-md border overflow-x-auto">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>ID</TableHead>
          <TableHead>Title</TableHead>
          <TableHead>Category</TableHead>
          <TableHead>Progress</TableHead>
          <TableHead>Status</TableHead>
          <TableHead>Created By</TableHead>
          <TableHead>Created At</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
      <template v-if="requests.data.length">
        <TableRow
          v-for="request in requests.data"
          :key="request.id"
          class="cursor-pointer hover:bg-muted/50 transition-colors"
          @click="viewRequest(request.id)"
        >
          <TableCell>{{ request.id }}</TableCell>
          <TableCell class="font-medium">{{ request.title }}</TableCell>
          <TableCell>
            <Badge variant="secondary">{{ request.category }}</Badge>
          </TableCell>
          <TableCell class="min-w-[300px]">
            <div class="space-y-2">
              <!-- Stages -->
              <div class="flex gap-2 text-xs">
                <Badge
                  variant="outline"
                  :class="[
                    request.progress === 'Form' ? 'bg-primary/10 border-primary' : 'opacity-50'
                  ]"
                >
                  Form
                </Badge>
                <Badge
                  variant="outline"
                  :class="[
                    request.progress === 'Quotation' ? 'bg-primary/10 border-primary' : 'opacity-50'
                  ]"
                >
                  Quotation
                </Badge>
                <Badge
                  variant="outline"
                  :class="[
                    request.progress === 'PR' || request.progress === 'Purchase Request' ? 'bg-primary/10 border-primary' : 'opacity-50'
                  ]"
                >
                  PR
                </Badge>
                <Badge
                  variant="outline"
                  :class="[
                    request.progress === 'PO' || request.progress === 'Purchase Order' ? 'bg-primary/10 border-primary' : 'opacity-50'
                  ]"
                >
                  PO
                </Badge>
              </div>
              <div class="flex items-center gap-2">
                <div class="progress-bar">
                  <div
                    class="progress-value"
                    :style="{ width: getProgressValue(request.stages, request.progress, request.is_completed) + '%' }"
                  ></div>
                </div>
                <div class="text-xs text-muted-foreground whitespace-nowrap">
                  {{ getProgressValue(request.stages, request.progress, request.is_completed) }}%
                </div>
              </div>
            </div>
          </TableCell>
          <TableCell>
            <Badge
              :variant="getStatusConfig(request.status).variant"
              class="flex items-center w-fit gap-1"
            >
              <component
                :is="getStatusConfig(request.status).icon"
                class="h-3 w-3"
                :class="getStatusConfig(request.status).class"
              />
              {{ request.status }}
            </Badge>
          </TableCell>
          <TableCell>{{ request.created_by }}</TableCell>
          <TableCell>{{ request.created_at }}</TableCell>
        </TableRow>

        </template>
        <template v-else>
          <TableRow>
              <TableCell colspan="8" class="h-32">
              <div class="flex flex-col items-center justify-center gap-4">
                  <InboxIcon class="h-8 w-8 text-muted-foreground/50" />
                  <div class="flex flex-col items-center gap-1">
                  <span class="text-muted-foreground font-medium">No requests found</span>
                  <span class="text-sm text-muted-foreground/70">Requests will appear here once created</span>
                  </div>
              </div>
              </TableCell>
          </TableRow>
          </template>
      </TableBody>
    </Table>

    <DataTablePagination
      v-if="requests.data.length > 0"
      :current-page="requests.meta.current_page"
      :total-pages="requests.meta.last_page"
      :on-page-change="handlePageChange"
    />
  </div>
</template>

<style scoped>
.overflow-auto {
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
}
</style>
