<script setup lang="ts">
import { ref, watch, defineProps, defineEmits } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';
import { useForm as useVeeForm, useField } from 'vee-validate';
import { object, string } from 'yup';
import Confirmation from '@/components/Confirmation.vue';
import { useToast } from '@/components/ui/toast/use-toast';

interface Category {
  id: number;
  name: string;
}

const props = defineProps<{
  show: boolean;
  subcategory: {
    id: number;
    category_id: number;
    name: string;
    description: string;
    status: 'active' | 'inactive';
  };
  categories: Category[];
}>();

const emit = defineEmits(['close']);

const validationSchema = object({
  category_id: string().required('Parent category is required'),
  name: string().required('Subcategory name is required'),
  description: string().nullable(),
  status: string().required('Status is required'),
});

const { handleSubmit: validateForm } = useVeeForm({
  validationSchema,
});

const { value: category_id, errorMessage: categoryError } = useField('category_id', undefined, { 
  initialValue: props.subcategory.category_id.toString() 
});
const { value: name, errorMessage: nameError } = useField('name', undefined, { 
  initialValue: props.subcategory.name 
});
const { value: description, errorMessage: descriptionError } = useField('description', undefined, { 
  initialValue: props.subcategory.description 
});
const { value: status, errorMessage: statusError } = useField('status', undefined, { 
  initialValue: props.subcategory.status 
});

const form = useForm({
  category_id: props.subcategory.category_id,
  name: props.subcategory.name,
  description: props.subcategory.description,
  status: props.subcategory.status,
});

const isOpen = ref(props.show);
const showConfirmation = ref(false);
const { toast } = useToast();

watch(() => props.show, (val) => {
  isOpen.value = val;
  if (val) {
    category_id.value = props.subcategory.category_id.toString();
    name.value = props.subcategory.name;
    description.value = props.subcategory.description;
    status.value = props.subcategory.status;
    form.reset();
    form.category_id = props.subcategory.category_id;
    form.name = props.subcategory.name;
    form.description = props.subcategory.description;
    form.status = props.subcategory.status;
  }
});

watch(isOpen, (val) => {
  if (!val) emit('close');
});

const handleSubmit = validateForm((values) => {
  form.category_id = Number(category_id.value);
  form.name = name.value;
  form.description = description.value;
  form.status = status.value;
  showConfirmation.value = true;
});

const confirmEdit = () => {
  form.put(route('captain.subcategories.update', props.subcategory.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: "Success",
        description: "Subcategory updated successfully",
        variant: "success",
      });
      isOpen.value = false;
      showConfirmation.value = false;
      form.reset();
    },
    onError: () => {
      toast({
        title: "Error",
        description: "Failed to update subcategory",
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
        <DialogTitle>Edit Subcategory</DialogTitle>
        <DialogDescription>
          Fill out the form below to edit subcategory.
        </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block mb-1 text-sm">Parent Category</label>
          <Select v-model="category_id">
            <SelectTrigger :class="categoryError ? 'border-red-500 focus:ring-red-500' : ''">
              <SelectValue placeholder="Select parent category" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem 
                v-for="category in categories" 
                :key="category.id" 
                :value="category.id.toString()"
              >
                {{ category.name }}
              </SelectItem>
            </SelectContent>
          </Select>
          <span v-if="categoryError" class="text-sm text-red-500">{{ categoryError }}</span>
        </div>
        <div>
          <label class="block mb-1 text-sm">Subcategory Name</label>
          <Input 
            v-model="name" 
            placeholder="Enter subcategory name" 
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
    title="Edit Subcategory"
    description="Are you sure you want to save these changes?"
    @confirm="confirmEdit"
    @cancel="showConfirmation = false"
  />
</template>