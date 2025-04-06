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
import { ClipboardList, Users, Upload, FileText, CreditCard, X, Check, Eye, Edit2 } from 'lucide-vue-next';

const props = defineProps<{
  show: boolean;
}>();

const emit = defineEmits(['close']);
const currentStep = ref(1);

const steps = [{
  step: 1,
  title: 'Request Type',
  description: 'Choose request type',
  icon: ClipboardList,
}, {
  step: 2,
  title: 'Request Info',
  description: 'Fill request details',
  icon: FileText,
}, {
  step: 3,
  title: 'Collaborators',
  description: 'Add team members',
  icon: Users,
}, {
  step: 4,
  title: 'Documents',
  description: 'Upload supporting files or documents',
  icon: Upload,
}];

const requestType = ref<'petty-cash' | 'request-form' | null>(null);

const validationSchema = object({
  type: string().required('Request type is required'),
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

const { handleSubmit, errors, resetForm } = useVeeForm({
  validationSchema,
  initialValues: {
    type: '',
    name: '',
    category: '',
    description: '',
    collaborators: [],
    files: []
  }
});

const { value: nameValue, errorMessage: nameError } = useField<string>('name');
const { value: categoryValue, errorMessage: categoryError } = useField<string>('category');
const { value: descriptionValue, errorMessage: descriptionError } = useField<string>('description');

const { toast } = useToast();
const fileInputRef = ref<HTMLInputElement | null>(null);
const uploadedFiles = ref<File[]>([]);

const isStep2Valid = ref(false);
const isStep2Touched = ref(false);

// Replace the existing isStepValid computed property
const isStepValid = computed(() => {
  switch (currentStep.value) {
    case 1:
      return requestType.value !== null;
    case 2:
      return isStep2Valid.value;
    case 3:
      return true; // Optional collaborators
    case 4:
      return true; // Optional files
    default:
      return false;
  }
});

// Add watchers to validate step 2
watch([nameValue, categoryValue, descriptionValue], ([name, category, description]) => {
  isStep2Touched.value = true;
  isStep2Valid.value = Boolean(
    name && 
    name.length >= 3 && 
    category && 
    description && 
    description.length >= 10
  );
}, { immediate: true });

// Update the handleStepChange function
const handleStepChange = (step: number) => {
  if (step > currentStep.value) {
    // Moving forward
    if (currentStep.value === 2) {
      isStep2Touched.value = true;
    }
    if (isStepValid.value) {
      currentStep.value = step;
    }
  } else {
    // Moving backward
    currentStep.value = step;
  }
};

const handleFileUpload = (event: Event) => {
  const input = event.target as HTMLInputElement;
  if (input.files) {
    uploadedFiles.value = [...uploadedFiles.value, ...Array.from(input.files)];
  }
};

const removeFile = (index: number) => {
  uploadedFiles.value.splice(index, 1);
};

interface Collaborator {
  name: string;
  permission: 'view' | 'edit';
}

const selectedCollaborators = ref<Collaborator[]>([]);

// Update the addCollaborator function
const addCollaborator = (value: string) => {
  if (value && !selectedCollaborators.value.some(c => c.name === value)) {
    selectedCollaborators.value.push({
      name: value,
      permission: 'view' // Default permission
    });
  }
};

// Add a function to update permissions
const updatePermission = (index: number, permission: 'view' | 'edit') => {
  selectedCollaborators.value[index].permission = permission;
};

// Update the removeCollaborator function (no changes needed but included for context)
const removeCollaborator = (index: number) => {
  selectedCollaborators.value.splice(index, 1);
};

const onSubmit = handleSubmit((values) => {
  const formData = {
    ...values,
    type: requestType.value,
    collaborators: selectedCollaborators.value,
    files: uploadedFiles.value
  };

  useForm(formData).post(route('requests.store'), {
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: "Success",
        description: "Request created successfully",
        variant: "success",
      });
      emit('close');
      resetForm();
      requestType.value = null;
      selectedCollaborators.value = [];
      uploadedFiles.value = [];
      currentStep.value = 1;
    },
    onError: (errors) => {
      toast({
        title: "Error",
        description: errors.error || "Failed to create request",
        variant: "destructive",
      });
    },
  });
});
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

      <Stepper v-model="currentStep">
        <StepperItem
          v-for="item in steps"
          :key="item.step"
          class="basis-1/4"
          :step="item.step"
        >
          <StepperTrigger class="cursor-default pointer-events-none select-none">
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

      <div class="mt-8">
        <div v-if="currentStep === 1" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <Button
              type="button"
              variant="outline"
              :class="[
                'h-24 flex flex-col items-center justify-center',
                requestType === 'petty-cash' ? 'border-primary ring-2 ring-primary' : ''
              ]"
              @click="requestType = 'petty-cash'"
            >
              <CreditCard class="w-6 h-6 mb-2" />
              <span class="font-medium">Petty Cash Voucher</span>
            </Button>
            <Button
              type="button"
              variant="outline"
              :class="[
                'h-24 flex flex-col items-center justify-center',
                requestType === 'request-form' ? 'border-primary ring-2 ring-primary' : ''
              ]"
              @click="requestType = 'request-form'"
            >
              <ClipboardList class="w-6 h-6 mb-2" />
              <span class="font-medium">Request Form</span>
            </Button>
          </div>
          <div v-if="requestType" class="mt-4 p-4 rounded-lg bg-gray-200 dark:bg-muted">
            <div v-if="requestType === 'petty-cash'" class="space-y-2">
              <h3 class="font-medium">Petty Cash Voucher Details:</h3>
              <ul class="text-sm text-muted-foreground list-disc pl-4">
                <li>For small cash disbursements and reimbursements</li>
                <li>Maximum amount: ₱5,000</li>
                <li>Requires immediate liquidation within 48 hours</li>
                <li>Must include receipts and supporting documents</li>
              </ul>
            </div>
            <div v-else-if="requestType === 'request-form'" class="space-y-2">
              <h3 class="font-medium">Request Form Details:</h3>
              <ul class="text-sm text-muted-foreground list-disc pl-4">
                <li>For general requests (supplies, equipment, maintenance)</li>
                <li>No maximum amount limit</li>
                <li>Requires department head approval</li>
                <li>Processing time: 3-5 working days</li>
              </ul>
            </div>
          </div>
        </div>

        <form v-if="currentStep === 2" @submit.prevent="onSubmit" class="space-y-4">
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
        </form>

        <div v-if="currentStep === 3" class="space-y-4">
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
                <SelectItem value="john">John Doe</SelectItem>
                <SelectItem value="jane">Jane Smith</SelectItem>
                <SelectItem value="bob">Bob Johnson</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div v-if="selectedCollaborators.length > 0" class="space-y-2">
            <div v-for="(collaborator, index) in selectedCollaborators" 
              :key="index"
              class="flex items-center gap-3 p-3 bg-muted rounded-lg hover:bg-muted/80 transition-colors"
            >
              <!-- Left: Avatar and Info -->
              <div class="flex items-center gap-3 flex-1">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                  <Users class="h-5 w-5 text-primary" />
                </div>
                <div class="flex flex-col min-w-0">
                  <span class="text-sm font-medium truncate">{{ collaborator.name }}</span>
                  <span class="text-xs text-muted-foreground">Team Member</span>
                </div>
              </div>

              <!-- Right: Actions -->
              <div class="flex items-center gap-2">
                <!-- Permission Dropdown -->
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

                <!-- Remove Button -->
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

        <div v-if="currentStep === 4" class="space-y-4">
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
              @change="handleFileUpload"
            />
            <Upload class="size-8 mx-auto text-muted-foreground mb-4" />
            <div class="space-y-2">
              <Button variant="link" @click="fileInputRef?.click()">
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
        <Button
          type="button"
          @click="currentStep < steps.length ? currentStep++ : onSubmit()"
          :disabled="!isStepValid"
        >
          {{ currentStep === steps.length ? 'Submit' : 'Next' }}
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>