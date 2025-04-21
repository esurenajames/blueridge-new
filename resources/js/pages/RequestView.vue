<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Clock, CheckCircle2, XCircle, User, Calendar, CheckCircle, AlertCircle, Circle, Check, Circle as Dot, FileText, Download, RefreshCw } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { computed, ref, watch } from 'vue';
import { useToast } from '@/components/ui/toast';
import Confirmation from '@/components/Confirmation.vue';

const { toast } = useToast();

const confirmationState = ref({
  show: false,
  title: '',
  description: '',
  action: null as (() => void) | null
});

const showConfirmation = (title: string, description: string, action: () => void) => {
  confirmationState.value = {
    show: true,
    title,
    description,
    action
  };
};

const handleConfirm = () => {
  if (confirmationState.value.action) {
    confirmationState.value.action();
  }
  confirmationState.value.show = false;
};

const handleCancel = () => {
  confirmationState.value.show = false;
};

const props = defineProps<{
  request: {
    id: number;
    title: string;
    category: string;
    description: string;
    status: 'draft' | 'pending' | 'approved' | 'declined' | 'voided';
    created_at: string;
    created_by: string;
    collaborators?: Array<{
      name: string;
      role: string;
    }>;
    files?: Array<{
      name: string;
      size: number;
      uploaded_at: string;
    }>;
  };
}>();

const request = ref({ ...props.request });

watch(
  () => props.request,
  (newVal) => {
    request.value = { ...newVal };
  }
);

// Update the breadcrumbs section
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'View All Requests',
        href: route('requests.index'),
    },
    {
        title: `Request #${request.value.id}`,
        href: '#',
    }
];


const statusConfig = computed(() => {
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

const processRequest = () => {
  router.post(route('requests.process', { id: request.value.id }), {}, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: (page) => {
      if (page.props.flash?.request?.status) {
        request.value.status = page.props.flash.request.status;
      }
      toast({
        title: "Success",
        description: page.props.flash?.success ?? "Request processed successfully.",
        variant: "success",
      });
    },
  });
};

const reprocessRequest = () => {
  router.post(route('requests.reprocess', { id: request.value.id }), {}, {
    preserveScroll: true,
    onSuccess: (page) => {
      if (page.props.flash?.request?.status) {
        request.value.status = page.props.flash.request.status;
      }
      toast({
        title: "Success",
        description: page.props.flash?.success ?? "Request has been reprocessed",
        variant: "success",
      });
    },
  });
};

const resubmitDocuments = () => {
  router.post(route('requests.resubmit', { id: request.value.id }), {}, {
    preserveScroll: true,
    onSuccess: (page) => {
      if (page.props.flash?.request?.status) {
        request.value.status = page.props.flash.request.status;
      }
      toast({
        title: "Success",
        description: page.props.flash?.success ?? "Documents have been resubmitted",
        variant: "success",
      });
    },
  });
};

const voidRequest = () => {
  router.post(route('requests.void', { id: request.value.id }), {}, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: (page) => {
      if (page.props.flash?.request?.status) {
        request.value.status = page.props.flash.request.status;
      }
      toast({
        title: "Success",
        description: page.props.flash?.success ?? "Request has been voided.",
        variant: "success",
      });
    },
  });
};

const downloadFile = (file: { name: string }) => {
  const url = route('requests.download-file', { id: props.request.id, filename: file.name });
  window.open(url, '_blank');
};
</script>

