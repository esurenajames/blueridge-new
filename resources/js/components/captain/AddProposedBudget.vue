<script setup lang="ts">
import { ref, watch } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/components/ui/toast/use-toast';
import Confirmation from '@/components/Confirmation.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
  show: boolean
  subcategoryId?: number
  budgetId?: number
}>();

const emit = defineEmits(['close']);

const { toast } = useToast();
const isOpen = ref(false);
const showConfirmation = ref(false);

const form = useForm({
  amount: '',
  remarks: '',
  transaction_date: new Date().toISOString().split('T')[0]
});

watch(() => props.show, (newVal) => {
  isOpen.value = newVal;
  if (!newVal) {
    resetForm();
  }
});

watch(isOpen, (newVal) => {
  if (!newVal) {
    emit('close');
  }
});

const resetForm = () => {
  form.reset();
  showConfirmation.value = false;
};

const handleSubmit = () => {
  showConfirmation.value = true;
};

const confirmCreate = () => {
  if (!props.budgetId) {
    toast({
      title: "Error",
      description: "No budget selected",
      variant: "destructive"
    });
    return;
  }

  form.post(route('captain.funds.add-proposed-budget', { budget: props.budgetId }), {
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: "Success",
        description: "Proposed budget added successfully",
      });
      isOpen.value = false;
      emit('close');
    },
    onError: (errors) => {
      toast({
        title: "Error",
        description: errors.proposed_budget || "Failed to add proposed budget. Please try again.",
        variant: "destructive"
      });
    }
  });
};
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Add Proposed Budget</DialogTitle>
        <DialogDescription>
          Fill out the form below to add a proposed budget to this item.
        </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block mb-1 text-sm">Amount</label>
          <Input 
            v-model="form.amount" 
            type="number" 
            min="0.01" 
            step="0.01"
            placeholder="Enter amount" 
            :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.amount }" 
          />
          <span v-if="form.errors.amount" class="text-sm text-red-500">{{ form.errors.amount }}</span>
        </div>
        <div>
          <label class="block mb-1 text-sm">Transaction Date</label>
          <Input 
            v-model="form.transaction_date" 
            type="date" 
            :max="new Date().toISOString().split('T')[0]"
            :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.transaction_date }" 
          />
          <span v-if="form.errors.transaction_date" class="text-sm text-red-500">{{ form.errors.transaction_date }}</span>
        </div>
        <div>
          <label class="block mb-1 text-sm">Remarks</label>
          <Textarea 
            v-model="form.remarks" 
            placeholder="Enter remarks (optional)"
            :class="{ 'border-red-500 focus-visible:ring-red-500': form.errors.remarks }" 
          />
          <span v-if="form.errors.remarks" class="text-sm text-red-500">{{ form.errors.remarks }}</span>
        </div>
        <div class="flex justify-end gap-4">
          <Button type="button" variant="outline" @click="isOpen = false">Cancel</Button>
          <Button type="submit" :disabled="form.processing">Add Proposed Budget</Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>

  <Confirmation
    :show="showConfirmation"
    title="Add Proposed Budget"
    description="Are you sure you want to add this proposed budget?"
    @confirm="confirmCreate"
    @cancel="showConfirmation = false"
  />
</template>