<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Stepper, StepperDescription, StepperIndicator, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from '@/components/ui/stepper';
import { useForm } from '@inertiajs/vue3';
import { useField, useForm as useVeeForm } from 'vee-validate';
import { object, string, array } from 'yup';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useToast } from '@/components/ui/toast/use-toast';
import { Users, Upload, FileText, Check } from 'lucide-vue-next';
import FileUpload from '@/components/FileUpload.vue';
import CollaboratorList from '@/components/CollaboratorList.vue';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{
  show: boolean;
  activeUsers: Array<{
    id: number;
    name: string;
    role: string;
  }>;
}>();

const emit = defineEmits(['close']);
const currentStep = ref(1);

const steps = [{
  step: 1,
  title: 'Request Info',
  description: 'Fill request details',
  icon: FileText,
}, {
  step: 2,
  title: 'Collaborators',
  description: 'Add team members',
  icon: Users,
}, {
  step: 3,
  title: 'Documents',
  description: 'Upload supporting files',
  icon: Upload,
}];

const validationSchema = object({
  name: string()
    .required('Name is required')
    .min(3, 'Name must be at least 3 characters'),
  category: string().required('Category is required'),
  description: string()
    .required('Description is required')
    .min(10, 'Description must be at least 10 characters'),
  collaborators: array().of(string()),
  files: array()
});

const { handleSubmit } = useVeeForm({ validationSchema });
const { value: nameValue, errorMessage: nameError } = useField<string>('name');
const { value: categoryValue, errorMessage: categoryError } = useField<string>('category');
const { value: descriptionValue, errorMessage: descriptionError } = useField<string>('description');

const { toast } = useToast();
const uploadedFiles = ref<File[]>([]);
const selectedCollaborators = ref<Array<{ id: string; name: string; role: string; permission: 'view' | 'edit' }>>([]);

const isStep1Valid = ref(false);

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

watch([nameValue, categoryValue, descriptionValue], ([name, category, description]) => {
  isStep1Valid.value = Boolean(
    name && 
    name.length >= 3 && 
    category && 
    description && 
    description.length >= 10
  );
}, { immediate: true });

const handleStepChange = (step: number) => {
  if (step > currentStep.value) {
    if (isStepValid.value) {
      currentStep.value = step;
    }
  } else {
    currentStep.value = step;
  }
};

const form = useForm({
  name: '',
  category: '',
  description: '',
  collaborators: [] as { id: number; permission: string }[],
  files: [] as File[]
});

const resetForm = () => {
  nameValue.value = '';
  categoryValue.value = '';
  descriptionValue.value = '';
  selectedCollaborators.value = [];
  uploadedFiles.value = [];
  form.reset();
};

const onSubmit = () => {
  if (!isStep1Valid.value) {
    toast({
      title: "Error",
      description: "Please fill in all required fields",
      variant: "destructive",
    });
    return;
  }

  const formattedCollaborators = selectedCollaborators.value.map(collaborator => ({
    id: parseInt(collaborator.id),
    permission: collaborator.permission
  }));

  form.name = nameValue.value;
  form.category = categoryValue.value;
  form.description = descriptionValue.value;
  form.collaborators = formattedCollaborators;
  form.files = uploadedFiles.value;

  form.post(route('dashboard.store-request'), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: (page) => {
      toast({
        title: "Success",
        description: "Request created successfully",
        variant: "success",
      });
      window.location.href = route('requests.view', { id: page.props.request.id });
    },
    onError: (errors) => {
      if (errors.name || errors.category || errors.description) {
        currentStep.value = 1;
      } else if (errors.collaborators) {
        currentStep.value = 2;
      }
      toast({
        title: "Error",
        description: Object.values(errors)[0] as string || "Failed to create request",
        variant: "destructive",
      });
    },
  });
};
</script>

<template>
  <Dialog :open="show" @update:open="emit('close')">
    <DialogContent class="sm:max-w-[700px]">
      <DialogHeader>
        <DialogTitle>Create New Request</DialogTitle>
        <DialogDescription>
          Follow the steps below to create your request
        </DialogDescription>
      </DialogHeader>
      
      <form @submit.prevent="onSubmit" class="space-y-4">
        <div class="flex justify-center">
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
                    class="w-4 h-4"
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

        <div class="mt-8">
          <!-- Step 1: Request Info -->
          <div v-if="currentStep === 1" class="space-y-4">
            <div class="grid gap-4">
              <div class="space-y-2">
                <label class="text-sm font-medium">Name of Request</label>
                <Input 
                  v-model="nameValue"
                  placeholder="Enter request name"
                  :class="{ 'border-red-500': nameError }"
                />
                <span v-if="nameError" class="text-sm text-red-500">{{ nameError }}</span>
              </div>
              
              <div class="space-y-2">
                <label class="text-sm font-medium">Category</label>
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
                <span v-if="categoryError" class="text-sm text-red-500">{{ categoryError }}</span>
              </div>
              
              <div class="space-y-2">
                <label class="text-sm font-medium">Description</label>
                <Textarea
                  v-model="descriptionValue"
                  placeholder="Describe your request"
                  class="h-24"
                  :class="{ 'border-red-500': descriptionError }"
                />
                <span v-if="descriptionError" class="text-sm text-red-500">{{ descriptionError }}</span>
              </div>
            </div>
          </div>

          <!-- Step 2: Collaborators -->
          <div v-if="currentStep === 2">
            <CollaboratorList
              v-model="selectedCollaborators"
              :active-users="activeUsers"
              :errors="form.errors.collaborators"
            />
          </div>

          <!-- Step 3: Documents -->
          <div v-if="currentStep === 3">
            <FileUpload
              v-model="uploadedFiles"
              :errors="form.errors.files"
            />
          </div>
        </div>

        <div class="flex justify-between mt-6">
          <Button
            type="button"
            variant="outline"
            @click="currentStep > 1 ? currentStep-- : null"
            :disabled="currentStep === 1"
          >
            Previous
          </Button>
          <div class="flex gap-2">
            <Button
              v-if="currentStep < steps.length"
              type="button"
              @click="handleStepChange(currentStep + 1)"
              :disabled="currentStep === 1 && !isStep1Valid"
            >
              Next
            </Button>
            <Button
              v-if="currentStep === steps.length"
              type="submit"
            >
              Submit Request
            </Button>
          </div>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>