<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import type { Request } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { CheckCircle2, XCircle, RotateCcw, FileText, Download, UserIcon, File, ChevronDown } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { ref, watch } from 'vue';
import { useToast } from '@/components/ui/toast';
import Confirmation from '@/components/Confirmation.vue';
import RemarksDialog from '@/components/RemarksDialog.vue';
import { useStatusConfig } from '@/composables/useStatusConfig';
import RequestTimeline from '@/components/RequestTimeline.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { getAvatarProps } from '@/utils/avatar';
import { getDisplayRole } from '@/utils/roles';
import ApproverRequestStatus from '@/components/ApproverRequestStatus.vue';
import ViewQuotationDialog from '@/components/ViewQuotationDialog.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import QuotationFormApprove from '@/components/captain/QuotationFormApprove.vue';

const { toast } = useToast();
const { getStatusConfig } = useStatusConfig();
const showQuotationDialog = ref(false);
const showQuotationApprove = ref(false);
const confirmationState = ref({
  show: false,
  title: '',
  description: '',
  action: null as (() => void) | null
});

const remarksState = ref({
  show: false,
  title: '',
  description: '',
  action: '' as 'approve' | 'decline' | 'return'
});

const props = defineProps<{
  request: {
    data: Request;
  };
}>();

const request = ref(props.request.data);

watch(
  () => props.request.data,
  (newVal) => {
    request.value = newVal;
  }
);

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: route('captain.dashboard'),
  },
  {
    title: 'Requests',
    href: route('captain.requests'),
  },
  {
    title: `Request #${request.value.id}`,
    href: '#',
  }
];

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

const handleStatusAction = (title: string, description: string, action: string) => {
  if (action === 'approve' && request.value.quotation?.have_quotation === 'true') {
    showQuotationApprove.value = true;
    return;
  }

  remarksState.value = {
    show: true,
    title,
    description,
    action: action as 'approve' | 'decline' | 'return'
  };
};

const handleRemarksConfirm = (remarks: string) => {
  remarksState.value.show = false;
  
  const title = 'Confirm Action';
  const description = `Are you sure you want to proceed? Your remarks will be saved with this action.`;
  
  showConfirmation(title, description, () => {
    const payload = { remarks };
    const options = {
      preserveScroll: true,
      onSuccess: () => {
        toast({
          title: "Success",
          description: `Request has been ${remarksState.value.action}d`,
          variant: "success",
        });
      },
    };

    switch (remarksState.value.action) {
      case 'approve':
        router.post(route('requests.approve', { id: request.value.id }), payload, options);
        break;
      case 'decline':
        router.post(route('requests.decline', { id: request.value.id }), payload, options);
        break;
      case 'return':
        router.post(route('requests.return', { id: request.value.id }), payload, options);
        break;
    }
  });
};

const handleQuotationApprove = (companyId: number) => {
  const payload = { remarks: '', company_id: companyId };
  
  router.post(route('requests.approve', { id: request.value.id }), payload, {
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: "Success",
        description: "Request has been approved",
        variant: "success",
      });
      showQuotationApprove.value = false;
    },
  });
};

const handleRemarksCancel = () => {
  remarksState.value.show = false;
};

const downloadFile = (file: { name: string }) => {
  const url = route('requests.download-file', { id: request.value.id, filename: file.name });
  window.open(url, '_blank');
};
</script>

