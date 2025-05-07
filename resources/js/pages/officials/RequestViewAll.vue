<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, router } from '@inertiajs/vue3';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ref, computed, watch } from 'vue';
import { type Request } from '@/types';
import { Clock, CheckCircle, XCircle, AlertCircle, LayoutGrid, ClipboardList, FileSpreadsheet, ShoppingCart, ShoppingBag, History, Eye, Edit2, Trash2, MoreVertical } from 'lucide-vue-next';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'View All Requests',
        href: route('requests.index'),
    },
];

interface RequestsByTab {
    all: { data: Request[] };
    form: { data: Request[] };
    quotation: { data: Request[] };
    'purchase-request': { data: Request[] };
    'purchase-order': { data: Request[] };
    history: { data: Request[] };
}

const { requests, initialTab } = usePage().props as {
    requests: RequestsByTab;
    initialTab: string;
};

const getStatusConfig = (status: string) => {
    switch (status) {
        case 'pending':
            return { icon: Clock, class: 'text-yellow-500', badge: 'warning' };
        case 'approved':
            return { icon: CheckCircle, class: 'text-green-500', badge: 'success' };
        case 'declined':
            return { icon: XCircle, class: 'text-red-500', badge: 'destructive' };
        default:
            return { icon: AlertCircle, class: 'text-gray-500', badge: 'secondary' };
    }
};

const tabs = [
    { id: 'all', label: 'View All', count: requests.all.data.length, icon: LayoutGrid },
    { id: 'form', label: 'Request Form', count: requests.form.data.length, icon: ClipboardList },
    { id: 'quotation', label: 'Quotation', count: requests.quotation.data.length, icon: FileSpreadsheet },
    { id: 'purchase-request', label: 'Purchase Request', count: requests['purchase-request'].data.length, icon: ShoppingCart },
    { id: 'purchase-order', label: 'Purchase Order', count: requests['purchase-order'].data.length, icon: ShoppingBag },
    { id: 'history', label: 'History', count: requests.history.data.length, icon: History },
];

const currentTab = ref(initialTab || 'all');

const filteredRequests = computed(() => {
    return requests[currentTab.value as keyof RequestsByTab].data;
});

const handleCardClick = (requestId: number) => {
    router.visit(route('requests.view', { id: requestId }));
};

watch(currentTab, (newTab) => {
    if (newTab !== initialTab) {
        router.visit(route('requests.index', { tab: newTab }), { replace: true, preserveState: true });
    }
});

const page = usePage();
const user = computed(() => {
    return page.props.auth.user;
});
</script>

<template>
    <Head title="View All Requests" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col p-6 w-full">
            <div class="flex flex-col mx-auto px-0 sm:px-10 w-full">
                <Tabs v-model="currentTab" class="w-full">
                    <TabsList class="flex overflow-x-auto whitespace-nowrap scrollbar-hide">
                        <TooltipProvider>
                            <TabsTrigger 
                                v-for="tab in tabs" 
                                :key="tab.id"
                                :value="tab.id"
                                class="shrink-0"
                            >
                                <Tooltip>
                                    <TooltipTrigger class="flex items-center gap-2">
                                        <component :is="tab.icon" class="h-4 w-4" />
                                        <span class="hidden sm:inline">{{ tab.label }}</span>
                                        <Badge 
                                            variant="secondary" 
                                            class="ml-1 hidden sm:inline-flex"
                                        >
                                            {{ tab.count }}
                                        </Badge>
                                    </TooltipTrigger>
                                    <TooltipContent class="flex items-center gap-2 sm:hidden">
                                        <span>{{ tab.label }}</span>
                                        <Badge variant="secondary" v-if="tab.count > 0">
                                            {{ tab.count }}
                                        </Badge>
                                    </TooltipContent>
                                </Tooltip>
                            </TabsTrigger>
                        </TooltipProvider>
                    </TabsList>

                    <TabsContent 
                        v-for="tab in tabs" 
                        :key="tab.id"
                        :value="tab.id"
                        class="mt-6"
                    >
                        <div class="flex flex-col space-y-4">
                            <template v-if="filteredRequests.length">
                                <Card 
                                    v-for="request in filteredRequests" 
                                    :key="request.id"
                                    class="hover:bg-muted/50 transition-colors group cursor-pointer"
                                    @click="handleCardClick(request.id)"
                                >
                                    <CardHeader>
                                        <div class="flex items-center justify-between">
                                            <div class="flex flex-col space-y-1.5">
                                                <div class="flex items-center gap-2">
                                                    <CardTitle class="text-lg font-semibold">{{ request.title }}</CardTitle>
                                                    <Badge variant="outline" class="capitalize text-xs font-medium">
                                                        {{ request.category }}
                                                    </Badge>
                                                </div>
                                                <CardDescription class="text-sm">Request #{{ request.id }}</CardDescription>
                                            </div>
                                            <Badge 
                                                :variant="getStatusConfig(request.status).badge"
                                                class="transition-transform group-hover:scale-105"
                                            >
                                                <component 
                                                    :is="getStatusConfig(request.status).icon" 
                                                    class="h-3.5 w-3.5 mr-1"
                                                />
                                                {{ request.status }}
                                            </Badge>
                                        </div>
                                    </CardHeader>
                                    <CardContent>
                                        <div class="flex flex-col space-y-4">
                                            <p class="text-sm text-muted-foreground line-clamp-2">
                                                {{ request.description }}
                                            </p>

                                            <!-- Collaborators section -->
                                            <div class="flex items-center gap-3" v-if="request.collaborators?.length">
                                                <div class="flex -space-x-2">
                                                    <div 
                                                        v-for="collaborator in request.collaborators" 
                                                        :key="collaborator.id"
                                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-primary/10 ring-2 ring-background transition-transform hover:scale-110"
                                                        :title="collaborator.name"
                                                    >
                                                        <span class="text-xs font-medium">
                                                            {{ collaborator.name.charAt(0) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <span class="text-xs text-muted-foreground/75">
                                                    {{ request.collaborators.length }} collaborator{{ request.collaborators.length !== 1 ? 's' : '' }}
                                                </span>
                                            </div>

                                            <!-- Footer section -->
                                            <div class="flex items-center justify-between pt-4 border-t border-border/50">
                                                <div class="flex items-center gap-2 text-xs text-muted-foreground/75">
                                                    <span>Created by {{ request.created_by }}</span>
                                                    <span>•</span>
                                                    <span>{{ request.created_at }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            </template>
                            
                            <div v-else>
                                <Card class="w-full">
                                    <CardContent class="flex flex-col items-center justify-center py-10 text-muted-foreground">
                                        <component :is="tab.icon" class="h-12 w-12 mb-4 opacity-50" />
                                        <p class="text-lg font-medium capitalize">No {{ tab.label }} Found</p>
                                        <p class="text-sm">There are currently no requests under this category.</p>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </TabsContent>
                </Tabs>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>