<script setup lang="ts">
import { ref } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useToast } from '@/components/ui/toast/use-toast';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';
import { useField, useForm as useVeeForm } from 'vee-validate';
import * as yup from 'yup';

interface Props {
  user: {
    id: number;
    name: string;
    email: string;
    role: string;
    status: 'active' | 'inactive';
  };
  show: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits(['close']);

const roles = [
  { value: 'captain', label: 'Captain' },
  { value: 'admin', label: 'Admin' },
  { value: 'official', label: 'Official' },
  { value: 'secretary', label: 'Secretary' },
  { value: 'treasurer', label: 'Treasurer' },
];

const statuses = [
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
];

const validationSchema = yup.object({
  name: yup.string().required('Name is required'),
  email: yup.string().required('Email is required').email('Must be a valid email'),
  role: yup.string().required('Role is required'),
  status: yup.string().required('Status is required'),
});

const { handleSubmit: validateForm } = useVeeForm({
  validationSchema,
  initialValues: {
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    status: props.user.status,
  },
});

const { value: name, errorMessage: nameError } = useField('name');
const { value: email, errorMessage: emailError } = useField('email');
const { value: role, errorMessage: roleError } = useField('role');
const { value: status, errorMessage: statusError } = useField('status');

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  role: props.user.role,
  status: props.user.status,
});

const { toast } = useToast();

const handleSubmit = validateForm((values) => {
  form.name = name.value;
  form.email = email.value;
  form.role = role.value;

  form.put(route('admin.users.update', props.user.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: "Success",
        description: "User updated successfully",
        variant: "success",
      });
      emit('close');
    },
    onError: (errors) => {
      if (errors.error) {
        toast({
          title: "Error",
          description: errors.error,
          variant: "destructive",
        });
      } else {
        toast({
          title: "Error",
          description: "Failed to update user. Please try again.",
          variant: "destructive",
        });
      }
    },
  });
});
</script>

<template>
  <Dialog :open="show" @update:open="emit('close')">
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Edit User</DialogTitle>
        <DialogDescription>
          Modify User details and information.
        </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="space-y-2">
          <label for="name" class="text-sm font-medium">Name</label>
          <Input 
            id="name"
            v-model="name"
            :class="nameError ? 'border-red-500 focus-visible:ring-red-500' : ''"
          />
          <span v-if="nameError" class="text-sm text-red-500">{{ nameError }}</span>
        </div>
        <div class="space-y-2">
          <label for="email" class="text-sm font-medium">Email</label>
          <Input 
            id="email"
            type="email"
            v-model="email"
            :class="emailError ? 'border-red-500 focus-visible:ring-red-500' : ''"
          />
          <span v-if="emailError" class="text-sm text-red-500">{{ emailError }}</span>
        </div>
        <div class="space-y-2">
          <label for="role" class="text-sm font-medium">Role</label>
          <Select v-model="role">
            <SelectTrigger :class="roleError ? 'border-red-500 focus:ring-red-500' : ''">
              <SelectValue placeholder="Select role" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem 
                v-for="role in roles" 
                :key="role.value" 
                :value="role.value"
              >
                {{ role.label }}
              </SelectItem>
            </SelectContent>
          </Select>
          <span v-if="roleError" class="text-sm text-red-500">{{ roleError }}</span>
        </div>
        <div class="space-y-2">
          <label for="status" class="text-sm font-medium">Status</label>
          <Select v-model="status">
            <SelectTrigger :class="statusError ? 'border-red-500 focus:ring-red-500' : ''">
              <SelectValue placeholder="Select status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem 
                v-for="status in statuses" 
                :key="status.value" 
                :value="status.value"
              >
                {{ status.label }}
              </SelectItem>
            </SelectContent>
          </Select>
          <span v-if="statusError" class="text-sm text-red-500">{{ statusError }}</span>
        </div>
        <div class="flex justify-end gap-3">
          <Button 
            type="button" 
            variant="outline" 
            @click="emit('close')"
          >
            Cancel
          </Button>
          <Button 
            type="submit" 
            :disabled="form.processing"
          >
            {{ form.processing ? 'Saving...' : 'Save Changes' }}
          </Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>