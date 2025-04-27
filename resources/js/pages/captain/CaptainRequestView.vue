<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { CheckCircle2, XCircle, RotateCcw, FileText, Download, UserIcon } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { ref, watch } from 'vue';
import { useToast } from '@/components/ui/toast';
import Confirmation from '@/components/Confirmation.vue';
import { useStatusConfig } from '@/composables/useStatusConfig';
import RequestTimeline from '@/components/RequestTimeline.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { getAvatarProps } from '@/utils/avatar';
import { getDisplayRole } from '@/utils/roles';
import ApproverRequestStatus from '@/components/ApproverRequestStatus.vue';

const { toast } = useToast();
const { getStatusConfig } = useStatusConfig();

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
    status: string;
    created_at: string;
    created_by: string;
    processed_by?: string;
    processed_at?: string;
    collaborators?: Array<{
      name: string;
      role: string;
      permission: string;
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

const approveRequest = () => {
  router.post(route('captain.requests.approve', { id: request.value.id }), {}, {
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: "Success",
        description: "Request has been approved",
        variant: "success",
      });
    },
  });
};

const declineRequest = () => {
  router.post(route('captain.requests.decline', { id: request.value.id }), {}, {
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: "Success",
        description: "Request has been declined",
        variant: "success",
      });
    },
  });
};

const returnRequest = () => {
  router.post(route('captain.requests.return', { id: request.value.id }), {}, {
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: "Success",
        description: "Request has been returned",
        variant: "success",
      });
    },
  });
};

const handleStatusAction = (title: string, description: string, action: string) => {
  switch (action) {
    case 'approve':
      showConfirmation(title, description, approveRequest);
      break;
    case 'decline':
      showConfirmation(title, description, declineRequest);
      break;
    case 'return':
      showConfirmation(title, description, returnRequest);
      break;
  }
};

const downloadFile = (file: { name: string }) => {
  const url = route('requests.download-file', { id: props.request.id, filename: file.name });
  window.open(url, '_blank');
};
</script>

<template>
  <Head title="Request Details" />

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
    </div>
  </AppLayout>
</template>