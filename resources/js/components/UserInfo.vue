<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import type { User } from '@/types';
import { computed } from 'vue';
import { getDisplayRole } from '@/utils/roles';
import { getAvatarProps } from '@/utils/avatar';

interface Props {
    user: User;
    showEmail?: boolean;
    showRole?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
    showRole: true,
});

const avatarProps = computed(() => getAvatarProps(props.user));
const displayRole = computed(() => getDisplayRole(props.user.role));
</script>

<template>
    <Avatar class="h-8 w-8 overflow-hidden rounded-lg">
        <AvatarImage 
            v-if="avatarProps.showAvatar" 
            :src="avatarProps.src" 
            :alt="avatarProps.alt" 
        />
        <AvatarFallback class="rounded-lg text-black dark:text-white">
            {{ avatarProps.fallback }}
        </AvatarFallback>
    </Avatar>

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-medium">{{ user.name }}</span>
        <span v-if="showRole" class="truncate text-xs text-muted-foreground capitalize">
            {{ displayRole }}
        </span>
        <span v-if="showEmail" class="truncate text-xs text-muted-foreground">
            {{ user.email }}
        </span>
    </div>
</template>