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
import { Building, Building2, Warehouse, Plus, Trash2, Repeat } from 'lucide-vue-next';
import type { Company } from '@/types';

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

// Initialize companies with their own item details
const companies = ref<Array<{
  companyName: string;
  contactPerson: string;
  address: string;
  contactNumber: string;
  email: string;
  items: Array<{
    name: string;
    description: string;
    quantity: number;
    price: number;
  }>;
}>>([]);

// Populate data if existing quotation is provided
if (props.quotation) {
  // Extract unique item names from all companies
  const uniqueItemNames = new Set<string>();
  props.quotation.details.forEach(detail => {
    detail.items.forEach(item => {
      uniqueItemNames.add(item.item_name);
    });
  });
  
  const sharedItemNames = Array.from(uniqueItemNames);
  
  // Create company data with items for each company
  companies.value = props.quotation.details.map(detail => {
    // Create map of existing items for quick lookup
    const itemMap = new Map();
    detail.items.forEach(item => {
      itemMap.set(item.item_name, {
        description: item.description,
        quantity: item.quantity,
        price: parseFloat(item.price)
      });
    });
    
    // Create items array with all shared names
    const items = sharedItemNames.map(name => {
      const existingItem = itemMap.get(name);
      return {
        name,
        description: existingItem ? existingItem.description : '',
        quantity: existingItem ? existingItem.quantity : 0,
        price: existingItem ? existingItem.price : 0
      };
    });
    
    return {
      companyName: detail.company.company_name,
      contactPerson: detail.company.contact_person,
      address: detail.company.address,
      contactNumber: detail.company.contact_number,
      email: detail.company.email,
      items
    };
  });
} else {
  // Initialize 3 empty companies if no existing data
  companies.value = Array(3).fill(null).map(() => ({
    companyName: '',
    contactPerson: '',
    address: '',
    contactNumber: '',
    email: '',
    items: [{ name: '', description: '', quantity: 0, price: 0 }]
  }));
}

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
  companies: [] // Will be populated on submit
});

const validatePrice = (event: KeyboardEvent) => {
  // Allow only numbers, decimal point, and backspace
  if (!/[\d.]/.test(event.key) && event.key !== 'Backspace' && event.key !== 'Delete') {
    event.preventDefault();
  }
};

const formatNumber = (num: number | null) => {
  if (!num) return '0';
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};

const validateQuantity = (event: KeyboardEvent) => {
  // Allow only numbers and backspace
  if (!/\d/.test(event.key) && event.key !== 'Backspace' && event.key !== 'Delete') {
    event.preventDefault();
  }
};

// Update item name across all companies
const updateItemName = (itemIndex: number, newName: string) => {
  // Update name in all companies
  companies.value.forEach(company => {
    company.items[itemIndex].name = newName;
  });
};

const addItem = () => {
  // Add corresponding item to each company
  companies.value.forEach(company => {
    company.items.push({
      name: '',
      description: '',
      quantity: 0,
      price: 0
    });
  });
};

const removeItem = (itemIndex: number) => {
  if (companies.value[0].items.length > 1) {
    // Remove corresponding item from each company
    companies.value.forEach(company => {
      company.items.splice(itemIndex, 1);
    });
  }
};

const handlePrevious = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};

// Validate form before submission - we'll only check for required company fields
const validateForm = () => {
  const errors = [];
  
  // Validate company information (basic checks)
  companies.value.forEach((company, companyIndex) => {
    // Check required company fields - at a minimum, company name is required
    if (!company.companyName.trim()) {
      errors.push(`Company ${companyIndex + 1}: Company name is required`);
    }
    // Email is optional now
  });
  
  return errors;
};

// Skip validation and go straight to submit
const handleNext = () => {
  if (currentStep.value < 3) {
    currentStep.value++;
  } else {
    // Only check company name
    const validationErrors = validateForm();
    
    if (validationErrors.length > 0) {
      // Show validation errors
      validationErrors.forEach(error => {
        toast({
          title: 'Validation Error',
          description: error,
          variant: 'destructive'
        });
      });
    } else {
      handleSubmit();
    }
  }
};

const handleSubmit = () => {
  // Format companies for the API - always ensure price and quantity have values (0 if empty)
  const formattedCompanies = companies.value.map(company => {
    // Filter out items with empty names
    const items = company.items
      .filter(item => item.name.trim() !== '')
      .map(item => {
        // Convert to number or force to 0 if NaN
        const price = parseFloat(String(item.price)) || 0;
        const quantity = parseInt(String(item.quantity)) || 0;
        
        return {
          name: item.name,
          description: item.description || '',
          price: price,
          quantity: quantity
        };
      });
    
    return {
      companyName: company.companyName,
      contactPerson: company.contactPerson || '',
      address: company.address || '',
      contactNumber: company.contactNumber || '',
      email: company.email || '', // Make email optional
      items
    };
  });
  
  form.companies = formattedCompanies;

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
    onError: (errors) => {
      console.error(errors);
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
                    <Label>Company Name <span class="text-destructive">*</span></Label>
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
              <CardHeader class="flex flex-row items-center justify-between">
                <CardTitle class="text-lg">Items</CardTitle>
                <div class="text-xs text-muted-foreground italic font-bold">
                  Empty/zero price or quantity values are allowed
                </div>
                <Button 
                  type="button" 
                  variant="outline" 
                  size="sm" 
                  class="gap-2"
                  @click="addItem"
                >
                  <Plus class="w-4 h-4" />
                  Add Item
                </Button>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-4">
                  <div class="grid gap-4">
                    <div 
                      v-for="(item, itemIndex) in companies[currentStep - 1].items" 
                      :key="itemIndex"
                      class="grid grid-cols-[1.5fr,1.5fr,1fr,1fr,auto] gap-4 items-end border-b pb-4 mb-2"
                    >
                      <div class="space-y-2">
                        <Label class="flex items-center gap-1">
                          Item Name
                          <Repeat class="h-3 w-3 text-muted-foreground" title="Synchronized across companies" />
                        </Label>
                        <Input 
                          v-model="companies[currentStep - 1].items[itemIndex].name"
                          placeholder="Enter item name"
                          @input="() => updateItemName(itemIndex, companies[currentStep - 1].items[itemIndex].name)"
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
                      <Button 
                        type="button" 
                        variant="ghost" 
                        size="icon"
                        @click="removeItem(itemIndex)"
                        :disabled="companies[currentStep - 1].items.length === 1"
                      >
                        <Trash2 class="w-4 h-4" />
                      </Button>
                    </div>
                  </div>
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