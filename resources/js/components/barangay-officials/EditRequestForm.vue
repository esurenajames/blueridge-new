<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Stepper, StepperContent, StepperDescription, StepperIndicator, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from '@/components/ui/stepper';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';
import { useToast } from '@/components/ui/toast';
import { ref, computed, watch } from 'vue';
import { useField, useForm as useVeeForm } from 'vee-validate';
import { object, string, array } from 'yup';
import { Check, FileText, Users, Upload } from 'lucide-vue-next';
import FileUpload from '@/components/FileUpload.vue';
import CollaboratorsList from '@/components/CollaboratorList.vue';

const props = defineProps<{
  show: boolean;
  request: {
    id: number;
    title: string;
    category: string;
    description: string;
    collaborators?: Array<{
      id: number;
      name: string;
      permission: string;
    }>;
    files?: Array<{
      name: string;
      size: number;
    }>;
  };
  activeUsers: Array<{ 
    id: number;
    name: string;
    role: string;
  }>;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'update:request', value: any): void;
}>();

const { toast } = useToast();
const currentStep = ref(1);

const steps = [
  {
    step: 1,
    title: 'Basic Info',
    description: 'Request details',
    icon: FileText,
  },
  {
    step: 2,
    title: 'Collaborators',
    description: 'Add members',
    icon: Users,
  },
  {
    step: 3,
    title: 'Attachments',
    description: 'Upload files',
    icon: Upload,
  },
];

const validationSchema = object({
  name: string()
    .required('Name is required')
    .min(3, 'Name must be at least 3 characters'),
  category: string().required('Category is required'),
  description: string()
    .required('Description is required')
    .min(10, 'Description must be at least 10 characters'),
  collaborators: array(),
  files: array(),
  existingFiles: array(),
  removedFiles: array()
});

const { handleSubmit, values } = useVeeForm({
  validationSchema,
  initialValues: {
    name: props.request.title,
    category: props.request.category,
    description: props.request.description,
    collaborators: props.request.collaborators || [],
    files: [],
    existingFiles: props.request.files || [],
    removedFiles: []
  }
});

const { value: nameValue, errorMessage: nameError } = useField<string>('name');
const { value: categoryValue, errorMessage: categoryError } = useField<string>('category');
const { value: descriptionValue, errorMessage: descriptionError } = useField<string>('description');
const { value: collaboratorsValue } = useField<any[]>('collaborators');
const { value: filesValue } = useField<File[]>('files');
const { value: existingFilesValue } = useField<any[]>('existingFiles');
const { value: removedFilesValue } = useField<string[]>('removedFiles');

const isStep1Valid = computed(() => {
  return Boolean(
    nameValue.value && 
    nameValue.value.length >= 3 && 
    categoryValue.value && 
    descriptionValue.value && 
    descriptionValue.value.length >= 10 &&
    !nameError.value &&
    !categoryError.value &&
    !descriptionError.value
  );
});

const isStepValid = computed(() => {
  switch (currentStep.value) {
    case 1:
      return isStep1Valid.value;
    case 2:
      return true;
    case 3:
      return true;
    default:
      return false;
  }
});

const onSubmit = handleSubmit((values) => {
  const formData = new FormData();
  formData.append('name', values.name);
  formData.append('category', values.category);
  formData.append('description', values.description);
  formData.append('collaborators', JSON.stringify(values.collaborators));
  formData.append('removedFiles', JSON.stringify(values.removedFiles));
  
  values.files.forEach((file: File) => {
    formData.append('files[]', file);
  });

  useForm(values).post(route('requests.update', { id: props.request.id }), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: (page) => {
      emit('update:request', page.props.request);
      toast({
        title: "Success",
        description: "Request updated successfully",
        variant: "success",
      });
      emit('close');
    },
    onError: (errors) => {
      if (errors.name || errors.category || errors.description) {
        currentStep.value = 1;
      } else if (errors.collaborators) {
        currentStep.value = 2;
      }
      toast({
        title: "Error",
        description: Object.values(errors)[0] as string || "Failed to update request",
        variant: "destructive",
      });
    },
  });
});

