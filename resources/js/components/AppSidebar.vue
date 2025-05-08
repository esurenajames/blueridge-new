<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarMenuSub, SidebarMenuSubItem } from '@/components/ui/sidebar';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Users, UserCheck, FileText } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const dashboardRoute = computed(() => {
    if (!user.value) return 'login';

    switch (user.value.role) {
        case 'admin':
            return 'admin.dashboard';
        case 'captain':
            return 'captain.dashboard';
        case 'secretary':
            return 'secretary.dashboard';
        case 'treasurer':
            return 'treasurer.dashboard';
        default:
            return 'dashboard';
    }
});

const mainNavItems = computed(() => {
    if (!dashboardRoute.value) return [];

    const items: NavItem[] = [{
        title: 'Dashboard',
        href: route(dashboardRoute.value),
        icon: LayoutGrid,
    }];

    if (user.value?.role === 'admin') {
        items.push({
            title: 'Users',
            href: route('admin.users'),
            icon: UserCheck,
        });
    }

    if (user.value?.role === 'official') {
        items.push({
            title: 'Requests',
            icon: FileText,
            collapsible: true,
            items: [
                {
                    title: 'View All',
                    href: route('requests.index'),
                    icon: Folder
                },
            ]
        });
    }

    if (user.value?.role === 'captain') {
        items.push({
            title: 'Requests',
            icon: FileText,
            collapsible: true,
            items: [
                {
                    title: 'View All',
                    href: route('captain.requests'),
                    icon: Folder,
                },
            ]
        });
    }

    if (user.value?.role === 'treasurer') {
        items.push({
            title: 'Budget Management',
            icon: FileText,
            collapsible: true,
            items: [
                {
                    title: 'Manage Budget',
                    href: route('requests.index'),
                    icon: Folder
                },
            ]
        });
    }

    return items;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link v-if="dashboardRoute" :href="route(dashboardRoute)" class>
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
