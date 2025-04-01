<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import { computed } from 'vue';

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
    <div class="flex h-full flex-col p-4">
        <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-white">
            Current Users
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-sm font-normal text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                {{ userCount }}
            </span>
        </h3>
        <div class="flex-1 min-h-0 overflow-y-auto">
            <div class="space-y-2">
                <div v-for="user in activeUsers" :key="user.id"
                    class="flex items-center justify-between rounded-lg bg-white p-3 shadow-sm dark:bg-gray-900">
                    <div class="flex items-center space-x-3">
                        <Avatar class="h-8 w-8 overflow-hidden rounded-lg">
                            <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" />
                            <AvatarFallback class="rounded-lg text-black dark:text-white">
                                {{ getInitials(user.name) }}
                            </AvatarFallback>
                        </Avatar>
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ user.name }}
                            </h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                                {{ displayRole(user.role) }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span 
                            class="capitalize inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset"
                            :class="{
                                'bg-green-700/20 text-green-500 ring-green-600/40 dark:bg-green-900/40 dark:text-green-300': user.status === 'active',
                                'bg-red-700/20 text-red-500 ring-red-600/40 dark:bg-red-900/40 dark:text-red-300': user.status === 'inactive'
                            }"
                        >
                            {{ user.status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>