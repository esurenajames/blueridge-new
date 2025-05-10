<script setup lang="ts">
import { ref, computed } from 'vue';
import { useToast } from '@/components/ui/toast';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Stepper, StepperContent, StepperDescription, StepperIndicator, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from '@/components/ui/stepper';
import { Building, Building2, Warehouse, Plus, Trash2 } from 'lucide-vue-next';
import type { Company, CompanyItem } from '@/types';

const props = defineProps<{
  show: boolean;
  requestId: number;
  quotation?: {
    details: Array<{
      company: {
        company_name: string;
        contact_person: string;
        address: string;
        contact_number: string;
        email: string;
      };
      items: Array<{
        item_name: string;
        description: string;
        price: string;
        quantity: number;
      }>;
    }>;
  };
}>();

const emit = defineEmits<{
  (e: 'close'): void;
}>();

const { toast } = useToast();
const currentStep = ref(1);

// Initialize companies with 3 empty companies
const companies = ref<Company[]>(
  props.quotation ? props.quotation.details.map(detail => ({
    companyName: detail.company.company_name,
    contactPerson: detail.company.contact_person,
    address: detail.company.address,
    contactNumber: detail.company.contact_number,
    email: detail.company.email,
    items: detail.items.map(item => ({
      name: item.item_name,
      description: item.description,
      price: parseFloat(item.price),
      quantity: item.quantity
    }))
  })) : [
    {
      companyName: '',
      contactPerson: '',
      address: '',
      contactNumber: '',
      email: '',
      items: [{
        name: '',
        description: '',
        price: null,
        quantity: null
      }]
    },
    {
      companyName: '',
      contactPerson: '',
      address: '',
      contactNumber: '',
      email: '',
      items: [{
        name: '',
        description: '',
        price: null,
        quantity: null
      }]
    },
    {
      companyName: '',
      contactPerson: '',
      address: '',
      contactNumber: '',
      email: '',
      items: [{
        name: '',
        description: '',
        price: null,
        quantity: null
      }]
    }
  ]
);

const steps = computed(() => [
  {
    step: 1,
    title: 'First Company',
    description: 'Quotation 1',
    icon: Building
  },
  {
    step: 2,
    title: 'Second Company',
    description: 'Quotation 2',
    icon: Building2
  },
  {
    step: 3,
    title: 'Third Company',
    description: 'Quotation 3',
    icon: Warehouse
  }
]);

const form = useForm({
  request_id: props.requestId,
  companies: companies.value
});

const validatePrice = (event: KeyboardEvent) => {
  // Allow only numbers and decimal point
  if (!/[\d.]/.test(event.key)) {
    event.preventDefault();
  }
};

const formatNumber = (num: number | null) => {
  if (!num) return '0';
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};

const validateQuantity = (event: KeyboardEvent) => {
  // Allow only numbers
  if (!/\d/.test(event.key)) {
    event.preventDefault();
  }
};

const addItem = (companyIndex: number) => {
  companies.value[companyIndex - 1].items.push({
    name: '',
    description: '',
    price: 0,
    quantity: 0
  });
};

const removeItem = (companyIndex: number, itemIndex: number) => {
  const company = companies.value[companyIndex - 1];
  if (company.items.length > 1) {
    company.items.splice(itemIndex, 1);
  }
};

const handlePrevious = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};

const handleNext = () => {
  if (currentStep.value < 3) {
    currentStep.value++;
  } else {
    handleSubmit();
  }
};

const handleSubmit = () => {
  form.companies = companies.value;

  const routeName = props.quotation
    ? 'officials.requests.quotation.resubmit'
    : 'officials.requests.quotation.submit';

  form.post(route(routeName, props.requestId), {
    onSuccess: () => {
      emit('close');
      toast({
        title: 'Success',
        description: props.quotation
          ? 'Quotation has been resubmitted successfully.'
          : 'Quotation has been added successfully.',
        variant: 'success'
      });
    },
    onError: () => {
      toast({
        title: 'Error',
        description: 'There was an error submitting the quotation.',
        variant: 'destructive'
      });
    }
  });
};
</script>

