<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import { useInitials } from '@/composables/useInitials';
import { computed } from 'vue';
import { getDisplayRole } from '@/utils/roles';
import { Users2 } from 'lucide-vue-next';
import { getAvatarProps } from '@/utils/avatar';

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
const avatarProps = computed(() => getAvatarProps(props.user));
</script>

<template>
    <Card class="h-full overflow-hidden">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/20">
                        <Users2 class="size-5 text-emerald-500" />
                    </div>
                    Current Active Users 
                    <Badge variant="secondary" class="text-sm bg-emerald-100 dark:bg-emerald-900/20">
                        {{ userCount }}
                    </Badge>
                </CardTitle>
                <CardDescription class="text-sm text-muted-foreground">
                    List of currently active and registered barangay officials, including how long ago each account was created.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <ScrollArea class="h-[400px] pb-2">
                <div class="space-y-2">
                    <div v-for="user in activeUsers" 
                        :key="user.id"
                        class="flex items-center justify-between rounded-lg bg-muted/40 p-3">
                        <!-- User info section -->
                        <div class="flex items-center space-x-3 flex-1">
                                <Avatar>
                                    <AvatarImage 
                                        v-if="getAvatarProps(user).showAvatar" 
                                        :src="getAvatarProps(user).src" 
                                        :alt="getAvatarProps(user).alt" 
                                    />
                                    <AvatarFallback>
                                        {{ getAvatarProps(user).fallback }}
                                    </AvatarFallback>
                                </Avatar>
                            <div>
                                <p class="text-sm font-medium leading-none">
                                    {{ user.name }}
                                </p>
                                <p class="text-sm text-muted-foreground capitalize">
                                    {{ getDisplayRole(user.role) }}
                                </p>
                            </div>
                        </div>

                        <!-- Status and time section -->
                        <div class="flex items-center gap-4">
                            <div class="hidden md:block min-w-24 text-right">
                                <p class="text-xs text-muted-foreground">
                                    {{ user.created_ago }}
                                </p>
                            </div>
                            <Badge 
                                :variant="user.status === 'active' ? 'success' : 'destructive'"
                                class="capitalize whitespace-nowrap"
                            >
                                {{ user.status }}
                            </Badge>
                            
                        </div>
                    </div>
                </div>
            </ScrollArea>
        </CardContent>
    </Card>
</template>