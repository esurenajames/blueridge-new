<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Stepper, StepperDescription, StepperIndicator, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from '@/components/ui/stepper';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';
import { useForm as useVeeForm, useField } from 'vee-validate';
import { object, string, number } from 'yup';
import Confirmation from '@/components/Confirmation.vue';
import { useToast } from '@/components/ui/toast/use-toast';
import { Textarea } from '@/components/ui/textarea';
import { BanknoteIcon, WalletIcon, Check } from 'lucide-vue-next';

interface Bank {
  id: number;
  name: string;
  bank: string;
  account_number: string;
  status: string;
}

const props = defineProps<{
  show: boolean;
  request: {
    id: number;
    date?: string;
  };
}>();

const emit = defineEmits(['close']);

const steps = [{
  step: 1,
  title: 'Bank Details',
  description: 'Select bank account',
  icon: BanknoteIcon,
}, {
  step: 2,
  title: 'Payment Info',
  description: 'Enter payment details',
  icon: WalletIcon,
}];

const validationSchema = object({
  date: string().required('Date is required'),
  bank_id: string().required('Bank account is required'),
  payee: string().required('Payee is required'),
  amount: number().required('Amount is required').min(0, 'Amount must be positive'),
  purpose: string().required('Purpose is required'),
});

const { handleSubmit: validateForm } = useVeeForm({
  validationSchema,
});

const { value: date, errorMessage: dateError } = useField('date');
const { value: bank_id, errorMessage: bankError } = useField('bank_id');
const { value: payee, errorMessage: payeeError } = useField('payee');
const { value: amount, errorMessage: amountError } = useField('amount');
const { value: purpose, errorMessage: purposeError } = useField('purpose');

const banks = ref<Bank[]>([]);
const selectedBank = ref<Bank | null>(null);
const accountName = ref('');
const accountNumber = ref('');
const currentStep = ref(1);
const isStep1Valid = ref(false);

const form = useForm({
  date: '',
  bank_id: null as number | null,
  payee: '',
  amount: '',
  purpose: '',
});

const isOpen = ref(props.show);
const showConfirmation = ref(false);
const { toast } = useToast();

const validatePrice = (event: KeyboardEvent) => {
  // Allow only numbers, decimal point, and backspace
  if (!/[\d.]/.test(event.key) && event.key !== 'Backspace' && event.key !== 'Delete') {
    event.preventDefault();
  }
};

const isStepValid = computed(() => {
  switch (currentStep.value) {
    case 1:
      return isStep1Valid.value;
    case 2:
      return Boolean(payee.value && amount.value && purpose.value);
    default:
      return false;
  }
});

watch(selectedBank, (newBank) => {
  if (newBank) {
    accountName.value = newBank.bank;
    accountNumber.value = newBank.account_number;
    bank_id.value = String(newBank.id);
  } else {
    accountName.value = '';
    accountNumber.value = '';
    bank_id.value = '';
  }
});

watch([date, selectedBank], ([newDate, newBank]) => {
  isStep1Valid.value = Boolean(newDate && newBank);
}, { immediate: true });

onMounted(async () => {
  try {
    const response = await fetch(route('api.bank-accounts'));
    const data = await response.json();
    banks.value = data.data;
  } catch (error) {
    toast({
      title: "Error",
      description: "Failed to load bank accounts",
      variant: "destructive",
    });
  }
});

watch(() => props.show, (val) => {
  isOpen.value = val;
  if (val) {
    form.reset();
    selectedBank.value = null;
    currentStep.value = 1;
    date.value = new Date().toISOString().split('T')[0];
  }
});

watch(isOpen, (val) => {
  if (!val) emit('close');
});

const handleStepChange = (step: number) => {
  if (step > currentStep.value) {
    if (isStepValid.value) {
      currentStep.value = step;
    } else {
      toast({
        title: "Error",
        description: "Please fill in all required fields",
        variant: "destructive",
      });
    }
  } else {
    currentStep.value = step;
  }
};

const handleSubmit = validateForm((values) => {
  if (!selectedBank.value || !payee.value || !amount.value || !purpose.value) {
    toast({
      title: "Error",
      description: "Please fill in all required fields",
      variant: "destructive",
    });
    return;
  }

  form.date = date.value;
  form.bank_id = selectedBank.value.id;
  form.payee = payee.value;
  form.amount = amount.value;
  form.purpose = purpose.value;
  showConfirmation.value = true;
});

