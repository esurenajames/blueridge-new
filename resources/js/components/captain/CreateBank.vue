<script setup lang="ts">
import { ref, watch, defineProps, defineEmits } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useToast } from '@/components/ui/toast/use-toast';
import Confirmation from '@/components/Confirmation.vue';
import { useForm } from '@inertiajs/vue3';
import { useForm as useVeeForm, useField } from 'vee-validate';
import { object, string } from 'yup';

const props = defineProps<{ show: boolean }>();
const emit = defineEmits(['close']);

const validationSchema = object({
  name: string().required('Account name is required'),
  bank: string().required('Bank name is required'),
  account_number: string().required('Account number is required'),
  status: string().required('Status is required'),
});

const { handleSubmit: validateForm } = useVeeForm({
  validationSchema,
});

const { value: name, errorMessage: nameError } = useField('name');
const { value: bank, errorMessage: bankError } = useField('bank');
const { value: account_number, errorMessage: accountNumberError } = useField('account_number');
const { value: status, errorMessage: statusError } = useField('status', undefined, {
  initialValue: 'active'
});

const form = useForm({
  name: '',
  bank: '',
  account_number: '',
  status: 'active',
});

const isOpen = ref(props.show);
const showConfirmation = ref(false);
const { toast } = useToast();

watch(() => props.show, (val) => {
  isOpen.value = val;
  if (val) {
    name.value = '';
    bank.value = '';
    account_number.value = '';
    status.value = 'active';
    form.reset();
  }
});
watch(isOpen, (val) => {
  if (!val) emit('close');
});

const handleSubmit = validateForm((values) => {
  form.name = name.value;
  form.bank = bank.value;
  form.account_number = account_number.value;
  form.status = status.value;
  showConfirmation.value = true;
});

const confirmCreate = () => {
  form.post(route('captain.bank-accounts.create'), {
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: "Success",
        description: "Bank account created successfully",
        variant: "success",
      });
      isOpen.value = false;
      showConfirmation.value = false;
      form.reset();
    },
    onError: () => {
      toast({
        title: "Error",
        description: "Failed to create bank account",
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
        <DialogTitle>Add Bank Account</DialogTitle>
        <DialogDescription>
          Fill out the form below to add a new bank account.
        </DialogDescription>
      </DialogHeader>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block mb-1 font-medium">Account Name</label>
          <Input v-model="name" placeholder="Enter account name" :class="nameError ? 'border-red-500 focus-visible:ring-red-500' : ''" />
          <span v-if="nameError" class="text-sm text-red-500">{{ nameError }}</span>
        </div>
        <div>
          <label class="block mb-1 font-medium">Bank Name</label>
          <Input v-model="bank" placeholder="Enter bank name" :class="bankError ? 'border-red-500 focus-visible:ring-red-500' : ''" />
          <span v-if="bankError" class="text-sm text-red-500">{{ bankError }}</span>
        </div>
        <div>
          <label class="block mb-1 font-medium">Account Number</label>
          <Input v-model="account_number" placeholder="Enter account number" :class="accountNumberError ? 'border-red-500 focus-visible:ring-red-500' : ''" />
          <span v-if="accountNumberError" class="text-sm text-red-500">{{ accountNumberError }}</span>
        </div>
        <div>
          <label class="block mb-1 font-medium">Status</label>
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
          <Button type="submit" :disabled="form.processing">Create Bank</Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>

  <Confirmation
    :show="showConfirmation"
    title="Create Bank Account"
    description="Are you sure you want to create this bank account?"
    @confirm="confirmCreate"
    @cancel="showConfirmation = false"
  />
</template>