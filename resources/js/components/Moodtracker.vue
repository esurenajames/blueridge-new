<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Smile, Meh, Frown, SmilePlus, Quote } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';

const props = defineProps<{
    userId: number;
}>();

const getStorageKey = (userId: number) => `user_mood_${userId}`;

const moods = [
    { 
        icon: SmilePlus, 
        label: 'Great', 
        color: 'text-green-500 hover:text-green-600',
        ringColor: 'ring-green-500'
    },
    { 
        icon: Smile, 
        label: 'Good', 
        color: 'text-blue-500 hover:text-blue-600',
        ringColor: 'ring-blue-500'
    },
    { 
        icon: Meh, 
        label: 'Okay', 
        color: 'text-amber-500 hover:text-amber-600',
        ringColor: 'ring-amber-500'
    },
    { 
        icon: Frown, 
        label: 'Not Good', 
        color: 'text-red-500 hover:text-red-600',
        ringColor: 'ring-red-500'
    },
];

const quotes = {
    'Great': [
        "Keep spreading that positive energy!",
        "Your enthusiasm is contagious!",
        "What a great day to make a difference!",
        "You're absolutely rocking it today!",
        "Keep shining—your vibe is inspiring others!",
        "Happiness looks great on you!",
        "Your confidence is your superpower!",
        "You're a beam of sunshine wherever you go!",
        "You're making waves in the best way!",
        "The world needs more of your energy!",
    ],
    'Good': [
        "Every day is a chance to be even better!",
        "You're doing great, keep it up!",
        "Your positive attitude makes a difference!",
        "You’ve got a good rhythm going—keep riding that wave!",
        "Today is a good day for progress, and you're on track!",
        "You're doing better than you think.",
        "Celebrate the small wins—they lead to big victories!",
        "You're showing up, and that matters a lot.",
        "Keep your head high—you’re doing just fine.",
        "You’re steady, and that’s powerful.",
    ],
    'Okay': [
        "Remember, every cloud has a silver lining.",
        "Take a deep breath, you've got this!",
        "Small steps forward are still progress.",
        "It’s okay to take things slow—progress is still progress.",
        "You’re doing your best, and that’s enough.",
        "Sometimes neutral days are the best reset.",
        "A little self-care can go a long way.",
        "You're not stuck—you’re just getting ready for a leap.",
        "Being 'okay' is perfectly okay.",
        "Even quiet strength is still strength.",
    ],
    'Not Good': [
        "It's okay not to be okay. Tomorrow is a new day.",
        "You're stronger than you think.",
        "Reach out to someone - we're here for you.",
        "You've made it through tough days before, you can do it again.",
        "Be gentle with yourself today.",
        "Your feelings are valid—take your time to heal.",
        "This moment will pass, and brighter days are ahead.",
        "You are not alone—don't hesitate to lean on others.",
        "Pain is temporary. You’re growing through this.",
        "Even on hard days, you matter more than you know.",
    ],
};

const selectedMood = ref<string | null>(null);
const showQuoteDialog = ref(false);

onMounted(() => {
    const saved = localStorage.getItem(getStorageKey(props.userId));
    selectedMood.value = saved || null;
});

const saveMood = (mood: string) => {
    localStorage.setItem(getStorageKey(props.userId), mood);
};

const selectMood = (mood: string) => {
    selectedMood.value = mood;
    saveMood(mood);
    showQuoteDialog.value = true;
};

const clearMood = () => {
    selectedMood.value = null;
    localStorage.removeItem(getStorageKey(props.userId));
};

const currentQuote = computed(() => {
    if (!selectedMood.value) return '';
    const moodQuotes = quotes[selectedMood.value as keyof typeof quotes];
    return moodQuotes[Math.floor(Math.random() * moodQuotes.length)];
});

const closeDialog = () => {
    showQuoteDialog.value = false;
};
</script>

<template>
    <Card class="h-full overflow-hidden">
        <div class="space-y-2">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-100 dark:bg-yellow-900/20">
                        <Smile class="size-5 text-yellow-500" />
                    </div>
                    How are you feeling today?
                </CardTitle>
                <CardDescription>
                    Select your mood and receive a supportive message
                </CardDescription>
            </CardHeader>
            <CardContent class="flex flex-col gap-4">
                <div class="grid grid-cols-2 gap-4">
                    <Button
                        v-for="mood in moods"
                        :key="mood.label"
                        variant="outline"
                        class="flex flex-col items-center gap-2 p-4 h-auto transition-all"
                        :class="[
                            mood.color,
                            selectedMood === mood.label ? `ring-2 ${mood.ringColor}` : ''
                        ]"
                        @click="selectMood(mood.label)"
                    >
                        <component 
                            :is="mood.icon" 
                            class="h-8 w-8"
                        />
                        <span class="text-sm">{{ mood.label }}</span>
                    </Button>
                </div>
            </CardContent>
        </div>
    </Card>

    <Dialog :open="showQuoteDialog" @update:open="closeDialog">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <component 
                        v-if="selectedMood"
                        :is="moods.find(m => m.label === selectedMood)?.icon"
                        class="h-5 w-5"
                        :class="moods.find(m => m.label === selectedMood)?.color"
                    />
                    {{ selectedMood }}
                </DialogTitle>
                <DialogDescription>
                    Here's a message for you
                </DialogDescription>
            </DialogHeader>
            <div class="flex items-start gap-2 p-4 rounded-lg bg-gray-200 dark:bg-gray-800/50">
                <Quote class="h-4 w-4 flex-shrink-0 mt-1 text-gray-500 dark:text-gray-400" />
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ currentQuote }}</p>
            </div>
        </DialogContent>
    </Dialog>
</template>