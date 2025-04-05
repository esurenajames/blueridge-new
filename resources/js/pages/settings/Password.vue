<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from '@/components/ui/toast/use-toast';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import { useField, useForm as useVeeForm } from 'vee-validate';
import { object, string, ref as yupRef } from 'yup';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Password settings',
        href: '/settings/password',
    },
];

const validationSchema = object({
    current_password: string().required('Current password is required'),
    password: string()
        .required('New password is required')
        .min(8, 'Password must be at least 8 characters'),
    password_confirmation: string()
        .required('Please confirm your password')
        .oneOf([yupRef('password')], 'Passwords must match'),
});

const { handleSubmit: validateForm } = useVeeForm({
    validationSchema,
});

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const { value: current_password, errorMessage: currentPasswordError } = useField('current_password');
const { value: password, errorMessage: passwordError } = useField('password');
const { value: password_confirmation, errorMessage: passwordConfirmationError } = 
    useField('password_confirmation');

const isSubmitting = ref(false);

const updatePassword = validateForm((values) => {
    isSubmitting.value = true;

    // Update form values with validated data
    form.current_password = values.current_password;
    form.password = values.password;
    form.password_confirmation = values.password_confirmation;

    // Submit the form using Inertia
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            toast({
                title: 'Success',
                description: 'Your password has been updated.',
                variant: 'default',
            });
            // Reset both form and validation values
            form.reset();
            current_password.value = '';
            password.value = '';
            password_confirmation.value = '';
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'There was a problem updating your password.',
                variant: 'destructive',
            });
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Password settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall 
                    title="Update password" 
                    description="Ensure your account is using a long, random password to stay secure" 
                />

                <form @submit.prevent="updatePassword" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="current_password">Current password</Label>
                        <Input
                            id="current_password"
                            v-model="current_password"
                            type="password"
                            class="mt-1 block w-full"
                            :class="currentPasswordError ? 'border-red-500 focus-visible:ring-red-500' : ''"
                            autocomplete="current-password"
                            placeholder="Current password"
                            :disabled="isSubmitting"
                        />
                        <span v-if="currentPasswordError" class="text-sm text-red-500">
                            {{ currentPasswordError }}
                        </span>
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">New password</Label>
                        <Input
                            id="password"
                            v-model="password"
                            type="password"
                            class="mt-1 block w-full"
                            :class="passwordError ? 'border-red-500 focus-visible:ring-red-500' : ''"
                            autocomplete="new-password"
                            placeholder="New password"
                            :disabled="isSubmitting"
                        />
                        <span v-if="passwordError" class="text-sm text-red-500">
                            {{ passwordError }}
                        </span>
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Confirm password</Label>
                        <Input
                            id="password_confirmation"
                            v-model="password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            :class="passwordConfirmationError ? 'border-red-500 focus-visible:ring-red-500' : ''"
                            autocomplete="new-password"
                            placeholder="Confirm password"
                            :disabled="isSubmitting"
                        />
                        <span v-if="passwordConfirmationError" class="text-sm text-red-500">
                            {{ passwordConfirmationError }}
                        </span>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="isSubmitting">
                            <template v-if="isSubmitting">
                                <svg
                                    class="mr-2 h-4 w-4 animate-spin"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Saving...
                            </template>
                            <template v-else>
                                Save password
                            </template>
                        </Button>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>