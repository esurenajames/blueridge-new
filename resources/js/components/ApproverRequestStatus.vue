<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { CheckCircle2, XCircle, RotateCcw, Clock, CheckCircle, AlertCircle } from 'lucide-vue-next';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { MoreHorizontal } from 'lucide-vue-next';

const props = defineProps<{
  request: {
    status: 'draft' | 'pending' | 'approved' | 'declined' | 'returned' | 'voided';
    is_completed?: boolean;
    progress?: string;
    quotation?: {
      have_quotation: 'true' | 'false';
    };
  };
}>();

const emit = defineEmits<{
  'show-confirmation': [title: string, description: string, action: string]
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
    case 'pending':
      return {
        icon: Clock,
        class: 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400',
        iconClass: 'text-yellow-600 dark:text-yellow-400',
        badge: 'warning',
        message: 'This request is awaiting your approval.'
      };
    case 'approved':
      return {
        icon: CheckCircle,
        class: 'bg-green-50 dark:bg-green-900/20',
        iconClass: 'text-green-600 dark:text-green-400',
        badge: 'success',
        message: 'You have approved this request.'
      };
    case 'declined':
      return {
        icon: XCircle,
        class: 'bg-red-50 dark:bg-red-900/20',
        iconClass: 'text-red-600 dark:text-red-400',
        badge: 'destructive',
        message: 'You have declined this request.'
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
              {{ request.is_completed ? 'completed' : request.status }}
            </span>
            <Badge :variant="statusConfig.badge" class="capitalize">
              {{ request.is_completed ? 'completed' : request.status }}
            </Badge>
          </div>
          <p class="text-sm text-muted-foreground">
            {{ statusConfig.message }}
          </p>
        </div>
      </div>

      <div class="flex flex-col gap-3 pt-2">
        <!-- Pending Status Actions (only if not completed) -->
        <template v-if="request.status === 'pending' && !request.is_completed">
          <div class="flex gap-2">
            <template v-if="request.progress === 'Quotation'">
              <Button
                class="flex-1 gap-2"
                :disabled="request.quotation?.have_quotation === 'false'"
                @click="handleAction(
                  'Approve Request',
                  'Are you sure you want to approve this request?',
                  'approve'
                )"
              >
                <CheckCircle2 class="h-4 w-4" />
                {{ request.quotation?.have_quotation === 'false' ? 'Waiting For Quotation...' : 'Approve Quotation' }}
              </Button>
            </template>
            <template v-else>
              <Button
                class="flex-1 gap-2"
                @click="handleAction(
                  'Approve Request',
                  'Are you sure you want to approve this request?',
                  'approve'
                )"
              >
                <CheckCircle2 class="h-4 w-4" />
                Approve Request
              </Button>
            </template>
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
                    'Decline Request',
                    'Are you sure you want to decline this request?',
                    'decline'
                  )"
                >
                  <XCircle class="h-4 w-4 mr-2" />
                  Decline Request
                </DropdownMenuItem>
                <DropdownMenuItem
                  @click="handleAction(
                    'Return Request',
                    'Are you sure you want to return this request for revision?',
                    'return'
                  )"
                >
                  <RotateCcw class="h-4 w-4 mr-2" />
                  Return Request
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </template>

        <!-- Approved Status Actions -->
        <template v-if="request.status === 'approved' && !request.is_completed">
          <div class="text-sm text-muted-foreground text-center p-2">
            You have approved this request
          </div>
        </template>

        <!-- Declined Status Actions -->
        <template v-if="request.status === 'declined' && !request.is_completed">
          <div class="text-sm text-muted-foreground text-center p-2">
            You have declined this request
          </div>
        </template>

        <!-- Returned Status Message -->
        <template v-if="request.status === 'returned' && !request.is_completed">
          <div class="text-sm text-muted-foreground text-center p-2">
            This request has been returned for revision
          </div>
        </template>

        <!-- Voided Status Message -->
        <div
          v-if="request.status === 'voided'"
          class="text-sm text-muted-foreground text-center p-2"
        >
          This request has been voided and cannot be modified.
        </div>

        <!-- Completed Status Message -->
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