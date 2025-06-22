<script setup lang="ts">
import { ref, computed, watch } from 'vue';
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

interface Company {
  id: number;
  company_name: string;
  contact_person: string;
  address: string;
  contact_number: string;
  email: string;
}

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

// Company search state
const companySuggestions = ref<Company[]>([]);
const isLoadingCompanies = ref(false);

// Initialize companies with updated item structure
const companies = ref([
  {
    companyName: '',
    contactPerson: '',
    address: '',
    contactNumber: '',
    email: '',
    items: [{ description: '', quantity: 0, unit: '', unitPrice: 0 }]
  },
  {
    companyName: '',
    contactPerson: '',
    address: '',
    contactNumber: '',
    email: '',
    items: [{ description: '', quantity: 0, unit: '', unitPrice: 0 }]
  },
  {
    companyName: '',
    contactPerson: '',
    address: '',
    contactNumber: '',
    email: '',
    items: [{ description: '', quantity: 0, unit: '', unitPrice: 0 }]
  }
]);

// Load existing quotation data if provided
if (props.quotation) {
  // Extract unique descriptions from all companies
  const uniqueDescriptions = new Set<string>();
  props.quotation.details.forEach(detail => {
    detail.items.forEach(item => {
      // Use item_name as description if no description exists
      const description = item.description || item.item_name;
      uniqueDescriptions.add(description);
    });
  });
  
  const sharedDescriptions = Array.from(uniqueDescriptions);
  
  // Update companies with existing data
  companies.value = props.quotation.details.map((detail, index) => {
    // Create map of existing items for quick lookup
    const itemMap = new Map();
    detail.items.forEach(item => {
      const description = item.description || item.item_name;
      itemMap.set(description, {
        quantity: item.quantity,
        unitPrice: parseFloat(item.price)
      });
    });
    
    // Create items array with all shared descriptions
    const items = sharedDescriptions.map(description => {
      const existingItem = itemMap.get(description);
      return {
        description,
        quantity: existingItem ? existingItem.quantity : 0,
        unit: '', // Will need to be added manually as it's not in existing data
        unitPrice: existingItem ? existingItem.unitPrice : 0
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
  companies: []
});

// Company search functionality
const searchCompanies = async (searchTerm: string) => {
  console.log('Searching for:', searchTerm);
  
  if (!searchTerm || searchTerm.length < 2) {
    companySuggestions.value = [];
    return;
  }

  isLoadingCompanies.value = true;
  try {
    const response = await fetch(`/api/companies/search?search=${encodeURIComponent(searchTerm)}`);
    
    if (response.ok) {
      const data = await response.json();
      console.log('Search results:', data);
      companySuggestions.value = data;
    } else {
      console.error('Search failed:', response.status);
      companySuggestions.value = [];
    }
  } catch (error) {
    console.error('Error searching companies:', error);
    companySuggestions.value = [];
  } finally {
    isLoadingCompanies.value = false;
  }
};

// Watch company name changes to trigger search
watch(() => companies.value[currentStep.value - 1]?.companyName, (newValue) => {
  if (newValue) {
    searchCompanies(newValue);
  } else {
    companySuggestions.value = [];
  }
}, { deep: true });

// Select company from suggestions
const selectCompany = (company: Company) => {
  const currentCompanyIndex = currentStep.value - 1;
  
  companies.value[currentCompanyIndex].companyName = company.company_name;
  companies.value[currentCompanyIndex].contactPerson = company.contact_person || '';
  companies.value[currentCompanyIndex].address = company.address || '';
  companies.value[currentCompanyIndex].contactNumber = company.contact_number || '';
  companies.value[currentCompanyIndex].email = company.email || '';
  
  // Clear suggestions
  companySuggestions.value = [];
};

const validatePrice = (event: KeyboardEvent) => {
  if (!/[\d.]/.test(event.key) && event.key !== 'Backspace' && event.key !== 'Delete') {
    event.preventDefault();
  }
};

const validateQuantity = (event: KeyboardEvent) => {
  if (!/\d/.test(event.key) && event.key !== 'Backspace' && event.key !== 'Delete') {
    event.preventDefault();
  }
};

// Update description across all companies
const updateItemDescription = (itemIndex: number, newDescription: string) => {
  companies.value.forEach(company => {
    if (company.items[itemIndex]) {
      company.items[itemIndex].description = newDescription;
    }
  });
};

// Calculate amount for display
const calculateAmount = (quantity: number, unitPrice: number) => {
  const qty = parseFloat(String(quantity)) || 0;
  const price = parseFloat(String(unitPrice)) || 0;
  return (qty * price).toFixed(2);
};

const addItem = () => {
  companies.value.forEach(company => {
    company.items.push({
      description: '',
      quantity: 0,
      unit: '',
      unitPrice: 0
    });
  });
};

const removeItem = (itemIndex: number) => {
  if (companies.value[0].items.length > 1) {
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

const validateForm = () => {
  const errors = [];
  
  companies.value.forEach((company, companyIndex) => {
    if (!company.companyName.trim()) {
      errors.push(`Company ${companyIndex + 1}: Company name is required`);
    }
  });
  
  return errors;
};

const handleNext = () => {
  if (currentStep.value < 3) {
    currentStep.value++;
  } else {
    const validationErrors = validateForm();
    
    if (validationErrors.length > 0) {
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
  const formattedCompanies = companies.value.map(company => {
    const items = company.items
      .filter(item => item.description.trim() !== '')
      .map(item => ({
        name: item.description, // Use description as name for backend compatibility
        description: item.description,
        price: parseFloat(String(item.unitPrice)) || 0,
        quantity: parseInt(String(item.quantity)) || 0,
        unit: item.unit || '' // Include unit field
      }));
    
    return {
      companyName: company.companyName,
      contactPerson: company.contactPerson || '',
      address: company.address || '',
      contactNumber: company.contactNumber || '',
      email: company.email || '',
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
    <DialogContent class="sm:max-w-[1000px] h-[90vh] flex flex-col">
      <DialogHeader class="pb-4 border-b">
        <DialogTitle class="text-xl font-semibold">
          {{ quotation ? 'Update Quotation' : 'Add Quotation' }}
        </DialogTitle>
        <DialogDescription class="text-sm text-muted-foreground">
          Add quotation details from three different companies
        </DialogDescription>
      </DialogHeader>

      <!-- Stepper -->
      <div class="flex justify-center py-4 border-b">
        <Stepper v-model="currentStep" class="max-w-[600px] w-full">
          <StepperItem
            v-for="item in steps"
            :key="item.step"
            class="basis-1/3"
            :step="item.step"
          >
            <StepperTrigger class="w-full">
              <StepperIndicator class="w-8 h-8">
                <component :is="item.icon" class="w-4 h-4" />
              </StepperIndicator>
              <div class="flex flex-col text-center">
                <StepperTitle class="text-sm font-medium">{{ item.title }}</StepperTitle>
                <StepperDescription class="text-xs text-muted-foreground">{{ item.description }}</StepperDescription>
              </div>
            </StepperTrigger>
            <StepperSeparator v-if="item.step !== steps.length" class="w-full h-px bg-border" />
          </StepperItem>
        </Stepper>
      </div>

      <!-- Content -->
      <ScrollArea class="flex-1 px-1">
        <div class="space-y-6 pr-4">
          <div v-if="companies[currentStep - 1]" class="space-y-6">
            <!-- Company Information Card -->
            <Card class="border-2">
              <CardHeader class="pb-4">
                <CardTitle class="text-lg font-semibold">
                  {{ steps[currentStep - 1].title }} Information
                </CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <Label class="text-sm font-medium">
                      Company Name <span class="text-red-500">*</span>
                    </Label>
                    <div class="relative">
                      <Input 
                        v-model="companies[currentStep - 1].companyName"
                        placeholder="Type company name..."
                        class="h-10"
                      />
                      
                      <!-- Company Suggestions Dropdown -->
                      <div 
                        v-if="companySuggestions.length > 0" 
                        class="absolute z-50 w-full mt-1 bg-background border border-border rounded-md shadow-lg max-h-60 overflow-auto"
                      >
                        <div
                          v-for="company in companySuggestions"
                          :key="company.id"
                          class="px-3 py-3 hover:bg-accent hover:text-accent-foreground cursor-pointer border-b border-border last:border-b-0 transition-colors"
                          @click="selectCompany(company)"
                        >
                          <div class="font-medium text-foreground mb-1">{{ company.company_name }}</div>
                          <div class="text-sm text-muted-foreground mb-1">
                            <span v-if="company.contact_person">{{ company.contact_person }}</span>
                            <span v-if="company.contact_number" class="ml-1">• {{ company.contact_number }}</span>
                            <span v-if="company.email" class="ml-1">• {{ company.email }}</span>
                          </div>
                          <div v-if="company.address" class="text-xs text-muted-foreground italic">
                            📍 {{ company.address }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="space-y-2">
                    <Label class="text-sm font-medium">Contact Person</Label>
                    <Input 
                      v-model="companies[currentStep - 1].contactPerson"
                      placeholder="Enter contact person"
                      class="h-10"
                    />
                  </div>
                </div>
                <div class="space-y-2">
                  <Label class="text-sm font-medium">Address</Label>
                  <Input 
                    v-model="companies[currentStep - 1].address"
                    placeholder="Enter company address"
                    class="h-10"
                  />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <Label class="text-sm font-medium">Contact Number</Label>
                    <Input 
                      v-model="companies[currentStep - 1].contactNumber"
                      placeholder="Enter contact number"
                      class="h-10"
                    />
                  </div>
                  <div class="space-y-2">
                    <Label class="text-sm font-medium">Email</Label>
                    <Input 
                      v-model="companies[currentStep - 1].email"
                      type="email"
                      placeholder="Enter email address"
                      class="h-10"
                    />
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Items Card -->
            <Card class="border-2">
              <CardHeader class="pb-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                  <CardTitle class="text-lg font-semibold">Items & Pricing</CardTitle>
                  <div class="flex items-center gap-4">
                    <div class="text-xs text-muted-foreground italic font-medium bg-muted px-3 py-1 rounded-full">
                      Empty/zero values are allowed
                    </div>
                    <Button 
                      type="button" 
                      variant="outline" 
                      size="sm" 
                      class="gap-2 shrink-0"
                      @click="addItem"
                    >
                      <Plus class="w-4 h-4" />
                      Add Item
                    </Button>
                  </div>
                </div>
              </CardHeader>
              <CardContent>
                <div class="space-y-4">
                  <div 
                    v-for="(item, itemIndex) in companies[currentStep - 1].items" 
                    :key="itemIndex"
                    class="grid grid-cols-1 lg:grid-cols-[3fr,1fr,1fr,1fr,1fr,auto] gap-4 p-4 border rounded-lg bg-muted/20"
                  >
                    <!-- Description of Articles -->
                    <div class="space-y-2">
                      <Label class="text-sm font-medium flex items-center gap-1">
                        Description of Articles
                        <Repeat class="h-3 w-3 text-muted-foreground" title="Synchronized across companies" />
                      </Label>
                      <Input 
                        v-model="companies[currentStep - 1].items[itemIndex].description"
                        placeholder="Enter description of articles"
                        class="h-9"
                        @input="() => updateItemDescription(itemIndex, companies[currentStep - 1].items[itemIndex].description)"
                      />
                    </div>
                    
                    <!-- Quantity -->
                    <div class="space-y-2">
                      <Label class="text-sm font-medium">Qty</Label>
                      <Input 
                        v-model="item.quantity"
                        type="text"
                        inputmode="numeric"
                        placeholder="0"
                        @keypress="validateQuantity"
                        class="text-center h-9"
                      />
                    </div>
                    
                    <!-- Unit -->
                    <div class="space-y-2">
                      <Label class="text-sm font-medium">Unit</Label>
                      <Input 
                        v-model="item.unit"
                        placeholder="pc, kg, etc."
                        class="text-center h-9"
                      />
                    </div>
                    
                    <!-- Unit Price -->
                    <div class="space-y-2">
                      <Label class="text-sm font-medium">Unit Price</Label>
                      <Input 
                        v-model.number="item.unitPrice"
                        inputmode="decimal"
                        placeholder="0.00"
                        @keypress="validatePrice"
                        class="text-right h-9"
                      />
                    </div>
                    
                    <!-- Amount (calculated) -->
                    <div class="space-y-2">
                      <Label class="text-sm font-medium">Amount</Label>
                      <div class="h-9 px-3 py-2 bg-muted/50 border border-input rounded-md text-right text-sm text-muted-foreground">
                        {{ calculateAmount(item.quantity, item.unitPrice) }}
                      </div>
                    </div>
                    
                    <!-- Remove Button -->
                    <div class="flex items-end">
                      <Button 
                        type="button" 
                        variant="ghost" 
                        size="icon"
                        class="h-9 w-9"
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
        </div>
      </ScrollArea>

      <!-- Footer -->
      <div class="flex justify-between items-center pt-4 border-t">
        <Button 
          type="button" 
          variant="outline"
          @click="handlePrevious"
          :disabled="currentStep === 1"
        >
          Previous
        </Button>
        
        <div class="flex items-center gap-2">
          <span class="text-sm text-muted-foreground">
            Step {{ currentStep }} of {{ steps.length }}
          </span>
          <Button 
            type="button"
            @click="handleNext"
            :disabled="form.processing"
          >
            {{ currentStep === 3 ? 'Submit' : 'Next' }}
          </Button>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>