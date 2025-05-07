<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { User, Clock, FileText, FileCheck, ShoppingCart, Package, MessageSquare } from 'lucide-vue-next';
import { useStatusConfig } from '@/composables/useStatusConfig';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';

const { getStatusConfig } = useStatusConfig();

const props = defineProps<{
  request: {
    status: string;
    created_by: string;
    created_at: string;
    processed_by?: string;
    processed_at?: string;
    timelines: {
      id: number;
      approver_name: string;
      approved_date: string;
      approved_progress: string;
      approved_status: string;
      remarks?: string;
      processor_name?: string;
      processed_date?: string;
      processed_progress?: string;
      processed_status?: string;
    }[];
  };
}>();

const progressIcons = {
  'Request Form': FileText,
  'Quotation': FileCheck,
  'Purchase Request': ShoppingCart,
  'Purchase Order': Package
};

const selectedRemarks = ref('');
const isDialogOpen = ref(false);

const showRemarks = (remarks: string) => {
  selectedRemarks.value = remarks;
  isDialogOpen.value = true;
};
</script>

<template>
  <Card>
    <CardHeader class="pb-6">
      <CardTitle>Timeline</CardTitle>
      <CardDescription>Request activity history</CardDescription>
    </CardHeader>
    <CardContent>
      <div class="relative space-y-6 before:absolute before:inset-0 before:ml-5 before:h-full before:w-0.5 before:-translate-x-px before:bg-muted">
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

        <!-- Processed Event (if available) -->
        <!-- <div v-if="request.processed_by" class="relative flex items-start gap-4">
          <div class="flex h-10 w-10 items-center justify-center rounded-full border bg-background shadow">
            <Clock class="h-4 w-4 text-blue-500" />
          </div>
          <div class="flex flex-col gap-0.5">
            <p class="text-sm font-medium">Request Processed</p>
            <div class="flex items-center gap-2">
              <span class="text-xs text-muted-foreground">
                by {{ request.processed_by }} - {{ request.processed_at }}
              </span>
            </div>
          </div>
        </div> -->

        <!-- Timeline Events -->
        <!-- Timeline Events -->
        <div 
          v-for="timeline in request.timelines" 
          :key="timeline.id"
          class="space-y-6"
        >
          <!-- Processor Event -->
         <div 
            v-if="timeline.processor_name && timeline.processor_name !== 'N/A' && timeline.processed_date" 
            class="relative flex items-start gap-4"
          >
            <div class="flex h-10 w-10 items-center justify-center rounded-full border bg-background shadow">
              <Clock 
                class="h-4 w-4"
                :class="{
                  'text-blue-500': timeline.processed_status === 'processed',
                  'text-red-500': timeline.processed_status === 'voided',
                  'text-yellow-500': timeline.processed_status === 'resubmitted'
                }"
              />
            </div>
            <div class="flex flex-col gap-0.5">
              <p class="text-sm font-medium capitalize">{{ timeline.processed_progress }} {{ timeline.processed_status }}</p>
              <div class="flex items-center gap-2">
                <span class="text-xs text-muted-foreground">
                  by {{ timeline.processor_name }} - {{ timeline.processed_date }}
                </span>
              </div>
              <div v-if="timeline.remarks" class="flex items-center gap-1 mt-2">
                <Button 
                  @click="showRemarks(timeline.remarks!)"
                  variant="outline"
                  size="sm"
                  class="h-8 px-3"
                >
                  <MessageSquare class="h-4 w-4 mr-2" />
                  View Process Remarks
                </Button>
              </div>
            </div>
          </div>

          <!-- Approver Event -->
          <div 
            v-if="timeline.approver_name && timeline.approver_name !== 'N/A' && timeline.approved_date" 
            class="relative flex items-start gap-4"
          >
            <div class="flex h-10 w-10 items-center justify-center rounded-full border bg-background shadow">
              <component 
                :is="progressIcons[timeline.approved_progress]" 
                class="h-4 w-4"
                :class="{
                  'text-green-500': timeline.approved_status === 'approved',
                  'text-red-500': timeline.approved_status === 'declined',
                  'text-yellow-500': timeline.approved_status === 'returned'
                }"
              />
            </div>
            <div class="flex flex-col gap-0.5">
              <p class="text-sm font-medium capitalize">{{ timeline.approved_progress }} {{ timeline.approved_status }}</p>
              <div class="flex items-center gap-2" v-if="timeline.approver_name && timeline.approved_date">
                <span class="text-xs text-muted-foreground">
                  by {{ timeline.approver_name }} - {{ timeline.approved_date }}
                </span>
              </div>
              <div v-if="timeline.remarks" class="flex items-center gap-1 mt-2">
                <Button 
                  @click="showRemarks(timeline.remarks!)"
                  variant="outline"
                  size="sm"
                  class="h-8 px-3"
                >
                  <MessageSquare class="h-4 w-4 mr-2" />
                  View Approval Remarks
                </Button>
              </div>
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

  <!-- Remarks Dialog -->
  <Dialog v-model:open="isDialogOpen">
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Remarks</DialogTitle>
      </DialogHeader>
      <div class="mt-4">
        <p class="text-sm text-muted-foreground whitespace-pre-wrap">{{ selectedRemarks }}</p>
      </div>
    </DialogContent>
  </Dialog>
</template>