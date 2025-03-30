<script setup lang="ts">
interface User {
    id: number;
    name: string;
    avatar: string;
    last_active_at: string;
    actions_count: number;
}

const props = defineProps<{
    activeUsers: User[];
}>();
</script>

<template>
    <div class="h-full p-4">
        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
            Active Users This Week
        </h3>
        <div class="space-y-4">
            <div v-for="user in activeUsers" :key="user.id"
                class="flex items-center justify-between rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                <div class="flex items-center space-x-3">
                    <img :src="user.avatar" :alt="user.name"
                        class="h-10 w-10 rounded-full object-cover" />
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white">
                            {{ user.name }}
                        </h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Last active: {{ user.last_active_at }}
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800 dark:bg-green-900 dark:text-green-100">
                        {{ user.actions_count }} actions
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>