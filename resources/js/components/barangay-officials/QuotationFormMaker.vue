<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useToast } from '@/components/ui/toast';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Combobox, ComboboxContent, ComboboxInput, ComboboxItem, ComboboxTrigger } from '@/components/ui/combobox';
import { Plus, Trash2, FileText, Download, Eye, Building, Send } from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps<{
  show: boolean;
  requestId?: number;
  currentUser?: {
    id: number;
    name: string;
    email: string;
    role: string;
    login: string;
  };
  currentDateTime?: string;
  requestData?: {
    title: string;
    description: string;
    items: Array<{
      name: string;
      description: string;
      quantity: number;
      unit: string;
    }>;
  };
}>();

const emit = defineEmits<{
  (e: 'close'): void;
}>();

const { toast } = useToast();

// Company suggestions
const companySuggestions = ref([]);
const isLoadingCompanies = ref(false);

// Unit options for dropdown
const unitOptions = [
  'pc', 'pcs', 'piece', 'pieces',
  'unit', 'units',
  'box', 'boxes',
  'pack', 'packs',
  'set', 'sets',
  'pair', 'pairs',
  'bottle', 'bottles',
  'can', 'cans',
  'bag', 'bags',
  'roll', 'rolls',
  'sheet', 'sheets',
  'meter', 'meters', 'm',
  'centimeter', 'centimeters', 'cm',
  'kilogram', 'kilograms', 'kg',
  'gram', 'grams', 'g',
  'liter', 'liters', 'L',
  'milliliter', 'milliliters', 'mL',
  'dozen', 'dozens',
  'gross',
  'carton', 'cartons',
  'case', 'cases'
];

// Helper function to get official title
const getOfficialTitle = (role: string) => {
  const titles: Record<string, string> = {
    'captain': 'Punong Barangay',
    'secretary': 'Barangay Secretary / Requesting Official',
    'treasurer': 'Barangay Treasurer / Requesting Official',
    'official': 'Barangay Official / Requesting Official',
    'councilor': 'Barangay Councilor / Requesting Official',
    'sk_chairman': 'SK Chairman / Requesting Official',
  };
  
  return titles[role] || 'Barangay Official / Requesting Official';
};

// Form data with auto-filled user information
const formData = ref({
  // Barangay Information (from your system)
  barangayName: 'BARANGAY BLUE RIDGE B',
  barangayLocation: 'Quezon City, District III',
  requestingOfficial: props.currentUser?.name || '',
  officialTitle: getOfficialTitle(props.currentUser?.role || 'official'),
  requestingOfficialLogin: props.currentUser?.login || '',
  currentDateTime: props.currentDateTime || '',
  
  // Company Information (to be filled)
  companyName: '',
  companyAddress: '',
  contactPerson: '',
  contactNumber: '',
  email: '',
  
  // Request Details
  requestTitle: props.requestData?.title || '',
  requestDescription: 'Please quote your lowest government net prices/taxes included on the items listed hereunder, specifying terms of sales, delivery, and applicability of quotation. Submit to this office within two (2) government working days for repair and/or job order after receipt hereof.',
  
  // Items - completely empty
  items: []
});

// Form for saving companies to database
const saveForm = useForm({
  companies: []
});

// Search companies function
const searchCompanies = async (searchTerm: string) => {
  if (!searchTerm || searchTerm.length < 2) {
    companySuggestions.value = [];
    return;
  }

  isLoadingCompanies.value = true;
  try {
    const response = await axios.get(route('officials.requests.companies.search'), {
      params: { search: searchTerm }
    });
    companySuggestions.value = response.data;
  } catch (error) {
    console.error('Error searching companies:', error);
    companySuggestions.value = [];
  } finally {
    isLoadingCompanies.value = false;
  }
};

