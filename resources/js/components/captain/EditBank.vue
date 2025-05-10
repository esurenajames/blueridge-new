<script setup lang="ts">
import { ref, watch, defineProps, defineEmits } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';
import { useForm as useVeeForm, useField } from 'vee-validate';
import { object, string } from 'yup';
import Confirmation from '@/components/Confirmation.vue';
import { useToast } from '@/components/ui/toast/use-toast';

const props = defineProps<{
  show: boolean;
  bank: {
    id: number;
    name: string;
    bank: string;
    account_number: string;
    status: 'active' | 'inactive';
  };
}>();

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

const { value: name, errorMessage: nameError } = useField('name', undefined, { initialValue: props.bank.name });
const { value: bank, errorMessage: bankError } = useField('bank', undefined, { initialValue: props.bank.bank });
const { value: account_number, errorMessage: accountNumberError } = useField('account_number', undefined, { initialValue: props.bank.account_number });
const { value: status, errorMessage: statusError } = useField('status', undefined, { initialValue: props.bank.status });

const form = useForm({
  name: props.bank.name,
  bank: props.bank.bank,
  account_number: props.bank.account_number,
  status: props.bank.status,
});

const isOpen = ref(props.show);
const showConfirmation = ref(false);
const { toast } = useToast();

watch(() => props.show, (val) => {
  isOpen.value = val;
  if (val) {
    name.value = props.bank.name;
    bank.value = props.bank.bank;
    account_number.value = props.bank.account_number;
    status.value = props.bank.status;
    form.reset();
    form.name = props.bank.name;
    form.bank = props.bank.bank;
    form.account_number = props.bank.account_number;
    form.status = props.bank.status;
  }
});
watch(isOpen, (val) => {
  if (!val) emit('close');
});

// Use the same confirmation logic as CreateBank
const handleSubmit = validateForm((values) => {
  form.name = name.value;
  form.bank = bank.value;
  form.account_number = account_number.value;
  form.status = status.value;
  showConfirmation.value = true;
});

const confirmEdit = () => {
  form.put(route('captain.bank-accounts.update', props.bank.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: "Success",
        description: "Bank account updated successfully",
        variant: "success",
      });
      isOpen.value = false;
      showConfirmation.value = false;
      form.reset();
    },
    onError: () => {
      toast({
        title: "Error",
        description: "Failed to update bank account",
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
        <DialogTitle>Edit Bank Account</DialogTitle>
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
          <Button type="submit" :disabled="form.processing">Save Changes</Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>

  <Confirmation
    :show="showConfirmation"
    title="Edit Bank Account"
    description="Are you sure you want to save these changes?"
    @confirm="confirmEdit"
    @cancel="showConfirmation = false"
  />
</template>