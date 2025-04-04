<script setup lang="ts">
import { ref } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useToast } from '@/components/ui/toast/use-toast';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';
import { useField, useForm as useVeeForm } from 'vee-validate';
import { object, string } from 'yup';
import { KeyRound } from 'lucide-vue-next';
import { Clipboard } from 'lucide-vue-next'; 

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits(['close']);

const roles = [
    { value: 'captain', label: 'Captain' },
    { value: 'admin', label: 'Admin' },
    { value: 'official', label: 'Official' },
    { value: 'secretary', label: 'Secretary' },
    { value: 'treasurer', label: 'Treasurer' },
];

const validationSchema = object({
    name: string().required('Name is required'),
    email: string().required('Email is required').email('Must be a valid email'),
    role: string().required('Role is required'),
    password: string().required('Password is required').min(8, 'Password must be at least 8 characters'),
});

const { handleSubmit: validateForm } = useVeeForm({
    validationSchema,
});

const { value: name, errorMessage: nameError } = useField('name');
const { value: email, errorMessage: emailError } = useField('email');
const { value: role, errorMessage: roleError } = useField('role');
const { value: password, errorMessage: passwordError } = useField('password');

const form = useForm({
    name: '',
    email: '',
    role: 'official',
    password: '',
    status: 'active'
});

const { toast } = useToast();

const generatePassword = () => {
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let generatedPassword = '';
    for (let i = 0; i < 12; i++) {
        const randomIndex = Math.floor(Math.random() * charset.length);
        generatedPassword += charset[randomIndex];
    }
    password.value = generatedPassword;
    form.password = generatedPassword;
};

const copyToClipboard = () => {
    navigator.clipboard.writeText(password.value);
    toast({
        title: "Success",
        description: "Password copied to clipboard",
        variant: "success",
    });
};

const handleSubmit = validateForm((values) => {
    form.name = name.value;
    form.email = email.value;
    form.role = role.value;
    form.password = password.value;
    
    form.post(route('admin.users.create'), {
        preserveScroll: true,
        onSuccess: () => {
            toast({
                title: "Success",
                description: "User created successfully",
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
                    description: "Failed to create user. Please try again.",
                    variant: "destructive",
                });
            }
        },
    });
});
</script>

<template>
    <Dialog :open="show" @update:open="emit('close')">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Create User</DialogTitle>
                <DialogDescription>
                    Add a new user by filling in the details below.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="space-y-2">
                    <label for="name" class="text-sm font-medium">Name</label>
                    <Input 
                    id="name"
                    v-model="name"
                    :class="nameError ? 'border-red-500 focus-visible:ring-red-500' : ''"
                    />
                    <span v-if="nameError" class="text-sm text-red-500">{{ nameError }}</span>
                </div>
                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium">Email</label>
                    <Input 
                        id="email"
                        type="email"
                        v-model="email"
                        :class="emailError ? 'border-red-500 focus-visible:ring-red-500' : ''"
                    />
                    <span v-if="emailError" class="text-sm text-red-500">{{ emailError }}</span>
                </div>
                <div class="space-y-2">
                    <label for="role" class="text-sm font-medium">Role</label>
                    <Select v-model="role">
                        <SelectTrigger :class="roleError ? 'border-red-500 focus:ring-red-500' : ''">
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
                    <span v-if="roleError" class="text-sm text-red-500">{{ roleError }}</span>
                </div>
                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium">Password</label>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <Input 
                                id="password"
                                type="text"
                                v-model="password"
                                :class="passwordError ? 'border-red-500 focus-visible:ring-red-500 pr-10' : 'pr-10'"
                                readonly
                            />
                            <button
                                type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 hover:text-gray-600"
                                @click="copyToClipboard"
                                :disabled="!password"
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
                    <span v-if="passwordError" class="text-sm text-red-500">{{ passwordError }}</span>
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
                        {{ form.processing ? 'Creating...' : 'Create User' }}
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>