<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { FileText, Receipt, CreditCard, PlusCircle } from 'lucide-vue-next';
</script>

<template>
    <Card class="h-full transition-all hover:border-blue-500/50 hover:shadow-md dark:hover:border-blue-400/50">
        <CardContent class="flex h-full flex-col justify-between p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                    <Receipt class="size-6 text-blue-500" />
                </div>
                <div>
                    <h3 class="font-semibold">Create Procurement Request</h3>
                    <p class="text-sm text-muted-foreground">New fund request form</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="grid gap-4">
                    <div class="flex items-center gap-2 rounded-lg bg-muted/50 p-3">
                        <FileText class="size-5 text-blue-500" />
                        <span class="text-sm">Documentation</span>
                    </div>
                </div>
            </div>

            <Button 
                variant="ghost" 
                class="mt-6 w-full p-10 gap-2 border-2 border-dashed border-muted hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20"
            >
                <PlusCircle class="size-5" />
                Start New Request
            </Button>
        </CardContent>
    </Card>
</template>