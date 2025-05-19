<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Request } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Edit2, FileText, File, Download, ChevronDown } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { computed, ref, watch } from 'vue';
import { useToast } from '@/components/ui/toast';
import Confirmation from '@/components/Confirmation.vue';
import { getDisplayRole } from '@/utils/roles';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { getAvatarProps } from '@/utils/avatar';
import EditRequestForm from '@/components/barangay-officials/EditRequestForm.vue';
import RequestTimeline from '@/components/RequestTimeline.vue';
import RequestStatus from '@/components/RequestStatus.vue';
import QuotationForm from '@/components/barangay-officials/QuotationForm.vue';
import ViewQuotationDialog from '@/components/ViewQuotationDialog.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';


const { toast } = useToast();
const showEditModal = ref(false);
const showQuotationModal = ref(false);
const quotationData = ref(null);
const showQuotationDialog = ref(false);
const confirmationState = ref({
  show: false,
  title: '',
  description: '',
  action: null as (() => void) | null
});

const props = defineProps<{
  request: {
    data: Request;
  };
  userPermission: string;
}>();

const page = usePage();
const activeUsers = page.props.activeUsers;

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

const handleEdit = () => {
  showEditModal.value = true;
};

const handleEditClose = () => {
  showEditModal.value = false;
};

const handleQuotationClose = () => {
  showQuotationModal.value = false;
};

const request = ref(props.request.data);

const exportAsPDF = (type: 'quotation' | 'form' | 'request') => {
  // TODO: Implement export functionality
  console.log(`Exporting ${type} as PDF`);
};

watch(
  () => props.request.data,
  (newVal) => {
    request.value = newVal;
  }
);

const canEdit = computed(() => {
  return props.userPermission === 'owner' || props.userPermission === 'edit';
});

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

const processRequest = () => {
  router.post(route('requests.process', { id: request.value.id }), {}, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: (page) => {
      if (page.props.flash?.request?.data?.status) {
        request.value.status = page.props.flash.request.data.status;
      }
      toast({
        title: "Success",
        description: page.props.flash?.success ?? "Request processed successfully.",
        variant: "success",
      });
    },
  });
};

const processPurchaseRequest = () => {
  router.post(route('requests.process-purchase', { id: request.value.id }), {}, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: (page) => {
      if (page.props.flash?.request?.data?.status) {
        request.value = page.props.flash.request.data;
      }
      toast({
        title: "Success",
        description: page.props.flash?.success ?? "Purchase request processed successfully.",
        variant: "success",
      });
    },
  });
};

