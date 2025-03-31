<template>
    <div class="flex min-h-screen flex-col items-center bg-gradient-to-br from-blue-50 to-blue-100 p-6 text-gray-800 dark:from-gray-900 dark:to-gray-800 dark:text-gray-100 lg:justify-center lg:p-8">
        <Head title="Log in" />

        <main class="flex w-full max-w-[400px] overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-800 lg:max-w-5xl lg:flex-row">
            <div class="relative hidden lg:w-1/2 lg:block">
                <div class="absolute inset-0">
                    <img 
                        :src="loginImage"  
                        alt="Background" 
                        class="h-full w-full object-cover transition-transform duration-300 hover:scale-105" 
                    />
                </div>
            </div>
            <div class="flex-1 p-6 lg:p-12">
                <div class="mx-auto w-full max-w-md space-y-8">
                    <div class="space-y-4">
                        <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                            Login to your account
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Enter your credentials to access the system
                        </p>
                    </div>

                    <div v-if="status" class="rounded-lg bg-green-50 p-4 text-sm font-medium text-green-600 dark:bg-green-900/50 dark:text-green-400">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <Label for="email" class="text-sm font-medium">Email address</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="email"
                                    v-model="form.email"
                                    placeholder="email@example.com"
                                    class="w-full h-11 px-4 transition-shadow focus:ring-2"
                                />
                                <InputError :message="form.errors.email" />
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <Label for="password" class="text-sm font-medium">Password</Label>
                                    <TextLink 
                                        v-if="canResetPassword" 
                                        :href="route('password.request')" 
                                        class="text-sm hover:text-blue-500 transition-colors no-underline" 
                                        :tabindex="5"
                                    >
                                        Forgot password?
                                    </TextLink>
                                </div>
                                <Input
                                    id="password"
                                    type="password"
                                    required
                                    :tabindex="2"
                                    autocomplete="current-password"
                                    v-model="form.password"
                                    placeholder="Enter your password"
                                    class="w-full h-11 px-4 transition-shadow focus:ring-2"
                                />
                                <InputError :message="form.errors.password" />
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <Checkbox 
                                id="remember" 
                                v-model:checked="form.remember" 
                                :tabindex="3"
                                class="border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800"
                            />
                            <Label for="remember" class="text-sm">Remember me</Label>
                        </div>

                        <Button 
                            type="submit" 
                            class="w-full h-11 text-base font-semibold shadow-lg hover:shadow-blue-500/20 transition-all duration-300" 
                            :tabindex="4" 
                            :disabled="form.processing"
                        >
                            <LoaderCircle v-if="form.processing" class="mr-2 h-5 w-5 animate-spin" />
                            Sign in to your account
                        </Button>

                        <div class="text-center text-sm text-gray-600 dark:text-gray-300">
                            Don't have an account?
                            <TextLink 
                                :href="route('register')" 
                                :tabindex="5" 
                                class="font-medium text-blue-600 hover:text-blue-500 dark:hover:text-blue-600  dark:text-blue-400 ml-1 no-underline"
                            >
                                Sign up
                            </TextLink>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LoaderCircle } from 'lucide-vue-next';
import loginImage from '@/./../images/login.svg';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>