// Function to get company display text
const getCompanyDisplayText = (company) => {
  let display = company.company_name;
  
  if (company.contact_person) {
    display += ` (${company.contact_person})`;
  }
  
  if (company.contact_number) {
    display += ` - ${company.contact_number}`;
  }
  
  if (company.email) {
    display += ` - ${company.email}`;
  }
  
  return display;
};

// Function to select a company
const selectCompany = (company) => {
  formData.value.companyName = company.company_name;
  formData.value.companyAddress = company.address || '';
  formData.value.contactPerson = company.contact_person || '';
  formData.value.contactNumber = company.contact_number || '';
  formData.value.email = company.email || '';
  companySuggestions.value = [];
};

// Function to filter units based on search
const getFilteredUnits = (searchTerm: string) => {
  if (!searchTerm) return unitOptions;
  return unitOptions.filter(unit => 
    unit.toLowerCase().includes(searchTerm.toLowerCase())
  );
};

const addItem = () => {
  formData.value.items.push({
    description: '',
    quantity: '',
    unit: '',
    unitPrice: ''
  });
};

const removeItem = (index: number) => {
  if (formData.value.items.length > 1) {
    formData.value.items.splice(index, 1);
  } else if (formData.value.items.length === 1) {
    // If only one item, reset it instead of removing
    formData.value.items[0] = {
      description: '',
      quantity: '',
      unit: '',
      unitPrice: ''
    };
  }
};

// Initialize with one empty item on component mount
if (formData.value.items.length === 0) {
  addItem();
}

const validateForm = () => {
  const errors = [];
  
  if (!formData.value.companyName.trim()) {
    errors.push('Company name is required');
  }
  
  if (!formData.value.companyAddress.trim()) {
    errors.push('Company address is required');
  }
  
  const hasValidItems = formData.value.items.some(item => 
    item.description.trim()
  );
  
  if (!hasValidItems) {
    errors.push('At least one item with description is required');
  }
  
  return errors;
};

const saveCompaniesToDatabase = () => {
  const errors = validateForm();
  
  if (errors.length > 0) {
    errors.forEach(error => {
      toast({
        title: 'Validation Error',
        description: error,
        variant: 'destructive'
      });
    });
    return;
  }

  // Prepare companies data for saving
  const companiesData = [{
    companyName: formData.value.companyName,
    companyAddress: formData.value.companyAddress,
    contactPerson: formData.value.contactPerson,
    contactNumber: formData.value.contactNumber,
    email: formData.value.email,
  }];

  saveForm.companies = companiesData;

  if (props.requestId) {
    saveForm.post(route('officials.requests.quotation-request-companies.save', props.requestId), {
      onSuccess: () => {
        // Simple success toast
        toast({
          title: 'Success',
          description: 'Company saved successfully.',
          variant: 'default'
        });
        
        // DON'T clear the form - keep company details
        // User might want to generate PDF with same company details
      },
      onError: (errors) => {
        console.error('Save errors:', errors);
        toast({
          title: 'Error',
          description: 'There was an error saving the company information.',
          variant: 'destructive'
        });
      }
    });
  }
};

// Rest of your existing functions (generatePDF, previewPDF, generatePrintableHTML, handleClose)
// ... keeping them exactly the same as before ...

const generatePDF = () => {
  const errors = validateForm();
  
  if (errors.length > 0) {
    errors.forEach(error => {
      toast({
        title: 'Validation Error',
        description: error,
        variant: 'destructive'
      });
    });
    return;
  }
  
  const printWindow = window.open('', '_blank');
  
  if (!printWindow) {
    toast({
      title: 'Error',
      description: 'Please allow popups to generate the PDF',
      variant: 'destructive'
    });
    return;
  }
  
  const printContent = generatePrintableHTML();
  
  printWindow.document.write(printContent);
  printWindow.document.close();
  
  setTimeout(() => {
    printWindow.print();
  }, 500);
};

