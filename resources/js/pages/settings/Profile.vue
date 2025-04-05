<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import { useField, useForm as useVeeForm } from 'vee-validate';
import { object, string } from 'yup';
import { toast } from '@/components/ui/toast/use-toast';
import { ref } from 'vue';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const validationSchema = object({
    name: string().required('Name is required'),
    email: string().required('Email is required').email('Email must be valid'),
});

const { handleSubmit: validateForm } = useVeeForm({
    validationSchema,
    initialValues: {
        name: user.name,
        email: user.email,
    },
});

const form = useForm({
    name: user.name,
    email: user.email,
});

const { value: name, errorMessage: nameError } = useField('name');
const { value: email, errorMessage: emailError } = useField('email');

const isSubmitting = ref(false);

const submit = validateForm((values) => {
    isSubmitting.value = true;

    form.name = values.name;
    form.email = values.email;

    form.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            toast({
                title: 'Success',
                description: 'Your profile has been updated.',
                variant: 'default',
            });
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'There was a problem updating your profile.',
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
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall 
                    title="Profile information" 
                    description="Update your name and email address" 
                />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input 
                            id="name" 
                            v-model="name"
                            class="mt-1 block w-full"
                            :class="nameError ? 'border-red-500 focus-visible:ring-red-500' : ''"
                            required 
                            autocomplete="name" 
                            placeholder="Full name"
                            :disabled="isSubmitting"
                        />
                        <span v-if="nameError" class="text-sm text-red-500">
                            {{ nameError }}
                        </span>
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            v-model="email"
                            class="mt-1 block w-full"
                            :class="emailError ? 'border-red-500 focus-visible:ring-red-500' : ''"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                            :disabled="isSubmitting"
                        />
                        <span v-if="emailError" class="text-sm text-red-500">
                            {{ emailError }}
                        </span>
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:!decoration-current dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </div>
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
                                Save
                            </template>
                        </Button>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>