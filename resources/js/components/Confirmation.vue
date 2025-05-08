<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

const props = defineProps<{
  show: boolean;
  title: string;
  description?: string;
}>();

const emit = defineEmits<{
  confirm: [];
  cancel: [];
}>();
</script>

<template>
  <Dialog :open="show" @update:open="emit('cancel')">
    <DialogContent class="remarks-dialog-z">
      <DialogHeader>
        <DialogTitle>{{ title }}</DialogTitle>
        <DialogDescription v-if="description">
          {{ description }}
        </DialogDescription>
      </DialogHeader>
      <DialogFooter>
        <Button variant="outline" @click="emit('cancel')">Cancel</Button>
        <Button variant="default" @click="emit('confirm')">Confirm</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>