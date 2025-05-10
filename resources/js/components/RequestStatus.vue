<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { CheckCircle2, XCircle, RefreshCw, Clock, RotateCcw, CheckCircle, AlertCircle, MoreHorizontal, FileText } from 'lucide-vue-next';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';

const props = defineProps<{
  request: {
    status: 'draft' | 'pending' | 'approved' | 'voided' | 'declined' | 'returned';
    is_completed?: boolean;
    progress?: string;
    data?: {
      quotation?: {
        have_quotation: string;
      };
    };
  };
  canEdit: boolean;
}>();

const emit = defineEmits<{
  'show-confirmation': [title: string, description: string, action: string];
  'show-edit': [];
  'show-quotation': [data: any];
}>();

const statusConfig = computed(() => {
  if (props.request.is_completed) {
    return {
      icon: CheckCircle,
      class: 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
      iconClass: 'text-blue-600 dark:text-blue-400',
      badge: 'success',
      message: 'This request has been completed.'
    };
  }
  switch (props.request.status) {
    case 'draft':
      return {
        icon: AlertCircle,
        class: 'bg-slate-50 dark:bg-slate-900/20',
        iconClass: 'text-slate-600 dark:text-slate-400',
        badge: 'secondary',
        message: 'This request is still in draft mode.'
      };
    case 'approved':
      return {
        icon: CheckCircle,
        class: 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400',
        iconClass: 'text-green-600 dark:text-green-400',
        badge: 'success',
        message: 'This request has been approved.'
      };
    case 'pending':
      return {
        icon: Clock,
        class: 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400',
        iconClass: 'text-yellow-600 dark:text-yellow-400',
        badge: 'warning',
        message: 'This request is awaiting approval.'
      };
    case 'declined':
      return {
        icon: XCircle,
        class: 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
        iconClass: 'text-red-600 dark:text-red-400',
        badge: 'destructive',
        message: 'This request has been declined.'
      };
    case 'returned':
      return {
        icon: RotateCcw,
        class: 'bg-orange-500/20 dark:bg-orange-500/20',
        iconClass: 'text-orage-600 dark:text-orange-50',
        badge: 'default',
        message: 'This request has been returned for revision.'
      };
    case 'voided':
      return {
        icon: XCircle,
        class: 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
        iconClass: 'text-red-600 dark:text-red-400',
        badge: 'destructive',
        message: 'This request has been voided.'
      };
    default:
      return {
        icon: AlertCircle,
        class: 'bg-slate-50 dark:bg-slate-900/20',
        iconClass: 'text-slate-600 dark:text-slate-400',
        badge: 'secondary',
        message: 'Status unknown.'
      };
  }
});

const showProcessButton = computed(() => {
  return props.request.progress === 'Request Form' && props.request.status === 'draft';
});

const showSubmitQuotation = computed(() => {
  return props.request.progress === 'Quotation' && 
         (!props.request?.quotation?.have_quotation || props.request.quotation.have_quotation === 'false') && 
         props.request.status !== 'returned';
});

const showResubmitDocument = computed(() => {
  return props.request.progress === 'Quotation' && 
         (!props.request?.quotation?.have_quotation || props.request.quotation.have_quotation === 'false') && 
         props.request.status === 'returned';
});

const showResubmitQuotation = computed(() => {
  return props.request.progress === 'Quotation' && 
         props.request?.quotation?.have_quotation === 'true' && 
         props.request.status === 'returned';
});

const showWaitingApproval = computed(() => {
  if (props.request.progress === 'Request Form' && props.request.status === 'pending') {
    return true;
  }
  
  if (props.request.progress === 'Quotation' && 
      props.request?.quotation?.have_quotation === 'true' && 
      props.request.status === 'pending') {
    return true;
  }
  
  return false;
});

const handleAction = (title: string, description: string, action: string) => {
  emit('show-confirmation', title, description, action);
};
</script>

