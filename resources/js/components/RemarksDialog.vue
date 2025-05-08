<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { ref } from 'vue';

const props = defineProps<{
  show: boolean;
  title: string;
  description?: string;
}>();

const remarks = ref('');

const emit = defineEmits<{
  confirm: [remarks: string];
  cancel: [];
}>();

const handleConfirm = () => {
  emit('confirm', remarks.value);
  remarks.value = ''; 
};

const handleCancel = () => {
  remarks.value = ''; 
  emit('cancel');
};
</script>

<template>
  <Dialog :open="show" @update:open="handleCancel">
    <DialogContent class="remarks-dialog-z">
      <DialogHeader>
        <DialogTitle>{{ title }}</DialogTitle>
        <DialogDescription v-if="description">
          {{ description }}
        </DialogDescription>
      </DialogHeader>
      <div class="py-4">
        <Textarea
          v-model="remarks"
          placeholder="Enter your remarks..."
          class="min-h-[100px]"
        />
      </div>
      <DialogFooter>
        <Button variant="outline" @click="handleCancel">Cancel</Button>
        <Button variant="default" @click="handleConfirm">Continue</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>