<script setup lang="ts">
import { ref } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useToast } from '@/components/ui/toast/use-toast';
import { KeyRound } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { Clipboard } from 'lucide-vue-next';

interface Props {
  user: {
    id: number;
    name: string;
    email: string;
  };
  show: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits(['close']);

const generatedPassword = ref('');
const form = useForm({
  password: '',
});

const { toast } = useToast();

const generatePassword = () => {
  const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
  let newPassword = '';
  for (let i = 0; i < 12; i++) {
    const randomIndex = Math.floor(Math.random() * charset.length);
    newPassword += charset[randomIndex];
  }
  generatedPassword.value = newPassword;
  form.password = newPassword;
};

const copyToClipboard = () => {
    navigator.clipboard.writeText(password.value);
    toast({
        title: "Success",
        description: "Password copied to clipboard",
        variant: "success",
    });
};

const handleSubmit = () => {
  form.put(route('admin.users.update-password', props.user.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: "Success",
        description: "Password updated successfully",
        variant: "success",
      });
      emit('close');
      form.reset();
      generatedPassword.value = '';
    },
    onError: (errors) => {
      toast({
        title: "Error",
        description: errors.error || "Failed to update password",
        variant: "destructive",
      });
    },
  });
};
</script>

<template>
  <Dialog :open="show" @update:open="emit('close')">
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Generate New Password</DialogTitle>
        <DialogDescription>
          Generate a new password for {{ user.name }}
        </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="space-y-2">
          <label for="password" class="text-sm font-medium">New Password</label>
         <div class="flex gap-2">
            <div class="relative flex-1">
              <Input 
                id="password"
                type="text"
                v-model="generatedPassword"
                class="pr-10"
                readonly
              />
              <button
                type="button"
                class="absolute right-3 top-1/2 -translate-y-1/2 hover:text-gray-600"
                @click="copyToClipboard"
                :disabled="!generatedPassword"
              >
                <Clipboard class="h-4 w-4" />
              </button>
            </div>
            <Button 
              type="button"
              variant="outline"
              @click="generatePassword"
            >
              <KeyRound class="h-4 w-4 mr-2" />
              Generate
            </Button>
          </div>
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
            :disabled="form.processing || !generatedPassword"
          >
            {{ form.processing ? 'Updating...' : 'Update Password' }}
          </Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>