<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import { useInitials } from '@/composables/useInitials';
import { computed } from 'vue';
import { Users2 } from 'lucide-vue-next';

const { getInitials } = useInitials();

const props = defineProps<{
    activeUsers: Array<{
        id: number;
        name: string;
        role: string;
        avatar?: string;
        status: string;
    }>;
}>();

const userCount = computed(() => props.activeUsers.length);
const displayRole = (role: string) => {
    if (role.toLowerCase() === 'official') {
        return 'Barangay Official';
    }
    return role;
};
</script>

<template>
    <Card class="h-full overflow-hidden">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/20">
                        <Users2 class="size-5 text-emerald-500" />
                    </div>
                    Current Users 
                    <Badge variant="secondary" class="text-sm bg-emerald-100 dark:bg-emerald-900/20">
                        {{ userCount }}
                    </Badge>
                </CardTitle>
                <CardDescription class="text-sm text-muted-foreground">
                    List of currently registered barangay officials and their status
                </CardDescription>
            </CardHeader>
            <CardContent>
                <ScrollArea class="h-[400px] pb-2">
                    <div class="space-y-2">
                        <div v-for="user in activeUsers" :key="user.id"
                            class="flex items-center justify-between rounded-lg bg-muted/40 p-3">
                            <div class="flex items-center space-x-3">
                                <Avatar>
                                    <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" />
                                    <AvatarFallback>
                                        {{ getInitials(user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div>
                                    <p class="text-sm font-medium leading-none">
                                        {{ user.name }}
                                    </p>
                                    <p class="text-sm text-muted-foreground capitalize">
                                        {{ displayRole(user.role) }}
                                    </p>
                                </div>
                            </div>
                            <Badge variant="secondary" :variant="user.status === 'active' ? 'success' : 'destructive'"
                                class="capitalize">
                                {{ user.status }}
                            </Badge>
                        </div>
                    </div>
                </ScrollArea>
            </CardContent>
    </Card>
</template>