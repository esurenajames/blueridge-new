<script setup lang="ts">
import { ref } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useToast } from '@/components/ui/toast/use-toast';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';
import { KeyRound } from 'lucide-vue-next';

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

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    role: 'official',
    password: '',
    status: 'active'
});

const { toast } = useToast();

const generatePassword = () => {
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let password = '';
    for (let i = 0; i < 12; i++) {
        const randomIndex = Math.floor(Math.random() * charset.length);
        password += charset[randomIndex];
    }
    form.password = password;
};

const handleSubmit = () => {
    form.post(route('admin.users.store'), {
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
};
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
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="first_name" class="text-sm font-medium">First Name</label>
                        <Input 
                            id="first_name"
                            v-model="form.first_name"
                            :error="form.errors.first_name"
                        />
                    </div>
                    <div class="space-y-2">
                        <label for="last_name" class="text-sm font-medium">Last Name</label>
                        <Input 
                            id="last_name"
                            v-model="form.last_name"
                            :error="form.errors.last_name"
                        />
                    </div>
                </div>
                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium">Email</label>
                    <Input 
                        id="email"
                        type="email"
                        v-model="form.email"
                        :error="form.errors.email"
                    />
                </div>
                <div class="space-y-2">
                    <label for="role" class="text-sm font-medium">Role</label>
                    <Select v-model="form.role">
                        <SelectTrigger>
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
                </div>
                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium">Password</label>
                    <div class="flex gap-2">
                        <Input 
                            id="password"
                            type="text"
                            v-model="form.password"
                            :error="form.errors.password"
                            readonly
                        />
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
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Creating...' : 'Create User' }}
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>