<template>
  <Head title="Request View" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <Confirmation
        :show="confirmationState.show"
        :title="confirmationState.title"
        :description="confirmationState.description"
        @confirm="handleConfirm"
        @cancel="handleCancel"
      />
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card class="md:col-span-2">
          <CardHeader class="pb-6">
            <div class="flex items-center justify-between">
              <div>
                <CardTitle class="text-2xl">{{ request.title }}</CardTitle>
                <div class="flex items-center gap-2 mt-2">
                  <Badge variant="secondary" class="capitalize">
                    {{ request.category }}
                  </Badge>
                  <CardDescription class="ml-2">Request #{{ request.id }}</CardDescription>
                </div>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <div class="grid gap-8">
              <div class="grid gap-2">
                <div class="text-sm font-medium text-muted-foreground">Description</div>
                <div class="text-sm">{{ request.description }}</div>
              </div>

              <div class="grid gap-2">
                <div class="text-sm font-medium text-muted-foreground">Collaborators</div>
                <div class="space-y-2">
                  <div v-for="(collaborator, index) in request.collaborators" 
                    :key="index"
                    class="flex items-center gap-2 p-2 bg-muted rounded-md"
                  >
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                      <User class="h-4 w-4 text-primary" />
                    </div>
                    <div class="flex flex-col">
                      <span class="text-sm font-medium">{{ collaborator.name }}</span>
                      <span class="text-xs text-muted-foreground">{{ collaborator.role }}</span>
                    </div>
                  </div>
                  <div v-if="!request.collaborators?.length" class="text-sm text-muted-foreground">
                    No collaborators assigned
                  </div>
                </div>
              </div>

              <div class="grid gap-2">
                <div class="text-sm font-medium text-muted-foreground flex items-center gap-2">
                  <FileText class="h-4 w-4" />
                  Attached Files
                </div>
                <div class="space-y-2">
                  <div v-for="(file, index) in request.files" 
                    :key="index"
                    class="flex items-center gap-2 p-2 bg-muted rounded-md"
                  >
                    <FileText class="h-4 w-4 text-primary ml-2" />
                    <span class="text-sm">{{ file.name }}</span>
                    <span class="text-xs text-muted-foreground ml-auto">
                      {{ Math.round(file.size / 1024) }}KB
                    </span>
                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="downloadFile(file)">
                      <Download class="size-4" />
                    </Button>
                  </div>
                  <div v-if="!request.files?.length" class="text-sm text-muted-foreground">
                    No files attached
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <div class="space-y-6">
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
                    <span class="font-medium capitalize">{{ request.status }}</span>
                    <Badge :variant="statusConfig.badge" class="capitalize">{{ request.status }}</Badge>
                  </div>
                  <p class="text-sm text-muted-foreground">
                    {{ statusConfig.message }}
                  </p>
                </div>
              </div>

              <div class="flex flex-col gap-3 pt-2">
                <Button 
                  v-if="request.status !== 'declined' && request.status !== 'pending' && request.status !== 'voided'"
                  class="w-full gap-2"
                  @click="showConfirmation(
                    'Process Request',
                    'Are you sure you want to process this request?',
                    processRequest
                  )"
                >
                  <CheckCircle2 class="h-4 w-4" />
                  Process Request
                </Button>

                <Button 
                  v-if="request.status === 'declined' && request.status !== 'voided'"
                  class="w-full gap-2"
                  @click="showConfirmation(
                    'Re-process Request',
                    'Are you sure you want to re-process this request?',
                    reprocessRequest
                  )"
                >
                  <CheckCircle2 class="h-4 w-4" />
                  Re-process Request
                </Button>

                <Button 
                  v-if="request.status === 'declined' && request.status !== 'voided'"
                  variant="secondary" 
                  class="w-full gap-2"
                  @click="showConfirmation(
                    'Resubmit Documents',
                    'Are you sure you want to resubmit the documents?',
                    resubmitDocuments
                  )"
                >
                  <RefreshCw class="h-4 w-4" />
                  Resubmit Documents
                </Button>

                <Button 
                  v-if="request.status !== 'voided'"
                  variant="destructive" 
                  class="w-full gap-2"
                  @click="showConfirmation(
                    'Void Request',
                    'Are you sure you want to void this request? This action cannot be undone.',
                    voidRequest
                  )"
                >
                  <XCircle class="h-4 w-4" />
                  Void Request
                </Button>

                <div 
                  v-if="request.status === 'voided'"
                  class="text-sm text-muted-foreground text-center p-2"
                >
                  This request has been voided and cannot be modified.
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader class="pb-6">
              <CardTitle>Timeline</CardTitle>
              <CardDescription>Request created on {{ request.created_at }}</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="flex items-center gap-2 text-sm text-muted-foreground">
                <User class="h-4 w-4" />
                <span>Created by {{ request.created_by }}</span>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>