<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { FileText, Receipt, PlusCircle, ArrowRight } from 'lucide-vue-next';
import { ref } from 'vue';
import RequestForm from '@/components/barangay-officials/RequestForm.vue';

defineOptions({
    inheritAttrs: true
})

const showRequestDialog = ref(false);

const handleCloseRequest = () => {
  showRequestDialog.value = false;
};

const downloadDocumentation = () => {
    window.open('/storage/test.pdf', '_blank');
};
</script>

<template>
    <Card v-bind="$attrs">
        <div class="space-y-2">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/20">
                        <Receipt class="size-5 text-blue-500" />
                    </div>
                    Create Procurement
                </CardTitle>
                <CardDescription>
                    Create and manage procurement requests for your barangay
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div class="space-y-4">
                    <div class="relative rounded-lg border bg-card text-card-foreground">
                        <div class="absolute -top-2.5 left-3 px-2 bg-background text-xs font-medium text-muted-foreground">
                            Before you start
                        </div>
                        <div class="p-4 space-y-3">
                            <div 
                                class="flex items-center gap-2 rounded-lg bg-muted/50 p-3 cursor-pointer hover:bg-muted/70 transition-colors"
                                @click="downloadDocumentation"
                            >
                                <FileText class="size-5 text-blue-500 overflow-hidden" />
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium max-w-[250px] truncate ">Documentation and Requirements</span>
                                    <span class="text-xs text-muted-foreground hidden sm:block max-w-[250px] truncate ">Click here to view the process and guidelines</span>
                                </div>
                                <ArrowRight class="size-4 ml-auto text-muted-foreground" />
                            </div>
                        </div>
                    </div>
                    <Button 
                        variant="ghost" 
                        @click="showRequestDialog = true"
                        class="w-full p-8 gap-2 border-2 overflow-hidden border-muted hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 group"
                    >
                        <PlusCircle class="size-5 group-hover:text-blue-500 transition-colors" />
                        <span class="font-medium">Start New Request</span>
                        <span class="text-xs text-muted-foreground hidden sm:block max-w-[250px] truncate">Begin the procurement process</span>
                    </Button>
                </div>
            </CardContent>
        </div>
    </Card>

    <RequestForm
        v-if="showRequestDialog"
        :show="showRequestDialog"
        @close="handleCloseRequest"
    />

</template>