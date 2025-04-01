<script setup lang="ts">
import { ref } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useToast } from '@/components/ui/toast/use-toast';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';

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

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  role: props.user.role,
  status: props.user.status,
});

const { toast } = useToast();

const handleSubmit = () => {
  form.put(route('admin.users.update', props.user.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: "Success",
        description: "User updated successfully",
        variant: "success",
      });
      emit('close');
      form.reset();
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
            description: "Failed to delete user. Please try again.",
            variant: "destructive",
          });
        }
      },
  });
};
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
            v-model="form.name"
            :error="form.errors.name"
          />
        </div>
        <div class="space-y-2">
          <label for="email" class="text-sm font-medium">Email</label>
          <Input 
            id="email"
            type="email"
            v-model="form.email"
            :error="form.errors.email"
          />
        </div>
        <div class="space-y-2">
          <label for="role" class="text-sm font-medium">Role</label>
          <Select v-model="form.role">
            <SelectTrigger>
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
        </div>
        <div class="space-y-2">
          <label for="status" class="text-sm font-medium">Status</label>
          <Select v-model="form.status">
            <SelectTrigger>
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
            Save Changes
          </Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>