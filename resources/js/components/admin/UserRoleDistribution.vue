<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { UserCog, ScrollText, BadgeDollarSign, Shield, Users2 } from 'lucide-vue-next';
import { ScrollArea } from '@/components/ui/scroll-area';

defineProps<{
    roles: {
        [key: string]: number;
    };
}>();

const getRoleIcon = (role: string) => {
    switch (role.toLowerCase()) {
        case 'admin':
            return Shield;
        case 'secretary':
            return ScrollText;
        case 'treasurer':
            return BadgeDollarSign;
        case 'captain':
            return UserCog;
        case 'official':
            return Users2;
        default:
            return Users2;
    }
};

const getRoleColor = (role: string) => {
    switch (role.toLowerCase()) {
        case 'admin':
            return 'text-purple-600 bg-purple-100 dark:bg-purple-950/30';
        case 'secretary':
            return 'text-blue-600 bg-blue-100 dark:bg-blue-950/30';
        case 'treasurer':
            return 'text-emerald-600 bg-emerald-100 dark:bg-emerald-950/30';
        case 'captain':
            return 'text-amber-600 bg-amber-100 dark:bg-amber-950/30';
        case 'official':
            return 'text-gray-600 bg-gray-200 dark:bg-gray-950/30';
        default:
            return 'text-gray-600 bg-gray-200 dark:bg-gray-950/30';
    }
};

</script>

<template>
    <Card class="h-full overflow-hidden">
        <div class="space-y-2">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/20">
                        <Users2 class="size-5 text-red-500" />
                    </div>
                    Role Distribution
                </CardTitle>
                <CardDescription>
                    Overview of user roles across the organization
                </CardDescription>
            </CardHeader>
            <CardContent>
                <ScrollArea class="h-[180px] mb-10">
                    <div class="grid gap-4">
                        <div v-for="(count, role) in roles" 
                             :key="role" 
                             class="flex items-center justify-between p-3 rounded-lg"
                             :class="getRoleColor(role)">
                            <div class="flex items-center gap-3">
                                <component :is="getRoleIcon(role)" 
                                         class="h-5 w-5" 
                                         :class="getRoleColor(role)" />
                                <span class="text-sm font-medium capitalize">{{ role }}</span>
                            </div>
                            <span class="text-lg font-semibold" :class="getRoleColor(role)">{{ count }}</span>
                        </div>
                    </div>
                </ScrollArea>
            </CardContent>
        </div>
    </Card>
</template>