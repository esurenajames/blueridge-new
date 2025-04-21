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
import { Users, Upload, FileText, X, Check, Eye, Edit2 } from 'lucide-vue-next';

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
const fileInputRef = ref<HTMLInputElement | null>(null);
const uploadedFiles = ref<File[]>([]);

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

const handleFileUpload = (event: Event) => {
  const input = event.target as HTMLInputElement;
  if (input.files) {
    const newFiles = Array.from(input.files);
    uploadedFiles.value = [...uploadedFiles.value, ...newFiles];
  }
};

const removeFile = (index: number) => {
  uploadedFiles.value.splice(index, 1);
};

interface Collaborator {
  id: string;
  name: string;
  role: string;
  permission: 'view' | 'edit';
}

const selectedCollaborators = ref<Collaborator[]>([]);

const addCollaborator = (userId: string) => {
  const user = props.activeUsers.find(u => u.id.toString() === userId);
  if (user && !selectedCollaborators.value.some(c => c.id === user.id.toString())) {
    const newCollaborator = {
      id: user.id.toString(),
      name: user.name,
      role: user.role,
      permission: 'view' as const
    };
    selectedCollaborators.value.push(newCollaborator);
  }
};

const updatePermission = (index: number, permission: 'view' | 'edit') => {
  selectedCollaborators.value[index].permission = permission;
};

const removeCollaborator = (index: number) => {
  selectedCollaborators.value.splice(index, 1);
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
      // Use page.props.request.id instead of response.request.id
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
            <StepperTrigger 
              class="cursor-pointer select-none"
              @click="handleStepChange(item.step)"
            >
              <StepperIndicator>
                <component 
                  :is="currentStep > item.step && isStepValid ? Check : item.icon" 
                  class="w-4 h-4"
                />
              </StepperIndicator>
              <div class="flex flex-col">
                <StepperTitle>
                  {{ item.title }}
                </StepperTitle>
                <StepperDescription>
                  {{ item.description }}
                </StepperDescription>
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
          <div class="space-y-4">
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
                <Input
                  v-model="descriptionValue"
                  type="textarea"
                  placeholder="Describe your request"
                  class="h-24"
                  :class="{ 'border-red-500': descriptionError }"
                />
                <span v-if="descriptionError" class="text-sm text-red-500">{{ descriptionError }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 2: Collaborators -->
        <div v-if="currentStep === 2" class="space-y-4">
          <div class="space-y-2">
            <label class="text-sm font-medium">Add Team Members
              <span class="text-xs ml-1 bg-muted px-2 py-1 rounded-md text-muted-foreground">
                Optional
              </span>
            </label>
            <Select @update:modelValue="addCollaborator">
              <SelectTrigger class="h-9">
                <SelectValue placeholder="Select team member" />
              </SelectTrigger>
              <SelectContent class="min-w-[200px]">
                <SelectItem 
                  v-for="user in activeUsers" 
                  :key="user.id" 
                  :value="user.id.toString()"
                >
                  {{ user.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div v-if="selectedCollaborators.length > 0" class="space-y-2">
            <div v-for="(collaborator, index) in selectedCollaborators" 
              :key="index"
              class="flex items-center gap-3 p-3 bg-muted rounded-lg hover:bg-muted/80 transition-colors"
            >
              <div class="flex items-center gap-3 flex-1">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                  <Users class="h-5 w-5 text-primary" />
                </div>
                <div class="flex flex-col min-w-0">
                  <span class="text-sm font-medium truncate">{{ collaborator.name }}</span>
                  <span class="text-xs text-muted-foreground capitalize">{{ collaborator.role }}</span>
                </div>
              </div>

              <div class="flex items-center gap-2">
                <Select 
                  :model-value="collaborator.permission"
                  @update:modelValue="(value) => updatePermission(index, value as 'view' | 'edit')"
                >
                  <SelectTrigger class="h-8 w-[100px]">
                    <SelectValue>
                      <div class="flex items-center gap-1.5">
                        <Eye v-if="collaborator.permission === 'view'" class="size-3.5" />
                        <Edit2 v-else class="size-3.5" />
                        <span class="capitalize text-xs">{{ collaborator.permission }}</span>
                      </div>
                    </SelectValue>
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="view">
                      <div class="flex items-center gap-1.5">
                        <Eye class="size-3.5" />
                        <span>View</span>
                      </div>
                    </SelectItem>
                    <SelectItem value="edit">
                      <div class="flex items-center gap-1.5">
                        <Edit2 class="size-3.5" />
                        <span>Edit</span>
                      </div>
                    </SelectItem>
                  </SelectContent>
                </Select>

                <Button
                  variant="ghost"
                  size="icon"
                  class="h-8 w-8 rounded-full hover:bg-destructive/10 hover:text-destructive"
                  @click="removeCollaborator(index)"
                >
                  <X class="size-4" />
                </Button>
              </div>
            </div>
          </div>

          <div 
            v-if="!selectedCollaborators.length" 
            class="text-sm text-muted-foreground text-center p-6 bg-muted/50 rounded-lg"
          >
            <Users class="h-8 w-8 mx-auto mb-3 text-muted-foreground/50" />
            <p>No collaborators added yet</p>
          </div>
        </div>

        <!-- Step 3: Documents -->
        <div v-if="currentStep === 3" class="space-y-4">
          <label class="text-sm font-medium">Supporting Documents
            <span class="text-xs ml-1 bg-muted px-2 py-1 rounded-md text-muted-foreground">
              Optional
            </span>
          </label>
          <div
            class="border-2 border-dashed rounded-lg p-8 text-center hover:border-primary transition-colors"
            @dragover.prevent
            @drop.prevent="handleFileUpload"
          >
            <input
              type="file"
              ref="fileInputRef"
              class="hidden"
              multiple
              @change.prevent="handleFileUpload"
            />
                        <Upload class="size-8 mx-auto text-muted-foreground mb-4" />
            <div class="space-y-2">
              <Button 
                type="button" 
                variant="link" 
                @click.prevent="(e) => {
                  e.preventDefault();
                  e.stopPropagation();
                  fileInputRef?.click();
                }"
              >
                Upload files
              </Button>
              <p class="text-sm text-muted-foreground">
                or drag and drop your files here
              </p>
            </div>
          </div>

          <div v-if="uploadedFiles.length > 0" class="space-y-2">
            <div v-for="(file, index) in uploadedFiles" 
              :key="index"
              class="flex items-center gap-2 p-2 bg-muted rounded-md">
              <FileText class="size-4" />
              <span class="text-sm">{{ file.name }}</span>
              <span class="text-xs text-muted-foreground">
                {{ Math.round(file.size / 1024) }}KB
              </span>
              <Button
                variant="ghost"
                size="icon"
                class="ml-auto h-8 w-8"
                @click="removeFile(index)"
              >
                <X class="size-4" />
              </Button>
            </div>
          </div>
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