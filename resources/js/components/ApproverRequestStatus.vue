<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { CheckCircle2, XCircle, RotateCcw, Clock, CheckCircle, AlertCircle, DollarSign } from 'lucide-vue-next';
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
    purchaseRequest?: {
      have_supplier_approval?: boolean;
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

const showApproveRequestForm = computed(() => {
  return props.request.progress === 'Request Form' && 
         props.request.status === 'pending';
});

const showRequestFormReturned = computed(() => {
  return props.request.progress === 'Request Form' && 
         props.request.status === 'returned';
});

const showQuotationWaiting = computed(() => {
  return props.request.progress === 'Quotation' && 
         (!props.request?.quotation?.have_quotation || 
          props.request.quotation.have_quotation === 'false') &&
         props.request.status !== 'returned';
});

const showQuotationApproval = computed(() => {
  return props.request.progress === 'Quotation' && 
         props.request.quotation?.have_quotation === 'true' &&
         props.request.status === 'pending';
});

const showQuotationReturned = computed(() => {
  return props.request.progress === 'Quotation' &&
         props.request.status === 'returned';
});

const showPurchaseRequestWaiting = computed(() => {
  return props.request.progress === 'Purchase Request' &&
    props.request.purchaseRequest?.have_supplier_approval === false &&
    props.request.status === 'pending';
});

const showPurchaseRequestApproval = computed(() => {
  return props.request.progress === 'Purchase Request' &&
    props.request.purchaseRequest?.have_supplier_approval === true &&
    props.request.status === 'pending';
});

const showPurchaseOrder = computed(() => {
  return props.request.progress === 'Purchase Order';
        props.request.status === 'pending';
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
              {{ request.progress }}
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
          <!-- Only show action buttons if not voided, declined, or completed -->
          <template v-if="request.status !== 'voided' && request.status !== 'declined' && !request.is_completed">
            <!-- Request Form - Pending -->
            <template v-if="showApproveRequestForm">
              <div class="flex gap-2">
                <Button class="flex-1 gap-2" @click="handleAction('Approve Request', 'Are you sure you want to approve this request?', 'approve')">
                  <CheckCircle2 class="h-4 w-4" />
                  Approve Request
                </Button>
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="outline" size="icon">
                      <MoreHorizontal class="h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-42">
                    <DropdownMenuItem class="text-destructive" @click="handleAction('Decline Request', 'Are you sure you want to decline this request?', 'decline')">
                      <XCircle class="h-4 w-4 mr-2" />
                      Decline Request
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="handleAction('Return Request', 'Are you sure you want to return this request?', 'return')">
                      <RotateCcw class="h-4 w-4 mr-2" />
                      Return Request
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </template>

            <!-- Request Form - Returned -->
            <template v-if="showRequestFormReturned">
              <div class="flex gap-2">
                <Button class="flex-1 gap-2" disabled>
                  <Clock class="h-4 w-4" />
                  Waiting for Resubmission...
                </Button>
                 <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="outline" size="icon">
                      <MoreHorizontal class="h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-42">
                    <DropdownMenuItem class="text-destructive" @click="handleAction('Decline Request', 'Are you sure you want to decline this request?', 'decline')">
                      <XCircle class="h-4 w-4 mr-2" />
                      Decline Request
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </template>

            <!-- Quotation - Waiting -->
            <template v-if="showQuotationWaiting">
              <div class="flex gap-2">
                <Button class="flex-1 gap-2" disabled>
                  <Clock class="h-4 w-4" />
                  Waiting for Quotation...
                </Button>
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="outline" size="icon">
                      <MoreHorizontal class="h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-42">
                    <DropdownMenuItem class="text-destructive" @click="handleAction('Decline Request', 'Are you sure you want to decline this request?', 'decline')">
                      <XCircle class="h-4 w-4 mr-2" />
                      Decline Request
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="handleAction('Return Request', 'Are you sure you want to return this request?', 'return')">
                      <RotateCcw class="h-4 w-4 mr-2" />
                      Return Request
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </template>

            <!-- Quotation - Approval -->
            <template v-if="showQuotationApproval">
              <div class="flex gap-2">
                <Button class="flex-1 gap-2" @click="handleAction('Approve Quotation', 'Are you sure you want to approve this quotation?', 'approve')">
                  <CheckCircle2 class="h-4 w-4" />
                  Approve Quotation
                </Button>
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="outline" size="icon">
                      <MoreHorizontal class="h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-42">
                    <DropdownMenuItem class="text-destructive" @click="handleAction('Decline Quotation', 'Are you sure you want to decline this quotation?', 'decline')">
                      <XCircle class="h-4 w-4 mr-2" />
                      Decline Quotation
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="handleAction('Return Quotation', 'Are you sure you want to return this quotation?', 'return')">
                      <RotateCcw class="h-4 w-4 mr-2" />
                      Return Quotation
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </template>

            <!-- Quotation - Returned -->
            <template v-if="showQuotationReturned">
              <div class="flex gap-2">
                <Button class="flex-1 gap-2" disabled>
                  <Clock class="h-4 w-4" />
                  Waiting for Quotation...
                </Button>
                 <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="outline" size="icon">
                      <MoreHorizontal class="h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-42">
                    <DropdownMenuItem class="text-destructive" @click="handleAction('Decline Quotation', 'Are you sure you want to decline this quotation?', 'decline')">
                      <XCircle class="h-4 w-4 mr-2" />
                      Decline Quotation
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </template>

            <!-- Purchase Request -->
            <template v-if="showPurchaseRequestWaiting">
              <div class="flex gap-2">
                <Button class="flex-1 gap-2" disabled>
                  <Clock class="h-4 w-4" />
                  Waiting for Purchase Request...
                </Button>
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="outline" size="icon">
                      <MoreHorizontal class="h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-42">
                    <DropdownMenuItem class="text-destructive" @click="handleAction('Decline Purchase Request', 'Are you sure you want to decline this request?', 'decline')">
                      <XCircle class="h-4 w-4 mr-2" />
                      Decline Purchase Request
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </template>

            <!-- Purchase Request - Approve/Decline -->
            <template v-if="showPurchaseRequestApproval">
              <div class="flex gap-2">
                <Button class="flex-1 gap-2" @click="handleAction('Approve Purchase Request', 'Are you sure you want to approve this purchase request?', 'approve')">
                  <CheckCircle2 class="h-4 w-4" />
                  Approve Purchase Request
                </Button>
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="outline" size="icon">
                      <MoreHorizontal class="h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-42">
                    <DropdownMenuItem class="text-destructive" @click="handleAction('Decline Purchase Request', 'Are you sure you want to decline this request?', 'decline')">
                      <XCircle class="h-4 w-4 mr-2" />
                      Decline Purchase Request
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </template>

            <!-- Purchase Order -->
            <template v-if="showPurchaseOrder">
              <div class="flex gap-2">
                <Button class="flex-1 gap-2" @click="handleAction('Approve Purchase Order', 'Are you sure you want to approve this purchase order?', 'approve')">
                  <CheckCircle2 class="h-4 w-4" />
                  Approve Purchase Order
                </Button>
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="outline" size="icon">
                      <MoreHorizontal class="h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-42">
                    <DropdownMenuItem class="text-destructive" @click="handleAction('Decline Quotation', 'Are you sure you want to decline this quotation?', 'decline')">
                      <XCircle class="h-4 w-4 mr-2" />
                      Decline Quotation
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="handleAction('Return Quotation', 'Are you sure you want to return this quotation?', 'return')">
                      <RotateCcw class="h-4 w-4 mr-2" />
                      Return Quotation
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </template>
          </template>

          <!-- Status Messages -->
          <template v-if="request.status === 'approved' && !request.is_completed">
            <div class="text-sm text-muted-foreground text-center p-2">
              You have approved this request
            </div>
          </template>

          <template v-if="request.status === 'declined' && !request.is_completed">
            <div class="text-sm text-muted-foreground text-center p-2">
              You have declined this request
            </div>
          </template>

          <template v-if="request.status === 'returned' && !request.is_completed">
            <div class="text-sm text-muted-foreground text-center p-2">
              This request has been returned for revision
            </div>
          </template>

          <div v-if="request.status === 'voided'" class="text-sm text-muted-foreground text-center p-2">
            This request has been voided and cannot be modified.
          </div>

          <div v-if="request.is_completed" class="text-sm text-muted-foreground text-center p-2">
            This request has been completed and cannot be modified.
          </div>
        </div>
      </CardContent>
  </Card>
</template>