const nextStep = () => {
  if (currentStep.value < steps.length && isStepValid.value) {
    currentStep.value++;
  }
};

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};
</script>

<template>
  <Dialog :open="show" @update:open="emit('close')">
    <DialogContent class="sm:max-w-[700px]">
      <DialogHeader>
        <DialogTitle>Edit Request</DialogTitle>
        <DialogDescription>
          Update your request details below
        </DialogDescription>
      </DialogHeader>

      <div class="flex justify-center mb-8">
        <Stepper v-model="currentStep" class="max-w-[600px]">
          <StepperItem
            v-for="item in steps"
            :key="item.step"
            class="basis-1/3"
            :step="item.step"
          >
            <StepperTrigger class="select-none cursor-default pointer-events-none">
              <StepperIndicator>
                <component 
                  :is="currentStep > item.step && isStepValid ? Check : item.icon" 
                  class="w-4"
                />
              </StepperIndicator>
              <div class="flex flex-col">
                <StepperTitle>{{ item.title }}</StepperTitle>
                <StepperDescription>{{ item.description }}</StepperDescription>
              </div>
            </StepperTrigger>
            <StepperSeparator
              v-if="item.step !== steps[steps.length - 1].step"
              class="w-full h-px"
            />
          </StepperItem>
        </Stepper>
      </div>
      
      <form @submit.prevent="onSubmit" class="space-y-6">
        <!-- Step 1: Basic Info -->
        <div v-if="currentStep === 1" class="space-y-4">
          <div class="grid gap-4">
            <div class="space-y-2">
              <Label for="name">Request Title</Label>
              <Input 
                id="name"
                v-model="nameValue"
                placeholder="Enter request name"
                :class="{ 'border-red-500': nameError }"
              />
              <span v-if="nameError" class="text-sm text-red-500">
                {{ nameError }}
              </span>
            </div>
            
            <div class="space-y-2">
              <Label for="category">Category</Label>
              <Select v-model="categoryValue">
                <SelectTrigger :class="{ 'border-red-500': categoryError }">
                  <SelectValue placeholder="Select category" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="supplies">Office Supplies</SelectItem>
                  <SelectItem value="equipment">Equipment</SelectItem>
                  <SelectItem value="maintenance">Maintenance</SelectItem>
                  <SelectItem value="others">Others</SelectItem>
                </SelectContent>
              </Select>
              <span v-if="categoryError" class="text-sm text-red-500">
                {{ categoryError }}
              </span>
            </div>
            
            <div class="space-y-2">
              <Label for="description">Description</Label>
              <Input
                id="description"
                v-model="descriptionValue"
                placeholder="Describe your request"
                class="h-24"
                :class="{ 'border-red-500': descriptionError }"
              />
              <span v-if="descriptionError" class="text-sm text-red-500">
                {{ descriptionError }}
              </span>
            </div>
          </div>
        </div>

        <!-- Step 2: Collaborators -->
        <div v-if="currentStep === 2" class="space-y-4">
          <CollaboratorsList 
            v-model="collaboratorsValue"
            :active-users="activeUsers ?? []"
          />
        </div>

        <!-- Step 3: Files -->
        <div v-if="currentStep === 3" class="space-y-4">
          <FileUpload 
            v-model="filesValue"
            v-model:existing-files="existingFilesValue"
            v-model:removed-files="removedFilesValue"
          />
        </div>

        <div class="flex justify-between gap-2">
          <Button 
            type="button" 
            variant="outline"
            @click="prevStep"
            :disabled="currentStep === 1"
          >
            Previous
          </Button>
          
          <div class="flex gap-2">
            <Button 
              type="button" 
              variant="outline"
              @click="emit('close')"
            >
              Cancel
            </Button>
            
            <Button 
              v-if="currentStep < steps.length"
              type="button"
              @click="nextStep"
              :disabled="!isStepValid"
            >
              Next
            </Button>
            
            <Button 
              v-else
              type="submit"
              :disabled="!isStepValid"
            >
              Save Changes
            </Button>
          </div>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>