const confirmRelease = () => {
  form.post(route('captain.pbc.release', props.request.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast({
        title: "Success",
        description: "PBC Form has been released successfully",
        variant: "success",
      });
      isOpen.value = false;
      showConfirmation.value = false;
      form.reset();
    },
    onError: () => {
      toast({
        title: "Error",
        description: "Failed to release PBC Form",
        variant: "destructive",
      });
      showConfirmation.value = false;
    },
  });
};
</script>

<template>
  <Dialog :open="isOpen" @update:open="emit('close')">
    <DialogContent class="sm:max-w-[700px]">
      <DialogHeader>
        <DialogTitle>Release PBC Form</DialogTitle>
        <DialogDescription>
          Fill out the form below to release Punong Barangay Certification
        </DialogDescription>
      </DialogHeader>
      
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="flex justify-center">
          <Stepper v-model="currentStep" class="max-w-[600px]">
            <StepperItem
              v-for="item in steps"
              :key="item.step"
              class="basis-1/2"
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

        <div class="mt-4">
          <!-- Step 1: Bank Details -->
          <div v-if="currentStep === 1" class="space-y-4">
            <div class="grid gap-4">
              <div class="space-y-2">
                <label class="text-sm font-medium">Date</label>
                <Input 
                  type="date" 
                  v-model="date" 
                  :class="{ 'border-red-500': dateError }"
                />
                <span v-if="dateError" class="text-sm text-red-500">{{ dateError }}</span>
              </div>
              
              <div class="space-y-2">
                <label class="text-sm font-medium">Bank Name</label>
                <Select v-model="selectedBank">
                  <SelectTrigger :class="{ 'border-red-500': bankError }">
                    <SelectValue placeholder="Select bank" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem 
                      v-for="bank in banks" 
                      :key="bank.id" 
                      :value="bank"
                    >
                      {{ bank.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <span v-if="bankError" class="text-sm text-red-500">{{ bankError }}</span>
              </div>

              <!-- Account Name -->
              <div class="space-y-2" v-if="selectedBank">
                <label class="text-sm font-medium">Account Name</label>
                <Input 
                  v-model="accountName"
                  disabled
                />
              </div>

              <!-- Account Number -->
              <div class="space-y-2" v-if="selectedBank">
                <label class="text-sm font-medium">Account Number</label>
                <Input 
                  v-model="accountNumber"
                  disabled
                />
              </div>
            </div>
          </div>

          <!-- Step 2: Payment Info -->
          <div v-if="currentStep === 2" class="space-y-4">
            <div class="grid gap-4">
              <div class="space-y-2">
                <label class="text-sm font-medium">Payee</label>
                <Input 
                  v-model="payee" 
                  placeholder="Enter payee name"
                  :class="{ 'border-red-500': payeeError }"
                />
                <span v-if="payeeError" class="text-sm text-red-500">{{ payeeError }}</span>
              </div>
              
              <div class="space-y-2">
                <label class="text-sm font-medium">Amount</label>
                <Input 
                  v-model="amount"
                  type="text"
                  step="0.01"
                  placeholder="Enter amount"
                  @keydown="validatePrice"
                  :class="{ 'border-red-500': amountError }"
                />
                <span v-if="amountError" class="text-sm text-red-500">{{ amountError }}</span>
              </div>
              
              <div class="space-y-2">
                <label class="text-sm font-medium">Purpose</label>
                <Textarea
                  v-model="purpose"
                  placeholder="Enter purpose"
                  :class="{ 'border-red-500': purposeError }"
                />
                <span v-if="purposeError" class="text-sm text-red-500">{{ purposeError }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-between mt-6">
          <Button
            type="button"
            variant="outline"
            @click="currentStep > 1 ? handleStepChange(currentStep - 1) : emit('close')"
          >
            {{ currentStep === 1 ? 'Cancel' : 'Previous' }}
          </Button>
          <Button
            v-if="currentStep < steps.length"
            type="button"
            @click="handleStepChange(currentStep + 1)"
            :disabled="!isStepValid"
          >
            Next
          </Button>
          <Button
            v-if="currentStep === steps.length"
            type="submit"
            :disabled="form.processing || !isStepValid"
          >
            Release PBC
          </Button>
        </div>
      </form>

      <Confirmation
        :show="showConfirmation"
        title="Release PBC Form"
        description="Are you sure you want to release this PBC Form?"
        @confirm="confirmRelease"
        @cancel="showConfirmation = false"
      />
    </DialogContent>
  </Dialog>
</template>