<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import { Users, User, FileText, Calculator, ShoppingCart, Receipt } from 'lucide-vue-next';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

defineProps<{
  requestStats: {
    single: {
      requestForms: number;
      quotations: number;
      purchaseRequests: number;
      purchaseOrders: number;
    },
    collaborative: {
      requestForms: number;
      quotations: number;
      purchaseRequests: number;
      purchaseOrders: number;
    }
  }
}>();

const activeTab = ref('single');

// Helper to redirect to the correct tab
function goToTab(tab: string) {
  router.visit(route('requests.index', { tab }));
}
</script>

<template>
  <Card class="h-full">
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/20">
          <FileText class="size-5 text-blue-500" />
        </div>
        Active Requests
      </CardTitle>
      <CardDescription>
        Summary of your current active requests.
      </CardDescription>
    </CardHeader>
    <CardContent class="space-y-6">
      <Tabs v-model="activeTab" class="w-full">
        <TabsList class="grid w-full grid-cols-2">
          <TabsTrigger value="single" class="flex items-center justify-center">
            <div class="inline-flex items-center">
              <User class="h-4 w-4 mr-2" />
              <span>My Requests</span>
            </div>
          </TabsTrigger>
          <TabsTrigger value="collaborative" class="flex items-center justify-center">
            <div class="inline-flex items-center">
              <Users class="h-4 w-4 mr-2" />
              <span>Shared With Me</span>
            </div>
          </TabsTrigger>
        </TabsList>
        <TabsContent value="single">
          <div class="grid grid-cols-2 gap-4 pt-4">
            <div class="space-y-4">
              <div
                class="flex items-center justify-between p-3 rounded-lg bg-muted cursor-pointer hover:bg-muted/70 transition"
                @click="goToTab('form')"
              >
                <div class="flex items-center gap-2">
                  <FileText class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm">Request Forms</span>
                </div>
                <span class="">{{ requestStats.single.requestForms }}</span>
              </div>
              <div
                class="flex items-center justify-between p-3 rounded-lg bg-muted cursor-pointer hover:bg-muted/70 transition"
                @click="goToTab('quotation')"
              >
                <div class="flex items-center gap-2">
                  <Calculator class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm">Quotations</span>
                </div>
                <span class="">{{ requestStats.single.quotations }}</span>
              </div>
            </div>
            <div class="space-y-4">
              <div
                class="flex items-center justify-between p-3 rounded-lg bg-muted cursor-pointer hover:bg-muted/70 transition"
                @click="goToTab('purchase-request')"
              >
                <div class="flex items-center gap-2">
                  <ShoppingCart class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm">Purchase Requests</span>
                </div>
                <span class="">{{ requestStats.single.purchaseRequests }}</span>
              </div>
              <div
                class="flex items-center justify-between p-3 rounded-lg bg-muted cursor-pointer hover:bg-muted/70 transition"
                @click="goToTab('purchase-order')"
              >
                <div class="flex items-center gap-2">
                  <Receipt class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm">Purchase Orders</span>
                </div>
                <span class="">{{ requestStats.single.purchaseOrders }}</span>
              </div>
            </div>
          </div>
        </TabsContent>
        <TabsContent value="collaborative">
          <div class="grid grid-cols-2 gap-4 pt-4">
            <div class="space-y-4">
              <div
                class="flex items-center justify-between p-3 rounded-lg bg-muted cursor-pointer hover:bg-muted/70 transition"
                @click="goToTab('form')"
              >
                <div class="flex items-center gap-2">
                  <FileText class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm">Request Forms</span>
                </div>
                <span class="">{{ requestStats.collaborative.requestForms }}</span>
              </div>
              <div
                class="flex items-center justify-between p-3 rounded-lg bg-muted cursor-pointer hover:bg-muted/70 transition"
                @click="goToTab('quotation')"
              >
                <div class="flex items-center gap-2">
                  <Calculator class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm">Quotations</span>
                </div>
                <span class="">{{ requestStats.collaborative.quotations }}</span>
              </div>
            </div>
            <div class="space-y-4">
              <div
                class="flex items-center justify-between p-3 rounded-lg bg-muted cursor-pointer hover:bg-muted/70 transition"
                @click="goToTab('purchase-request')"
              >
                <div class="flex items-center gap-2">
                  <ShoppingCart class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm">Purchase Requests</span>
                </div>
                <span class="">{{ requestStats.collaborative.purchaseRequests }}</span>
              </div>
              <div
                class="flex items-center justify-between p-3 rounded-lg bg-muted cursor-pointer hover:bg-muted/70 transition"
                @click="goToTab('purchase-order')"
              >
                <div class="flex items-center gap-2">
                  <Receipt class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm">Purchase Orders</span>
                </div>
                <span class="">{{ requestStats.collaborative.purchaseOrders }}</span>
              </div>
            </div>
          </div>
        </TabsContent>
      </Tabs>
    </CardContent>
  </Card>
</template>