const previewPDF = () => {
  const errors = validateForm();
  
  if (errors.length > 0) {
    errors.forEach(error => {
      toast({
        title: 'Validation Error',
        description: error,
        variant: 'destructive'
      });
    });
    return;
  }
  
  const previewWindow = window.open('', '_blank');
  
  if (!previewWindow) {
    toast({
      title: 'Error',
      description: 'Please allow popups to preview the document',
      variant: 'destructive'
    });
    return;
  }
  
  const previewContent = generatePrintableHTML();
  previewWindow.document.write(previewContent);
  previewWindow.document.close();
};

const generatePrintableHTML = () => {
  const validItems = formData.value.items.filter(item => item.description.trim());
  
  const itemRows = validItems.map((item, index) => {
    const quantity = parseFloat(item.quantity) || 0;
    const unitPrice = parseFloat(item.unitPrice) || 0;
    const amount = quantity * unitPrice;
    
    return `
      <tr>
        <td style="border: 1px solid black; padding: 4px; text-align: center; font-size: 11px;">${index + 1}</td>
        <td style="border: 1px solid black; padding: 4px; text-align: center; font-size: 11px;">${quantity}</td>
        <td style="border: 1px solid black; padding: 4px; text-align: center; font-size: 11px;">${item.unit || ''}</td>
        <td style="border: 1px solid black; padding: 4px; font-size: 11px;">${item.description}</td>
        <td style="border: 1px solid black; padding: 4px; text-align: right; font-size: 11px;">${unitPrice.toFixed(2)}</td>
        <td style="border: 1px solid black; padding: 4px; text-align: right; font-size: 11px;">${amount.toFixed(2)}</td>
      </tr>
    `;
  }).join('');
  
  const totalAmount = validItems.reduce((sum, item) => {
    const quantity = parseFloat(item.quantity) || 0;
    const unitPrice = parseFloat(item.unitPrice) || 0;
    return sum + (quantity * unitPrice);
  }, 0);
  
  const maxRows = 7;
  const emptyRowsNeeded = Math.max(0, maxRows - validItems.length);
  const emptyRows = Array(emptyRowsNeeded).fill(0).map(() => `
    <tr>
      <td style="border: 1px solid black; padding: 4px; height: 25px; font-size: 11px;"></td>
      <td style="border: 1px solid black; padding: 4px; font-size: 11px;"></td>
      <td style="border: 1px solid black; padding: 4px; font-size: 11px;"></td>
      <td style="border: 1px solid black; padding: 4px; font-size: 11px;"></td>
      <td style="border: 1px solid black; padding: 4px; font-size: 11px;"></td>
      <td style="border: 1px solid black; padding: 4px; font-size: 11px;"></td>
    </tr>
  `).join('');
  
  return `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Quotation Request Form</title>
      <style>
        @media print {
          @page {
            margin: 1in;
            size: A4;
          }
          body { margin: 0; }
        }
        
        body {
          font-family: Arial, sans-serif;
          font-size: 11px;
          line-height: 1.2;
          max-width: 8.5in;
          margin: 0 auto;
          padding: 20px;
        }
        
        .header {
          text-align: center;
          margin-bottom: 20px;
        }
        
        .header h1 {
          margin: 5px 0;
          font-size: 13px;
          font-weight: bold;
        }
        
        .company-info {
          margin-bottom: 20px;
        }
        
        .company-info h2 {
          margin: 5px 0;
          font-size: 11px;
          font-weight: bold;
        }
        
        .greeting {
          margin: 15px 0;
          font-size: 11px;
        }
        
        .description {
          margin: 15px 0;
          text-align: justify;
          font-size: 11px;
        }
        
        table {
          width: 100%;
          border-collapse: collapse;
          margin: 15px 0;
        }
        
        th {
          border: 1px solid black;
          padding: 4px;
          background-color: #f5f5f5;
          font-weight: bold;
          text-align: center;
          font-size: 10px;
        }
        
        .signature-section {
          margin-top: 30px;
          display: flex;
          justify-content: space-between;
        }
        
        .signature-box {
          width: 45%;
          font-size: 11px;
        }
        
        .signature-line {
          border-bottom: 1px solid black;
          margin-top: 50px;
          text-align: center;
          padding-top: 3px;
          font-size: 11px;
        }
      </style>
    </head>
    <body>
      <div class="header">
        <h1>${formData.value.barangayName}</h1>
        <div>${formData.value.barangayLocation}</div>
      </div>
      
      <div class="company-info">
        <h2>${formData.value.companyName.toUpperCase()}</h2>
        <div>${formData.value.companyAddress}</div>
      </div>
      
      <div class="greeting">
        <strong>Sir/Madam:</strong>
      </div>
      
      <div class="description">
        ${formData.value.requestDescription}
      </div>
      
      <table>
        <thead>
          <tr>
            <th style="width: 35px;">ITEM</th>
            <th style="width: 45px;">QTY</th>
            <th style="width: 50px;">UNIT</th>
            <th style="width: 280px;">DESCRIPTION OF ARTICLES</th>
            <th style="width: 80px;">UNIT PRICE</th>
            <th style="width: 80px;">AMOUNT</th>
          </tr>
        </thead>
        <tbody>
          ${itemRows}
          ${emptyRows}
          <tr>
            <td colspan="5" style="border: 1px solid black; padding: 4px; text-align: right; font-weight: bold; font-size: 11px;">Total</td>
            <td style="border: 1px solid black; padding: 4px; text-align: right; font-size: 11px;">${totalAmount.toFixed(2)}</td>
          </tr>
        </tbody>
      </table>
      
      <div class="signature-section">
        <div class="signature-box">
          <div><strong>Attested by:</strong></div>
          <div class="signature-line">
            ${formData.value.requestingOfficial}
          </div>
          <div style="text-align: center; margin-top: 3px;">
            ${formData.value.officialTitle}
          </div>
        </div>
        
        <div class="signature-box">
          <div><strong>&nbsp;</strong></div>
          <div class="signature-line">
           <strong>Actual Canvass</strong>
          </div>
          <div style="text-align: center; margin-top: 3px;">
            Signature of Proprietor
          </div>
        </div>
      </div>
    </body>
    </html>
  `;
};

