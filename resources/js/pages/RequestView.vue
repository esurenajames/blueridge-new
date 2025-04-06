<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Clock, CheckCircle2, XCircle, User, Calendar, CheckCircle, AlertCircle, Circle, Check, Circle as Dot, FileText, Download, RefreshCw } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { computed } from 'vue';
import { Stepper, StepperDescription, StepperIndicator, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from '@/components/ui/stepper';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Form Request',
        href: '/admin/dashboard',
    },
];

const request = {
    id: 1,
    title: 'Office Supplies Request',
    type: 'request-form',
    status: 'voided',
    created_at: '2025-04-06',
    description: 'Request for new office supplies including paper, pens, and folders',
    amount: 5000,
    category: 'Office Supplies',
    collaborators: [
        { name: 'Louie Saguinsin', role: 'Barangay Official' },
        { name: 'Crisha Reyes', role: 'Barangay Official' }
    ],
    timeline: [
        { status: 'Created', date: '2025-04-06', by: 'Crisha Reyes' },
        { status: 'Processed', date: '2025-04-06', by: 'Crisha Reyes' },
    ],
    files: [
        { name: 'quotation.pdf', size: 1024000, uploaded_at: '2025-04-06' },
        { name: 'requirements.docx', size: 512000, uploaded_at: '2025-04-06' }
    ],
    remarks: 'This request is for the monthly office supplies replenishment. Please process as soon as possible.'
};

const statusConfig = computed(() => {
    switch (request.status) {
        case 'approved':
            return {
                icon: CheckCircle,
                class: 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400',
                iconClass: 'text-green-600 dark:text-green-400',
                badge: 'success'
            };
        case 'pending':
            return {
                icon: Clock,
                class: 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400',
                iconClass: 'text-yellow-600 dark:text-yellow-400',
                badge: 'warning'
            };
        case 'declined':
            return {
                icon: XCircle,
                class: 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
                iconClass: 'text-red-600 dark:text-red-400',
                badge: 'destructive'
            };
        case 'voided':
            return {
                icon: XCircle,
                class: 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
                iconClass: 'text-red-600 dark:text-red-400',
                badge: 'destructive'
            };
        default:
            return {
                icon: AlertCircle,
                class: 'bg-slate-50 dark:bg-slate-900/20',
                iconClass: 'text-slate-600 dark:text-slate-400',
                badge: 'secondary'
            };
    }
});

const timelineSteps = computed(() => 
    request.timeline.map((event, index) => ({
        step: index + 1,
        title: event.status,
        description: `By ${event.by} on ${event.date}`,
        state: index === request.timeline.length - 1 ? 'active' : 'completed'
    }))
);

</script>