<template>
  <Card>
    <CardHeader class="pb-6">
      <CardTitle>Status</CardTitle>
      <CardDescription>Current request status</CardDescription>
    </CardHeader>
    <CardContent class="space-y-6">
      <!-- Status Display Section -->
      <div 
        :class="[
          'rounded-lg p-4 flex items-start gap-3',
          statusConfig.class
        ]"
      >
        <component :is="statusConfig.icon" class="h-5 w-5 mt-0.5" :class="statusConfig.iconClass" />
        <div class="space-y-2">
          <div class="flex items-center gap-2">
            <span class="font-medium capitalize">
              {{ props.request.progress}}
            </span>
            <Badge :variant="statusConfig.badge" class="capitalize">
              {{ props.request.is_completed ? 'completed' : request.status }}
            </Badge>
          </div>
          <p class="text-sm text-muted-foreground">
            {{ statusConfig.message }}
          </p>
        </div>
      </div>

      <!-- Actions Section -->
      <div class="flex flex-col gap-3 pt-2">
        <template v-if="canEdit && request.status !== 'voided' && request.status !== 'declined' && !request.is_completed">
          <div class="flex gap-2">
            <!-- Process Request -->
            <Button 
              v-if="showProcessButton"
              class="flex-1 gap-2"
              @click="handleAction(
                'Process Request',
                'Are you sure you want to process this request?',
                'process'
              )"
            >
              <CheckCircle2 class="h-4 w-4" />
              Process Request
            </Button>

            <!-- Submit Quotation -->
            <Button 
              v-if="showSubmitQuotation"
              class="flex-1 gap-2"
              @click="emit('show-confirmation', 
                'Submit Quotation',
                'Are you sure you want to submit quotation for this request?',
                'show-quotation'
              )"
            >
              <FileText class="h-4 w-4" />
              Submit Quotation
            </Button>

            <!-- Resubmit Document -->
            <Button 
              v-if="showResubmitDocument"
              class="flex-1 gap-2"
              @click="emit('show-edit')"
            >
              <RefreshCw class="h-4 w-4" />
              Resubmit Documents
            </Button>

            <!-- Resubmit Quotation -->
            <Button 
              v-if="showResubmitQuotation"
              class="flex-1 gap-2"
              @click="emit('show-confirmation', 
                'Resubmit Quotation',
                'Are you sure you want to resubmit this quotation?',
                'show-quotation',
                request.quotation
              )"
            >
              <RefreshCw class="h-4 w-4" />
              Resubmit Quotation
            </Button>

            <!-- Waiting For Approval -->
            <Button 
              v-if="showWaitingApproval"
              class="flex-1 gap-2"
              disabled
            >
              <Clock class="h-4 w-4" />
              Waiting For Approval...
            </Button>

            <!-- Void Action Dropdown -->
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="outline" size="icon">
                  <MoreHorizontal class="h-4 w-4" />
                  <span class="sr-only">More actions</span>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" class="w-42">
                <DropdownMenuItem
                  class="text-destructive"
                  @click="handleAction(
                    'Void Request',
                    'Are you sure you want to void this request? This action cannot be undone.',
                    'void'
                  )"
                >
                  <XCircle class="h-4 w-4 mr-2" />
                  Void Request
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </template>

        <!-- Status Messages -->
        <div 
          v-if="!canEdit && request.status !== 'voided' && request.status !== 'declined' && !request.is_completed"
          class="text-sm text-muted-foreground text-center p-2"
        >
          You only have view access to this request.
        </div>

        <div 
          v-if="request.status === 'voided'"
          class="text-sm text-muted-foreground text-center p-2"
        >
          This request has been voided and cannot be modified.
        </div>
        
        <div 
          v-if="request.status === 'declined'"
          class="text-sm text-muted-foreground text-center p-2"
        >
          This request has been declined and cannot be modified.
        </div>

        <div 
          v-if="request.is_completed"
          class="text-sm text-muted-foreground text-center p-2"
        >
          This request has been completed and cannot be modified.
        </div>
      </div>
    </CardContent>
  </Card>
</template>