<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import { Eye, InboxIcon } from 'lucide-vue-next';
import { useStatusConfig } from '@/composables/useStatusConfig';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
  requests: Array<{
    id: number;
    title: string;
    category: string;
    status: string;
    created_at: string;
    created_by: string;
    progress: number;
  }>;
}>();

const { getStatusConfig } = useStatusConfig();

const viewRequest = (id: number) => {
  router.visit(route('admin.requests.view', { id }));
};
</script>

<template>
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
        <TableHead class="w-[100px]">Actions</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
    <template v-if="requests.length">
      <TableRow v-for="request in requests" :key="request.id">
        <TableCell>{{ request.id }}</TableCell>
        <TableCell class="font-medium">{{ request.title }}</TableCell>
        <TableCell>
          <Badge variant="secondary">{{ request.category }}</Badge>
        </TableCell>
        <TableCell class="w-[200px]">
          <div class="space-y-1">
            <Progress :value="request.progress" class="h-2" />
            <div class="text-xs text-muted-foreground">{{ request.progress }}%</div>
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
        <TableCell>
          <Button 
            variant="ghost" 
            size="icon"
            @click="viewRequest(request.id)"
          >
            <Eye class="h-4 w-4" />
          </Button>
        </TableCell>
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
</template>