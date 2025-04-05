<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Users, UserCheck, UserX } from 'lucide-vue-next';

defineProps<{
    total: number;
    active: number;
    inactive: number;
}>();
</script>

<template>
    <Card class="h-full overflow-hidden">
        <div class="space-y-2">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/20">
                        <Users class="size-5 text-blue-500" />
                    </div>
                    User Statistics
                </CardTitle>
                <CardDescription>
                    Overview of all registered users and their current account status
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-3 gap-4">
                    <div class="flex flex-col items-center space-y-2 p-3 rounded-lg bg-blue-100 dark:bg-blue-950/30">
                        <Users class="h-6 w-6 text-blue-600" />
                        <p class="text-2xl font-semibold text-blue-600">{{ total }}</p>
                        <p class="text-xs text-muted-foreground">Total Users</p>
                    </div>
                    <div class="flex flex-col items-center space-y-2 p-3 rounded-lg bg-green-100 dark:bg-green-950/30">
                        <UserCheck class="h-6 w-6 text-green-600" />
                        <p class="text-2xl font-semibold text-green-600">{{ active }}</p>
                        <p class="text-xs text-muted-foreground">Active</p>
                    </div>
                    <div class="flex flex-col items-center space-y-2 p-3 rounded-lg bg-red-100 dark:bg-red-950/30">
                        <UserX class="h-6 w-6 text-red-600" />
                        <p class="text-2xl font-semibold text-red-600">{{ inactive }}</p>
                        <p class="text-xs text-muted-foreground">Inactive</p>
                    </div>
                </div>
            </CardContent>
        </div>
    </Card>
</template>