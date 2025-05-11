<script setup lang="ts">
import { ref, watch, defineProps, defineEmits } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useToast } from '@/components/ui/toast/use-toast';
import Confirmation from '@/components/Confirmation.vue';
import { useForm } from '@inertiajs/vue3';
import { useForm as useVeeForm, useField } from 'vee-validate';
import { object, string, number } from 'yup';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{ show: boolean }>();
const emit = defineEmits(['close']);

const validationSchema = object({
  name: string().required('Category name is required'),
  description: string().nullable(),
  position: number().required('Position is required').min(0, 'Position cannot be negative'),
  status: string().required('Status is required'),
});

const { handleSubmit: validateForm } = useVeeForm({
  validationSchema,
});

const { value: name, errorMessage: nameError } = useField('name');
const { value: description, errorMessage: descriptionError } = useField('description');
const { value: position, errorMessage: positionError } = useField('position', undefined, {
  initialValue: '0'
});
const { value: status, errorMessage: statusError } = useField('status', undefined, {
  initialValue: 'active'
});

const form = useForm({
  name: '',
  description: '',
  position: 0,
  status: 'active',
});

const isOpen = ref(props.show);
const showConfirmation = ref(false);
const { toast } = useToast();

watch(() => props.show, (val) => {
  isOpen.value = val;
  if (val) {
    name.value = '';
    description.value = '';
    position.value = '0';
    status.value = 'active';
    form.reset();
  }
});

watch(isOpen, (val) => {
  if (!val) emit('close');
});

const handleSubmit = validateForm((values) => {
  form.name = name.value;
  form.description = description.value;
  form.position = Number(position.value);
  form.status = status.value;
  showConfirmation.value = true;
});

const confirmCreate = () => {
  form.post(route('captain.categories.create'), {
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: "Success",
        description: "Category created successfully",
        variant: "success",
      });
      isOpen.value = false;
      showConfirmation.value = false;
      form.reset();
    },
    onError: () => {
      toast({
        title: "Error",
        description: "Failed to create category",
        variant: "destructive",
      });
      showConfirmation.value = false;
    },
  });
};
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Add Category</DialogTitle>
        <DialogDescription>
          Fill out the form below to add a new category.
        </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block mb-1 text-sm">Category Name</label>
          <Input 
            v-model="name" 
            placeholder="Enter category name" 
            :class="nameError ? 'border-red-500 focus-visible:ring-red-500' : ''" 
          />
          <span v-if="nameError" class="text-sm text-red-500">{{ nameError }}</span>
        </div>
        <div>
          <label class="block mb-1 text-sm">Description</label>
          <Textarea 
            v-model="description" 
            placeholder="Enter description" 
            :class="descriptionError ? 'border-red-500 focus-visible:ring-red-500' : ''" 
          />
          <span v-if="descriptionError" class="text-sm text-red-500">{{ descriptionError }}</span>
        </div>
         <div>
          <label class="block mb-1 text-sm">Position</label>
          <Input 
            v-model="position" 
            type="number" 
            min="0"
            placeholder="Enter position" 
            :class="positionError ? 'border-red-500 focus-visible:ring-red-500' : ''"
            @keypress="(e) => {
              const charCode = (e.which) ? e.which : e.keyCode;
              if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                e.preventDefault();
              }
            }"
            @paste="(e) => {
              e.preventDefault();
              const text = (e.clipboardData || window.clipboardData).getData('text');
              if (!isNaN(text) && !isNaN(parseFloat(text))) {
                position = text;
              }
            }"
          />
          <span v-if="positionError" class="text-sm text-red-500">{{ positionError }}</span>
        </div>
        <div>
          <label class="block mb-1 text-sm">Status</label>
          <Select v-model="status">
            <SelectTrigger :class="statusError ? 'border-red-500 focus:ring-red-500' : ''">
              <SelectValue placeholder="Select status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="active">Active</SelectItem>
              <SelectItem value="inactive">Inactive</SelectItem>
            </SelectContent>
          </Select>
          <span v-if="statusError" class="text-sm text-red-500">{{ statusError }}</span>
        </div>
        <div class="flex justify-end gap-4">
          <Button type="button" variant="outline" @click="isOpen = false">Cancel</Button>
          <Button type="submit" :disabled="form.processing">Create Category</Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>

  <Confirmation
    :show="showConfirmation"
    title="Create Category"
    description="Are you sure you want to create this category?"
    @confirm="confirmCreate"
    @cancel="showConfirmation = false"
  />
</template>