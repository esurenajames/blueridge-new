<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    items: NavItem[];
}>();

const page = usePage<SharedData>();
</script>

<template>
    <SidebarGroup class="px-3 py-2">
        <SidebarGroupLabel class="text-sm mb-3">Platform</SidebarGroupLabel>
        <SidebarMenu class="space-y-2">
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton 
                    as-child 
                    :is-active="item.href === page.url"
                    :tooltip="item.title"
                    class="p-3 text-sm"
                >
                    <Link :href="item.href" class="flex items-center space-x-3">
                        <component :is="item.icon" class="h-5 w-5" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>