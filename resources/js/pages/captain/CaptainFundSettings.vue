<script setup lang="ts">
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Heading from '@/components/Heading.vue'
import { Button } from '@/components/ui/button'
import { Switch } from '@/components/ui/switch'
import { Label } from '@/components/ui/label'
import { Separator } from '@/components/ui/separator'
import type { BreadcrumbItem } from '@/types'
import { useToast } from '@/components/ui/toast/use-toast';

interface Setting {
  id: number
  name: string
  is_locked: boolean
  created_at: string
  updated_at: string
}
const { toast } = useToast();

const props = defineProps<{
  settings: {
    data: Setting[]
  }
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { 
    title: 'Fund Settings',
    href: 'captain/fund-settings'
  },
]

const settings = ref({
  budgetLocked: props.settings.data.find(s => s.name === 'budget')?.is_locked ?? false,
  categoriesLocked: props.settings.data.find(s => s.name === 'categories')?.is_locked ?? false,
  subCategoriesLocked: props.settings.data.find(s => s.name === 'sub_categories')?.is_locked ?? false
})

const originalSettings = ref([
  ...props.settings.data.map(s => ({
    id: s.id,
    name: s.name,
    is_locked: s.is_locked
  }))
])

const saveSettings = () => {
  const changedSettings = []

  // Compare each setting with its original value
  originalSettings.value.forEach(orig => {
    if (orig.name === 'budget' && orig.is_locked !== settings.value.budgetLocked) {
      changedSettings.push({ id: orig.id, is_locked: settings.value.budgetLocked })
    }
    if (orig.name === 'categories' && orig.is_locked !== settings.value.categoriesLocked) {
      changedSettings.push({ id: orig.id, is_locked: settings.value.categoriesLocked })
    }
    if (orig.name === 'sub_categories' && orig.is_locked !== settings.value.subCategoriesLocked) {
      changedSettings.push({ id: orig.id, is_locked: settings.value.subCategoriesLocked })
    }
  })

  if (changedSettings.length === 0) {
    toast({
      title: "No changes",
      description: "No settings were changed.",
      variant: "info",
    })
    return
  }

  const payload = {
    settings: changedSettings
  }

  router.post(route('captain.fund-settings.save'), payload, {
    onSuccess: (page) => {
      const newSettings = page.props.settings.data
      settings.value = {
        budgetLocked: newSettings.find(s => s.name === 'budget')?.is_locked ?? false,
        categoriesLocked: newSettings.find(s => s.name === 'categories')?.is_locked ?? false,
        subCategoriesLocked: newSettings.find(s => s.name === 'sub_categories')?.is_locked ?? false
      }
      // Update originalSettings to match new values
      originalSettings.value = newSettings.map(s => ({
        id: s.id,
        name: s.name,
        is_locked: s.is_locked
      }))
      toast({
        title: "Success",
        description: "Settings updated successfully",
        variant: "success",
      });
    },
    onError: () => {
      toast({
        title: "Error",
        description: "Failed to update settings",
        variant: "destructive",
      });
    }
  })
}

const activeTab = ref('budget')

const setActiveTab = (tab: string) => {
  activeTab.value = tab
}
</script>

<template>
  <Head title="Fund Settings" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="px-4 py-6">
      <Heading 
        title="Fund Settings" 
        description="Configure funds management and categories restrictions" 
      />

      <div class="flex flex-col space-y-8 md:space-y-0 lg:flex-row lg:space-x-12 lg:space-y-0">
        <aside class="lg:w-48">
          <nav class="flex flex-col space-y-1">
            <Button
              variant="ghost"
              class="w-full justify-start"
              :class="{ 'bg-muted': activeTab === 'budget' }"
              @click="setActiveTab('budget')"
            >
              Budget
            </Button>
            <Button
              variant="ghost"
              class="w-full justify-start"
              :class="{ 'bg-muted': activeTab === 'categories' }"
              @click="setActiveTab('categories')"
            >
              Categories
            </Button>
          </nav>
        </aside>

        <Separator class="my-6 md:hidden" />

        <div class="flex-1 lg:max-w-2xl">
          <section v-if="activeTab === 'budget'" class="space-y-6">
            <!-- Budget Lock Settings -->
            <div class="flex items-center justify-between space-x-4">
              <div class="space-y-0.5">
                <Label>Proposed Budget Lock</Label>
                <p class="text-sm text-muted-foreground">
                  When enabled, users cannot modify the proposed budget
                </p>
              </div>
              <Switch v-model="settings.budgetLocked" />
            </div>
          </section>

          <section v-if="activeTab === 'categories'" class="space-y-6">
            <!-- Categories Lock Settings -->
            <div class="flex items-center justify-between space-x-4">
              <div class="space-y-0.5">
                <Label>Categories Lock</Label>
                <p class="text-sm text-muted-foreground">
                  When enabled, users cannot delete or modify categories
                </p>
              </div>
              <Switch v-model="settings.categoriesLocked" />
            </div>
            <div class="flex items-center justify-between space-x-4">
              <div class="space-y-0.5">
                <Label>Subcategories Lock</Label>
                <p class="text-sm text-muted-foreground">
                  When enabled, users cannot delete or modify subcategories
                </p>
              </div>
              <Switch v-model="settings.subCategoriesLocked" />
            </div>
          </section>

          <div class="flex justify-end pt-6">
            <Button @click="saveSettings">Save changes</Button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>