const handleClose = () => {
  emit('close');
};
</script>

<template>
  <Dialog :open="show" @update:open="handleClose">
    <DialogContent class="sm:max-w-[900px] h-[90vh] flex flex-col">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <FileText class="w-5 h-5" />
          Quotation Request Generator
        </DialogTitle>
        <DialogDescription>
          Generate printable quotation request forms to send to companies
        </DialogDescription>
      </DialogHeader>

      <ScrollArea class="flex-1 px-1">
        <div class="space-y-6">
          <!-- Company Information -->
          <Card>
            <CardHeader>
              <CardTitle class="text-lg">Company Information</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label>Company Name <span class="text-destructive">*</span></Label>
                  <div class="relative">
                    <Input 
                      v-model="formData.companyName" 
                      placeholder="Type company name..."
                      @input="searchCompanies($event.target.value)"
                    />
                    <!-- Company suggestions dropdown with address -->
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
                        <!-- Company Name -->
                        <div class="font-medium text-foreground mb-1">{{ company.company_name }}</div>
                        
                        <!-- Contact Details -->
                        <div class="text-sm text-muted-foreground mb-1">
                          <span v-if="company.contact_person">{{ company.contact_person }}</span>
                          <span v-if="company.contact_number" class="ml-1">• {{ company.contact_number }}</span>
                          <span v-if="company.email" class="ml-1">• {{ company.email }}</span>
                        </div>
                        
                        <!-- Address -->
                        <div v-if="company.address" class="text-xs text-muted-foreground italic">
                          📍 {{ company.address }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="space-y-2">
                  <Label>Contact Person</Label>
                  <Input 
                    v-model="formData.contactPerson" 
                    placeholder="Enter contact person"
                  />
                </div>
              </div>
              <div class="space-y-2">
                <Label>Address</Label>
                <Input 
                  v-model="formData.companyAddress" 
                  placeholder="Enter company address"
                />
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label>Contact Number</Label>
                  <Input 
                    v-model="formData.contactNumber" 
                    placeholder="Enter contact number"
                  />
                </div>
                <div class="space-y-2">
                  <Label>Email</Label>
                  <Input 
                    v-model="formData.email" 
                    type="email"
                    placeholder="Enter email address"
                  />
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Request Description -->
          <Card>
            <CardHeader>
              <CardTitle class="text-lg">Request Description</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-2">
                <Label>Description</Label>
                <Textarea 
                  v-model="formData.requestDescription" 
                  rows="3"
                />
              </div>
            </CardContent>
          </Card>

          <!-- Items -->
          <Card>
            <CardHeader class="flex flex-row items-center justify-between">
              <CardTitle class="text-lg">Items</CardTitle>
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
              <div 
                v-for="(item, index) in formData.items" 
                :key="index"
                class="space-y-3"
              >
                <!-- Description, Quantity, Unit, Unit Price Row -->
                <div class="grid grid-cols-[2fr,1fr,1fr,1fr,auto] gap-4 items-end">
                  <div class="space-y-2">
                    <Label>Description of Articles</Label>
                    <Input 
                      v-model="item.description"
                      placeholder="Enter item description"
                    />
                  </div>
                  <div class="space-y-2">
                    <Label>Quantity</Label>
                    <Input 
                      v-model="item.quantity"
                      type="number"
                      min="0"
                      placeholder=""
                      class="text-center"
                    />
                  </div>
                  <div class="space-y-2">
                    <Label>Unit</Label>
                    <div class="relative">
                      <Input 
                        v-model="item.unit"
                        placeholder="Type or select unit"
                        :list="`units-${index}`"
                      />
                      <datalist :id="`units-${index}`">
                        <option 
                          v-for="unit in getFilteredUnits(item.unit)" 
                          :key="unit" 
                          :value="unit"
                        />
                      </datalist>
                    </div>
                  </div>
                  <div class="space-y-2">
                    <Label>Unit Price</Label>
                    <Input 
                      v-model="item.unitPrice"
                      type="number"
                      step="0.01"
                      min="0"
                      placeholder=""
                      class="text-center"
                    />
                  </div>
                  <Button 
                    type="button" 
                    variant="ghost" 
                    size="icon"
                    @click="removeItem(index)"
                    :disabled="formData.items.length === 1"
                  >
                    <Trash2 class="w-4 h-4" />
                  </Button>
                </div>
                
                <!-- Separator line between items -->
                <div v-if="index < formData.items.length - 1" class="border-b"></div>
              </div>
            </CardContent>
          </Card>
        </div>
      </ScrollArea>

      <div class="flex justify-between gap-2 pt-4 border-t">
        <Button 
          type="button" 
          variant="outline"
          @click="handleClose"
        >
          Cancel
        </Button>
        
        <div class="flex gap-2">
          <!-- Save Companies button -->
          <Button 
            v-if="requestId"
            type="button"
            variant="secondary"
            @click="saveCompaniesToDatabase"
            class="gap-2"
            :disabled="saveForm.processing"
          >
            <Building class="w-4 h-4" />
            Save Company Info
          </Button>
          
          <Button 
            type="button"
            variant="outline"
            @click="previewPDF"
            class="gap-2"
          >
            <Eye class="w-4 h-4" />
            Preview
          </Button>
          
          <Button 
            type="button"
            @click="generatePDF"
            class="gap-2"
          >
            <Download class="w-4 h-4" />
            Generate PDF
          </Button>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>