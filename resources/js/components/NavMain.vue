<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarMenuSub, SidebarMenuSubItem } from '@/components/ui/sidebar';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    items: NavItem[];
}>();

const page = usePage<SharedData>();
const openItems = ref<Record<string, boolean>>({});
</script>

<template>
    <SidebarGroup class="px-2 py-2">
        <SidebarGroupLabel class="text-sm mb-3">Platform</SidebarGroupLabel>
        <SidebarMenu class="">
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <template v-if="item.collapsible">
                    <Collapsible v-model:open="openItems[item.title]">
                        <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title" class="p-3 text-sm w-full">
                            <CollapsibleTrigger>
                                <div class="flex items-center justify-between w-full">
                                    <div class="flex items-center space-x-3">
                                        <component :is="item.icon" class="h-4 w-4" />
                                        <span class="pl-2">{{ item.title }}</span>
                                    </div>
                                    <ChevronRight class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-90': openItems[item.title] }"/>
                                </div>
                            </CollapsibleTrigger>
                        </SidebarMenuButton>
                        <CollapsibleContent class="mt-1">   
                            <SidebarMenuSub>
                                <SidebarMenuSubItem v-for="subItem in item.items" :key="subItem.title">
                                    <SidebarMenuButton as-child :is-active="subItem.href === page.url" :tooltip="subItem.title" class="p-3 text-sm w-full">
                                        <Link :href="subItem.href" class="flex items-center space-x-3 w-full">
                                            <component :is="subItem.icon" class="h-4 w-4" />
                                            <span>{{ subItem.title }}</span>
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </Collapsible>
                </template>
                <template v-else>
                    <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title" class="p-3 text-sm w-full">
                        <Link :href="item.href" class="flex items-center space-x-3 w-full">
                            <component :is="item.icon" class="h-5 w-5" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </template>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>