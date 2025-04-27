<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { User, Clock } from 'lucide-vue-next';
import { useStatusConfig } from '@/composables/useStatusConfig';

const { getStatusConfig } = useStatusConfig();

const props = defineProps<{
  request: {
    status: string;
    created_by: string;
    created_at: string;
    processed_by?: string;
    processed_at?: string;
  };
}>();
</script>

<template>
  <Card>
    <CardHeader class="pb-6">
      <CardTitle>Timeline</CardTitle>
      <CardDescription>Request activity history</CardDescription>
    </CardHeader>
    <CardContent>
      <div class="relative space-y-8 before:absolute before:inset-0 before:ml-5 before:h-full before:w-0.5 before:-translate-x-px before:bg-muted">
        <!-- Created Event -->
        <div class="relative flex items-start gap-4">
          <div class="flex h-10 w-10 items-center justify-center rounded-full border bg-background shadow">
            <User class="h-4 w-4 text-primary" />
          </div>
          <div class="flex flex-col gap-0.5">
            <p class="text-sm font-medium">Request Created</p>
            <div class="flex items-center gap-2">
              <span class="text-xs text-muted-foreground">by {{ request.created_by }} - {{ request.created_at }}</span>
            </div>
          </div>
        </div>

        <!-- Processed Event -->
        <div 
          v-if="request.processed_by && request.processed_at" 
          class="relative flex items-start gap-4"
        >
          <div class="flex h-10 w-10 items-center justify-center rounded-full border bg-background shadow">
            <Clock class="h-4 w-4 text-primary" />
          </div>
          <div class="flex flex-col gap-0.5">
            <p class="text-sm font-medium">Request Processed</p>
            <div class="flex items-center gap-2">
              <span class="text-xs text-muted-foreground">by {{ request.processed_by }} - {{ request.processed_at }}</span>
            </div>
          </div>
        </div>
        
        <!-- Status Event -->
        <div class="relative flex items-center gap-4">
          <div class="flex h-10 w-10 items-center justify-center rounded-full border bg-background shadow">
            <component 
              :is="getStatusConfig(request.status).icon" 
              class="h-4 w-4"
              :class="getStatusConfig(request.status).class"
            />
          </div>
          <div class="flex flex-col gap-0.5">
            <p class="text-sm font-medium capitalize">Status: {{ request.status }}</p>
            <span class="text-xs text-muted-foreground">{{ getStatusConfig(request.status).message }}</span>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>