<template>
  <Head title="Request Details" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div v-if="!request" class="text-center p-6">
        <p class="text-muted-foreground">Request not found or loading...</p>
      </div>

      <template v-else>
        <Confirmation
          :show="confirmationState.show"
          :title="confirmationState.title"
          :description="confirmationState.description"
          @confirm="handleConfirm"
          @cancel="handleCancel"
        />

        <RemarksDialog
          :show="remarksState.show"
          :title="remarksState.title"
          :description="remarksState.description"
          @confirm="handleRemarksConfirm"
          @cancel="handleRemarksCancel"
        />

        <ViewQuotationDialog
          v-if="request.quotation"
          v-model:show="showQuotationDialog"
          :quotation="request.quotation"
        />

        <QuotationFormApprove
          v-if="showQuotationApprove"
          :show="showQuotationApprove"
          :quotation="request.quotation"
          @close="showQuotationApprove = false"
          @approve="handleQuotationApprove"
        />

        <div class="flex justify-end mb-6">
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="outline" size="sm" class="gap-2">
                <File class="h-4 w-4" />
                Export as PDF
                <ChevronDown class="h-4 w-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-[200px]">
              <DropdownMenuItem @click="exportAsPDF('form')">
                <File class="h-4 w-4 mr-2" />
                  Request Form as PDF
              </DropdownMenuItem>
              <DropdownMenuItem 
                v-if="request.quotation"
                @click="exportAsPDF('quotation')"
              >
                <File class="h-4 w-4 mr-2" />
                  Quotation as PDF
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <Card class="md:col-span-2">
            <CardHeader>
              <CardTitle class="text-2xl">{{ request.title }}</CardTitle>
              <div class="flex items-center gap-2 mt-2">
                <Badge variant="secondary" class="capitalize">
                  {{ request.category }}
                </Badge>
                <CardDescription class="ml-2">Request #{{ request.id }}</CardDescription>
              </div>
            </CardHeader>
            <CardContent>
              <div class="grid gap-8">
                <!-- Description Section -->
                <div class="grid gap-2">
                  <div class="text-sm font-medium text-muted-foreground">Description</div>
                  <div class="text-sm">{{ request.description }}</div>
                </div>

                <!-- Collaborators Section -->
                <div class="grid gap-2">
                  <div class="text-sm font-medium text-muted-foreground">Collaborators</div>
                  <div class="space-y-2">
                    <div v-for="(collaborator, index) in request.collaborators" 
                      :key="index"
                      class="flex items-center justify-between p-2 bg-muted rounded-md"
                    >
                      <div class="flex items-center gap-2">
                        <Avatar class="h-8 w-8">
                          <AvatarImage 
                            v-if="getAvatarProps(collaborator).showAvatar" 
                            :src="getAvatarProps(collaborator).src" 
                            :alt="getAvatarProps(collaborator).alt" 
                          />
                          <AvatarFallback class="bg-primary/10">
                            {{ getAvatarProps(collaborator).fallback }}
                          </AvatarFallback>
                        </Avatar>
                        <div class="flex flex-col">
                          <span class="text-sm font-medium">{{ collaborator.name }}</span>
                          <span class="text-xs text-muted-foreground">{{ getDisplayRole(collaborator.role) }}</span>
                        </div>
                      </div>
                      <Badge :variant="collaborator.permission === 'edit' ? 'default' : 'secondary'" class="capitalize text-xs px-4">
                        {{ collaborator.permission === 'edit' ? 'Can Edit' : 'View Only' }}
                      </Badge>
                    </div>
                    <div v-if="!request.collaborators?.length" class="text-sm text-muted-foreground">
                      No collaborators assigned
                    </div>
                  </div>
                </div>

                <!-- Files Section -->
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

                <!-- Quotation Section -->
                <div v-if="request.quotation?.have_quotation === 'true'" class="grid gap-2">
                  <div class="text-sm font-medium text-muted-foreground flex items-center gap-2">
                    <FileText class="h-4 w-4" />
                    Quotation Details
                  </div>
                  <div class="p-4 bg-muted rounded-md">
                    <div class="flex flex-col gap-2">
                      <div class="flex items-center justify-between">
                        <div class="space-y-1">
                          <p class="text-sm">Status: 
                            <Badge :variant="request.quotation.status === 'pending' ? 'warning' : 'success'">
                              {{ request.quotation.status }}
                            </Badge>
                          </p>
                          <p class="text-xs text-muted-foreground">
                            Processed by: {{ request.quotation.processed_by }}
                          </p>
                          <p class="text-xs text-muted-foreground">
                            Date: {{ request.quotation.processed_at }}
                          </p>
                        </div>
                        <Button 
                          @click="showQuotationDialog = true"
                          variant="outline"
                          size="sm"
                          class="gap-2"
                        >
                          <FileText class="h-4 w-4" />
                          View Details
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <div class="space-y-6">
            <ApproverRequestStatus 
              :request="request"
              @show-confirmation="handleStatusAction"
            />

            <RequestTimeline :request="request" />
          </div>
        </div>
      </template>
    </div>
  </AppLayout>
</template>