<template>
  <Dialog :open="show" @update:open="emit('close')">
    <DialogContent class="sm:max-w-[800px] h-[88vh] flex flex-col">
      <DialogHeader>
        <DialogTitle>Add Quotation</DialogTitle>
        <DialogDescription>
          Add quotation details from three different companies
        </DialogDescription>
      </DialogHeader>

      <div class="flex justify-center mb-2">
        <Stepper v-model="currentStep" class="max-w-[600px]">
          <StepperItem
            v-for="item in steps"
            :key="item.step"
            class="basis-1/3"
            :step="item.step"
          >
            <StepperTrigger>
              <StepperIndicator>
                <component :is="item.icon" class="w-4" />
              </StepperIndicator>
              <div class="flex flex-col">
                <StepperTitle>{{ item.title }}</StepperTitle>
                <StepperDescription class="whitespace-nowrap">{{ item.description }}</StepperDescription>
              </div>
            </StepperTrigger>
            <StepperSeparator v-if="item.step !== steps.length" class="w-full h-px" />
          </StepperItem>
        </Stepper>
      </div>

      <ScrollArea class="flex-1" style="min-width: 600px;">
        <form @submit.prevent="handleSubmit" class="space-y-6 px-1">
          <div v-if="companies[currentStep - 1]" class="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle class="text-lg">{{ steps[currentStep - 1].title }} Information</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <Label>Company Name</Label>
                    <Input 
                      v-model="companies[currentStep - 1].companyName" 
                      placeholder="Enter company name" 
                    />
                  </div>
                  <div class="space-y-2">
                    <Label>Contact Person</Label>
                    <Input 
                      v-model="companies[currentStep - 1].contactPerson"
                      placeholder="Enter contact person"
                    />
                  </div>
                </div>
                <div class="space-y-2">
                  <Label>Address</Label>
                  <Input 
                    v-model="companies[currentStep - 1].address"
                    placeholder="Enter company address"
                  />
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <Label>Contact Number</Label>
                    <Input 
                      v-model="companies[currentStep - 1].contactNumber"
                      placeholder="Enter contact number"
                    />
                  </div>
                  <div class="space-y-2">
                    <Label>Email</Label>
                    <Input 
                      v-model="companies[currentStep - 1].email"
                      type="email"
                      placeholder="Enter email address"
                    />
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle class="text-lg">Items</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-4">
                  <div class="grid gap-4">
                    <div 
                      v-for="(item, itemIndex) in companies[currentStep - 1].items" 
                      :key="itemIndex"
                      class="grid grid-cols-[2fr,2fr,1fr,1fr,auto] gap-4 items-end"
                    >
                      <div class="space-y-2">
                        <Label>Item Name</Label>
                        <Input 
                          v-model="item.name"
                          placeholder="Enter item name"
                        />
                      </div>
                      <div class="space-y-2">
                        <Label>Description</Label>
                        <Input 
                          v-model="item.description"
                          placeholder="Enter item description"
                        />
                      </div>
                      <div class="space-y-2">
                        <Label>Unit Price</Label>
                        <Input 
                          v-model.number="item.price"
                          inputmode="decimal"
                          placeholder="0.00"
                          @keypress="validatePrice"
                          class="text-right"
                        />
                      </div>
                      <div class="space-y-2">
                        <Label>Quantity</Label>
                        <Input 
                          v-model="item.quantity"
                          type="text"
                          inputmode="numeric"
                          placeholder="0"
                          @keypress="validateQuantity"
                          class="text-right"
                        />
                      </div>
                      <Button 
                        type="button" 
                        variant="ghost" 
                        size="icon"
                        @click="removeItem(currentStep, itemIndex)"
                        :disabled="companies[currentStep - 1].items.length === 1"
                      >
                        <Trash2 class="w-4 h-4" />
                      </Button>
                    </div>
                  </div>
                  <Button 
                    type="button" 
                    variant="outline" 
                    size="sm" 
                    class="gap-2"
                    @click="addItem(currentStep)"
                  >
                    <Plus class="w-4 h-4" />
                    Add Item
                  </Button>
                </div>
              </CardContent>
            </Card>
          </div>
        </form>
      </ScrollArea>

      <div class="flex justify-between gap-2 pt-4 border-t">
        <Button 
          type="button" 
          variant="outline"
          @click="handlePrevious"
          :disabled="currentStep === 1"
        >
          Previous
        </Button>
        
        <div class="flex gap-2">            
          <Button 
            type="button"
            @click="handleNext"
          >
            {{ currentStep === 3 ? 'Submit' : 'Next' }}
          </Button>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>