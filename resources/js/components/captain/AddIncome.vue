<script setup lang="ts">
import { ref, watch } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/components/ui/toast/use-toast';
import Confirmation from '@/components/Confirmation.vue';
import { useForm } from '@inertiajs/vue3';
import { X, ZoomIn } from 'lucide-vue-next';
import { Label } from '@/components/ui/label';
import FileUpload from '@/components/FileUpload.vue';

const props = defineProps<{
  show: boolean
  subcategoryId?: number
  budgetId?: number
}>();

const emit = defineEmits(['close']);

const { toast } = useToast();
const isOpen = ref(false);
const showConfirmation = ref(false);
const showPreviewDialog = ref(false);
const selectedPreview = ref<{ url: string; type: string } | null>(null);
const uploadedFiles = ref<File[]>([]);
const previewUrls = ref<{ id: number; url: string; type: string }[]>([]);

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

watch(uploadedFiles, (newFiles) => {
  // Clear old preview URLs
  previewUrls.value.forEach(preview => URL.revokeObjectURL(preview.url));
  previewUrls.value = [];
  
  // Create new preview URLs
  newFiles.forEach((file, index) => {
    const url = URL.createObjectURL(file);
    previewUrls.value.push({
      id: index,
      url,
      type: file.type
    });
  });
}, { deep: true });

const openPreview = (preview: { url: string; type: string }) => {
  selectedPreview.value = preview;
  showPreviewDialog.value = true;
};

const closePreview = () => {
  selectedPreview.value = null;
  showPreviewDialog.value = false;
};

const removeFile = (index: number) => {
  // Revoke the URL for the removed file
  if (previewUrls.value[index]) {
    URL.revokeObjectURL(previewUrls.value[index].url);
  }
  uploadedFiles.value.splice(index, 1);
  previewUrls.value.splice(index, 1);
};

const resetForm = () => {
  form.reset();
  showConfirmation.value = false;
  // Clean up all preview URLs
  previewUrls.value.forEach(preview => URL.revokeObjectURL(preview.url));
  previewUrls.value = [];
  uploadedFiles.value = [];
};

const form = useForm({
  amount: '',
  remarks: '',
  transaction_date: new Date().toISOString().split('T')[0],
  receipts: [] as File[]
});

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

  form.receipts = uploadedFiles.value;

  form.post(route('captain.funds.add-income', { budget: props.budgetId }), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      toast({
        title: "Success",
        description: "Income added successfully",
      });
      isOpen.value = false;
      emit('close');
    },
    onError: (errors) => {
      toast({
        title: "Error",
        description: "Failed to add income. Please try again.",
        variant: "destructive"
      });
    }
  });
};
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogContent class="sm:min-w-[600px]">
      <DialogHeader>
        <DialogTitle>Add income</DialogTitle>
        <DialogDescription>
          Fill out the form below to add income to this budget.
        </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block mb-1 text-sm">Amount</label>
          <Input 
            v-model="form.amount" 
            type="number" 
            min="0" 
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
          <div class="py-2">  
            <Label>Receipts
              <span class="text-xs ml-1 bg-muted px-2 py-1 rounded-md text-muted-foreground">
                Required
              </span>
            </Label>
          </div>
          <FileUpload
            v-model="uploadedFiles"
            :errors="form.errors.receipts"
            accept="image/*,.pdf"
            :max-size="5242880"
          />
        </div>

        <div class="flex justify-end gap-4">
          <Button type="button" variant="outline" @click="isOpen = false">Cancel</Button>
          <Button type="submit" :disabled="form.processing">Add income</Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>

  <Confirmation
    :show="showConfirmation"
    title="Add income"
    description="Are you sure you want to add this income?"
    @confirm="confirmCreate"
    @cancel="showConfirmation = false"
  />
</template>