<template>
    <Head title="Request View" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Left Card -->
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
                            <!-- Description Section -->
                            <div class="grid gap-2">
                                <div class="text-sm font-medium text-muted-foreground">Description</div>
                                <div class="text-sm">{{ request.description }}</div>
                            </div>

                            <!-- Amount Section -->
                            <div class="grid gap-2">
                                <div class="text-sm font-medium text-muted-foreground">Amount</div>
                                <div class="text-sm">₱{{ request.amount.toLocaleString() }}</div>
                            </div>

                            <!-- Collaborators Section -->
                            <div class="grid gap-2">
                                <div class="text-sm font-medium text-muted-foreground">Collaborators</div>
                                <div class="space-y-2">
                                    <div v-for="(collaborator, index) in request.collaborators" 
                                        :key="index"
                                        class="flex items-center gap-2 p-2 bg-muted rounded-md"
                                    >
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                                            <Users class="h-4 w-4 text-primary" />
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
                                        <Button variant="ghost" size="icon" class="h-8 w-8">
                                            <Download class="size-4" />
                                        </Button>
                                    </div>
                                    <div v-if="!request.files.length" class="text-sm text-muted-foreground">
                                        No files attached
                                    </div>
                                </div>
                            </div>

                            <!-- Remarks Section -->
                            <div class="grid gap-3">
                                <div class="text-sm font-medium text-muted-foreground">Remarks</div>
                                <div class="p-4 bg-muted/50 rounded-lg text-sm">
                                    {{ request.remarks || 'No remarks provided' }}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Status Card -->
                    <Card>
                        <CardHeader class="pb-6">
                            <CardTitle>Status</CardTitle>
                            <CardDescription>Current request status</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <!-- Status Alert -->
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
                                        {{ 
                                            request.status === 'draft' ? 'This request is still in draft mode.' :
                                            request.status === 'pending' ? 'This request is awaiting approval.' :
                                            request.status === 'approved' ? 'This request has been approved.' :
                                            request.status === 'declined' ? 'This request has been declined.' :
                                            request.status === 'voided' ? 'This request has been voided.' :
                                            'Status unknown.'
                                        }}
                                    </p>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col gap-3 pt-2">
                                <Button 
                                    v-if="request.status !== 'declined' && request.status !== 'pending' && request.status !== 'voided'"
                                    class="w-full gap-2"
                                >
                                    <CheckCircle2 class="h-4 w-4" />
                                    Process Request
                                </Button>
                                <Button 
                                    v-if="request.status === 'declined' && request.status !== 'voided'"
                                    class="w-full gap-2"
                                >
                                    <CheckCircle2 class="h-4 w-4" />
                                    Re-process Request
                                </Button>
                                <Button 
                                    v-if="request.status === 'declined' && request.status !== 'voided'"
                                    variant="secondary" 
                                    class="w-full gap-2"
                                >
                                    <RefreshCw class="h-4 w-4" />
                                    Resubmit Documents
                                </Button>
                                
                                <Button 
                                    v-if="request.status !== 'voided'"
                                    variant="destructive" 
                                    class="w-full gap-2"
                                >
                                    <XCircle class="h-4 w-4" />
                                    Void Request
                                </Button>

                                <!-- Optional: Show message when request is voided -->
                                <div 
                                    v-if="request.status === 'voided'"
                                    class="text-sm text-muted-foreground text-center p-2"
                                >
                                    This request has been voided and cannot be modified.
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Timeline Card -->
                    <Card>
                        <CardHeader class="pb-6">
                            <CardTitle>Timeline</CardTitle>
                            <CardDescription>Status updates and approvals</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Stepper 
                            orientation="vertical" 
                            class="mx-auto flex w-full max-w-md flex-col justify-start gap-6"
                            :model-value="timelineSteps.length"
                        >
                            <StepperItem
                                v-for="(event, index) in timelineSteps"
                                :key="index"
                                v-slot="{ state }"
                                class="relative flex w-full items-start gap-3 cursor-default pointer-events-none select-none"
                                :step="event.step"
                            >
                                <StepperSeparator
                                    v-if="index !== timelineSteps.length - 1"
                                    class="absolute left-[14px] top-[28px] block h-[calc(100%+1.5rem)] w-0.5 shrink-0 rounded-full bg-muted group-data-[state=completed]:bg-primary"
                                />

                                <StepperTrigger as-child>
                                    <Button
                                        :variant="event.state === 'completed' || event.state === 'active' ? 'default' : 'outline'"
                                        size="icon"
                                        class="z-10 rounded-full shrink-0 h-7 w-7"
                                        :class="[event.state === 'active' && 'ring-2 ring-ring ring-offset-2 ring-offset-background']"
                                    >
                                        <Check v-if="event.state === 'completed'" class="size-4" />
                                        <Circle v-if="event.state === 'active'" class="size-4" />
                                        <Dot v-if="event.state === 'inactive'" class="size-4" />
                                    </Button>
                                </StepperTrigger>

                                <div class="flex flex-col gap-1">
                                    <StepperTitle
                                        :class="[event.state === 'active' && 'text-primary']"
                                        class="text-sm font-semibold transition"
                                    >
                                        {{ event.title }}
                                    </StepperTitle>
                                    <StepperDescription
                                        class="flex items-center gap-2 text-xs text-muted-foreground"
                                    >
                                        <div class="flex items-center gap-2">
                                            <Calendar class="h-3 w-3" />
                                            <span>{{ request.timeline[index].date }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <User class="h-3 w-3" />
                                            <span>{{ request.timeline[index].by }}</span>
                                        </div>
                                    </StepperDescription>
                                </div>
                            </StepperItem>
                        </Stepper>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>