const reprocessRequest = () => {
  router.post(route('requests.reprocess', { id: request.value.id }), {}, {
    preserveScroll: true,
    onSuccess: (page) => {
      if (page.props.flash?.request?.data?.status) {
        request.value.status = page.props.flash.request.data.status;
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
  const form = useForm({
    name: request.value.title,
    category: request.value.category,
    description: request.value.description,
    collaborators: request.value.collaborators,
    files: [],
    removedFiles: []
  });

  form.post(route('requests.resubmit', { id: request.value.id }), {
    preserveScroll: true,
    onSuccess: (page) => {
      if (page.props.flash?.request?.data) {
        request.value = page.props.flash.request.data;
      }
      toast({
        title: "Success",
        description: page.props.flash?.success ?? "Documents have been resubmitted",
        variant: "success",
      });
      showEditModal.value = false;
    },
    onError: (errors) => {
      toast({
        title: "Error",
        description: Object.values(errors)[0] as string || "Failed to resubmit request",
        variant: "destructive",
      });
    }
  });
};

const voidRequest = () => {
  router.post(route('requests.void', { id: request.value.id }), {}, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: (page) => {
      if (page.props.flash?.request?.data?.status) {
        request.value.status = page.props.flash.request.data.status;
      }
      toast({
        title: "Success",
        description: page.props.flash?.success ?? "Request has been voided.",
        variant: "success",
      });
    },
  });
};

const handleStatusAction = (title: string, description: string, action: string, data?: any) => {
  switch (action) {
    case 'process':
      showConfirmation(title, description, processRequest);
      break;
    case 'process-purchase-request':
      showConfirmation(title, description, processPurchaseRequest);
      break;
    case 'reprocess':
      showConfirmation(title, description, reprocessRequest);
      break;
    case 'resubmit':
      showConfirmation(title, description, () => {
        showEditModal.value = true;
      });
      break;
    case 'void':
      showConfirmation(title, description, voidRequest);
      break;
    case 'show-quotation':
      quotationData.value = data;
      showQuotationModal.value = true;
      break;
  }
};

const downloadFile = (file: { name: string }) => {
  const url = route('requests.download-file', { id: request.value.id, filename: file.name });
  window.open(url, '_blank');
};

const downloadPurchaseRequestPDF = () => {
  const url = route('requests.purchase-request-pdf', { id: request.value.id });
  window.open(url, '_blank');
};

const downloadAbstractOfCanvass = () => {
  const url = route('requests.abstract-of-canvass', { id: request.value.id });
  window.open(url, '_blank');
};
</script>

<template>
  <Head title="Request View" />

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

        <EditRequestForm
          v-if="showEditModal"
          :show="showEditModal"
          :request="request"
          :active-users="activeUsers ?? []"
          :is-resubmit="request.status === 'returned'"
          @close="handleEditClose"  
          @update:request="(newRequest) => request = newRequest.data"
        />

        <QuotationForm
          v-if="showQuotationModal"
          :show="showQuotationModal"
          :request-id="request.id"
          :quotation="quotationData" 
          @close="handleQuotationClose"
        />

        <ViewQuotationDialog
          v-if="request.quotation"
          v-model:show="showQuotationDialog"
          :quotation="request.quotation"
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
                <Button 
                  v-if="request.status === 'draft' && canEdit"
                  variant="default"
                  class="gap-2"
                  @click="handleEdit"
                >
                  <Edit2 class="h-4 w-4" />
                  Edit Request
                </Button>
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
                        <Download class="h-4 w-4" />
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

                <div v-if="request.progress === 'Purchase Request'" class="grid gap-2 mt-4">
                  <div class="text-sm font-medium text-muted-foreground flex items-center gap-2">
                    <FileText class="h-4 w-4" />
                    Abstract of Canvass
                  </div>
                  <div class="space-y-2">
                    <div class="flex items-center gap-2 p-2 bg-muted rounded-md">
                      <FileText class="h-4 w-4 text-primary ml-2" />
                      <div class="flex-1">
                        <span class="text-sm">abstract-of-canvass.pdf</span>
                        <span class="text-xs text-muted-foreground ml-2">(System Generated)</span>
                      </div>
                      <Button 
                        variant="ghost" 
                        size="icon" 
                        class="h-8 w-8 ml-auto" 
                        @click="downloadAbstractOfCanvass"
                      >
                        <Download class="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                </div>

                <div v-if="request.purchaseRequest?.have_supplier_approval === true" class="grid gap-2">
                  <div class="text-sm font-medium text-muted-foreground flex items-center gap-2">
                    <FileText class="h-4 w-4" />
                    Purchase Request Details
                  </div>
                  <div class="space-y-2">
                    <div class="flex items-center gap-2 p-2 bg-muted rounded-md">
                      <FileText class="h-4 w-4 text-primary ml-2" />
                      <div class="flex-1">
                        <span class="text-sm">Purchase-request.pdf</span>
                        <span class="text-xs text-muted-foreground ml-2">(System Generated)</span>
                      </div>
                      <Button 
                        variant="ghost" 
                        size="icon" 
                        class="h-8 w-8 ml-auto" 
                        @click="downloadPurchaseRequestPDF"
                      >
                        <Download class="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                </div>

                
              </div>
            </CardContent>
          </Card>

          <div class="space-y-6">
            <RequestStatus 
              :request="request"
              :can-edit="canEdit"
              @show-confirmation="handleStatusAction"
              @show-edit="handleEdit"
            />
            <RequestTimeline :request="request" />
          </div>
        </div>
      </template>
    </div>
  </AppLayout>
</template>