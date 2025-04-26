<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
    stages: {
      form: boolean;
      quotation: boolean;
      purchaseRequest: boolean;
      purchaseOrder: boolean;
    };
  }>;
}>();

const { getStatusConfig } = useStatusConfig();

const viewRequest = (id: number) => {
  router.visit(route('admin.requests.view', { id }));
};
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <template v-if="requests.length">
      <Card v-for="request in requests" :key="request.id" class="hover:bg-accent/5">
        <CardHeader>
          <div class="flex items-center">
            <CardTitle class="text-lg">{{ request.title }}</CardTitle>
          </div>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <!-- Progress Section -->
            <div class="space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-muted-foreground">Progress</span>
                <span class="font-medium">{{ request.progress }}%</span>
              </div>
              <Progress :value="request.progress" class="h-2" />
            </div>

            <!-- Stages Section -->
            <div class="grid grid-cols-2 gap-2">
              <div v-for="(completed, stage) in request.stages" 
                   :key="stage"
                   class="flex items-center gap-2"
              >
                <Badge 
                  :variant="completed ? 'default' : 'secondary'"
                  class="w-full flex items-center gap-2"
                >
                  {{ stage }}
                </Badge>
              </div>
            </div>

            <!-- Basic Info -->
            <div class="space-y-2">
              <div class="flex justify-between items-center">
                <span class="text-sm text-muted-foreground">ID:</span>
                <span>{{ request.id }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-muted-foreground">Category:</span>
                <Badge variant="secondary">{{ request.category }}</Badge>
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
                <span>{{ request.created_by }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-muted-foreground">Created At:</span>
                <span>{{ request.created_at }}</span>
              </div>
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
</template>