<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { useToast } from '@/components/ui/toast/use-toast';
import { useForm } from '@inertiajs/vue3';

interface Props {
  user: {
    id: number;
    name: string;
  };
  show: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits(['close']);
const { toast } = useToast();

const form = useForm({});

const handleDelete = () => {
  form.delete(route('admin.users.destroy', props.user.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: "Success",
        description: "User deleted successfully",
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
        <DialogTitle>Delete User</DialogTitle>
        <DialogDescription class="text-muted-foreground">
          Are you sure you want to delete this user? This action cannot be undone.
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-4">
        <div class="rounded-lg border border-muted p-4">
          <p class="text-sm font-medium">User Details</p>
          <p class="text-sm text-muted-foreground mt-1">Name: {{ props.user.name }}</p>
        </div>
      </div>

      <DialogFooter class="mt-2">
        <div class="flex justify-end gap-3">
          <Button 
            type="button" 
            variant="outline" 
            @click="emit('close')"
          >
            Cancel
          </Button>
          <Button 
            type="button"
            variant="destructive"
            :disabled="form.processing"
            @click="handleDelete"
          >
            Delete User
          </Button>
        </div>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>