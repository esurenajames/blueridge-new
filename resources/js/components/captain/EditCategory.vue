<script setup lang="ts">
import { ref, watch, defineProps, defineEmits } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';
import { useForm as useVeeForm, useField } from 'vee-validate';
import { object, string, number } from 'yup';
import Confirmation from '@/components/Confirmation.vue';
import { useToast } from '@/components/ui/toast/use-toast';

const GROUP_NAMES = [
  'Beginning Cash Balance',
  'Receipts',
  'Expenditures',
];

const props = defineProps<{
  show: boolean;
  category: {
    id: number;
    name: string;
    description: string;
    group_name: 'Beginning Cash Balance' | 'Receipts' | 'Expenditures' | 'MOOE';
    position: number;
    status: 'active' | 'inactive';
  };
}>();

const emit = defineEmits(['close']);

const validationSchema = object({
  name: string().required('Category name is required'),
  description: string().nullable(),
  group_name: string().required('Group name is required'),
  position: number().required('Position is required').min(1, 'Position must be at least 1'),
  status: string().required('Status is required'),
});

const { handleSubmit: validateForm } = useVeeForm({
  validationSchema,
});

const { value: name, errorMessage: nameError } = useField('name', undefined, { initialValue: props.category.name });
const { value: description, errorMessage: descriptionError } = useField('description', undefined, { initialValue: props.category.description });
const { value: group_name, errorMessage: groupNameError } = useField('group_name', undefined, { initialValue: props.category.group_name });
const { value: status, errorMessage: statusError } = useField('status', undefined, { initialValue: props.category.status });
const { value: position, errorMessage: positionError } = useField('position', undefined, { initialValue: props.category.position });

const form = useForm({
  name: props.category.name,
  description: props.category.description,
  group_name: props.category.group_name,
  position: props.category.position,
  status: props.category.status,
});

const isOpen = ref(props.show);
const showConfirmation = ref(false);
const { toast } = useToast();

watch(() => props.show, (val) => {
  isOpen.value = val;
  if (val) {
    name.value = props.category.name;
    description.value = props.category.description;
    group_name.value = props.category.group_name;
    position.value = props.category.position;
    status.value = props.category.status;
    form.reset();
    form.name = props.category.name;
    form.description = props.category.description;
    form.group_name = props.category.group_name;
    form.position = props.category.position;
    form.status = props.category.status;
  }
});

watch(isOpen, (val) => {
  if (!val) emit('close');
});

const handleSubmit = validateForm((values) => {
  form.name = name.value;
  form.description = description.value;
  form.group_name = group_name.value;
  form.position = position.value;
  form.status = status.value;
  showConfirmation.value = true;
});

const confirmEdit = () => {
  form.put(route('captain.categories.update', props.category.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: "Success",
        description: "Category updated successfully",
        variant: "success",
      });
      isOpen.value = false;
      showConfirmation.value = false;
      form.reset();
    },
    onError: (errors) => {
      if (errors.position) {
        positionError.value = errors.position;
      }
      toast({
        title: "Error",
        description: errors.categories || errors.position || "Failed to update category",
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
        <DialogTitle>Edit Category</DialogTitle>
        <DialogDescription>
          Fill out the form below to edit category.
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
          <label class="block mb-1 text-sm">Category Group Name</label>
          <Select v-model="group_name">
            <SelectTrigger :class="groupNameError ? 'border-red-500 focus:ring-red-500' : ''">
              <SelectValue placeholder="Select group name" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="g in GROUP_NAMES" :key="g" :value="g">{{ g }}</SelectItem>
            </SelectContent>
          </Select>
          <span v-if="groupNameError" class="text-sm text-red-500">{{ groupNameError }}</span>
        </div>
        <div>
          <label class="block mb-1 text-sm">Position</label>
           <Input 
              v-model="position" 
              type="number"
              min="1"
              @keypress="(e) => {
                if (!/[0-9]/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete') {
                  e.preventDefault()
                }
              }"
              @paste.prevent="(e) => {
                const pastedValue = e.clipboardData?.getData('text')
                if (pastedValue && /^\d+$/.test(pastedValue)) {
                  position = parseInt(pastedValue)
                }
              }"
              placeholder="Enter position" 
              :class="positionError ? 'border-red-500 focus-visible:ring-red-500' : ''" 
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
          <Button type="submit" :disabled="form.processing">Save Changes</Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>

  <Confirmation
    :show="showConfirmation"
    title="Edit Category"
    description="Are you sure you want to save these changes?"
    @confirm="confirmEdit"
    @cancel="showConfirmation.value = false"
  />
</template>