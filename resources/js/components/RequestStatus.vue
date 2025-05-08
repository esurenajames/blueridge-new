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
  };
  quotation?: {
    have_quotation: 'true' | 'false';
  };
  canEdit: boolean;
}>();

const emit = defineEmits<{
  'show-confirmation': [title: string, description: string, action: string];
  'show-edit': [];
  'show-quotation': [];
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
              {{ props.request.is_completed ? 'completed' : request.status }}
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

      <div class="flex flex-col gap-3 pt-2">
        <template v-if="canEdit && request.status !== 'voided' && request.status !== 'declined' && !request.is_completed">
          <div class="flex gap-2">
            <!-- Primary Actions -->
            <Button 
              class="flex-1 gap-2"
              v-if="request.status === 'returned'"
              @click="emit('show-edit')"
            >
              <RefreshCw class="h-4 w-4" />
              Resubmit Documents
            </Button>

            <Button 
              v-else-if="request.quotation?.have_quotation === 'false'"
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

            <Button 
              v-else
              class="flex-1 gap-2"
              :disabled="request.status === 'pending'"
              @click="handleAction(
                'Process Request',
                'Are you sure you want to process this request?',
                'process'
              )"
            >
              <CheckCircle2 class="h-4 w-4" />
              Process Request
            </Button>

            <!-- Secondary Actions Dropdown -->
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