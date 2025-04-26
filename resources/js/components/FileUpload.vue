<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Upload, X, FileText } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
  modelValue: File[];
  existingFiles?: Array<{
    name: string;
    size: number;
  }>;
  removedFiles?: string[];
  errors?: string[];
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: File[]): void;
  (e: 'update:existingFiles', value: Array<{ name: string; size: number }>): void;
  (e: 'update:removedFiles', value: string[]): void;
}>();

const fileInput = ref<HTMLInputElement | null>(null);

const handleFileSelect = (event: Event) => {
  const input = event.target as HTMLInputElement;
  if (input.files?.length) {
    emit('update:modelValue', [...props.modelValue, ...Array.from(input.files)]);
  }
};

const handleDrop = (event: DragEvent) => {
  const droppedFiles = event.dataTransfer?.files;
  if (droppedFiles?.length) {
    emit('update:modelValue', [...props.modelValue, ...Array.from(droppedFiles)]);
  }
};

const removeFile = (index: number) => {
  const updatedFiles = [...props.modelValue];
  updatedFiles.splice(index, 1);
  emit('update:modelValue', updatedFiles);
};

const removeExistingFile = (fileName: string) => {
  if (props.existingFiles) {
    const updatedFiles = props.existingFiles.filter(file => file.name !== fileName);
    emit('update:existingFiles', updatedFiles);
    emit('update:removedFiles', [...(props.removedFiles || []), fileName]);
  }
};

const formatFileSize = (bytes: number) => {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};
</script>

<template>
  <div class="space-y-4">
    <!-- Existing Files Section -->
  

    <!-- Upload Section -->
    <div class="space-y-2">
      <Label>Add Files
        <span class="text-xs ml-1 bg-muted px-2 py-1 rounded-md text-muted-foreground">
          Optional
        </span>
      </Label>
      
      <div
        class="border-2 border-dashed rounded-lg p-8 text-center hover:border-primary transition-colors"
        @dragover.prevent
        @drop.prevent="handleDrop"
      >
        <input
          ref="fileInput"
          type="file"
          multiple
          class="hidden"
          @change="handleFileSelect"
        />
        <Upload class="size-8 mx-auto text-muted-foreground mb-4" />
        <div class="space-y-2">
          <Button 
            type="button" 
            variant="link" 
            @click="fileInput?.click()"
          >
            Upload files
          </Button>
          <p class="text-sm text-muted-foreground">
            or drag and drop your files here
          </p>
        </div>
      </div>

      <!-- New Files List -->
      <div v-if="modelValue.length > 0" class="space-y-2">
        <div v-for="(file, index) in modelValue" 
          :key="index"
          class="flex items-center gap-2 p-3 bg-muted rounded-lg hover:bg-muted/80 transition-colors"
        >
          <FileText class="size-4 text-primary" />
          <div class="flex-1">
            <p class="text-sm font-medium">{{ file.name }}</p>
            <p class="text-xs text-muted-foreground">{{ formatFileSize(file.size) }}</p>
          </div>
          <Button
            variant="ghost"
            size="icon"
            class="h-8 w-8 rounded-full hover:bg-destructive/10 hover:text-destructive"
            @click="removeFile(index)"
          >
            <X class="size-4" />
          </Button>
        </div>
      </div>
    </div>

      <div v-if="existingFiles?.length" class="space-y-2">
      <Label>Current Files</Label>
      <div class="space-y-2">
        <div v-for="file in existingFiles" 
          :key="file.name"
          class="flex items-center gap-2 p-3 bg-muted rounded-lg hover:bg-muted/80 transition-colors"
        >
          <FileText class="size-4 text-primary" />
          <div class="flex-1">
            <p class="text-sm font-medium">{{ file.name }}</p>
            <p class="text-xs text-muted-foreground">{{ formatFileSize(file.size) }}</p>
          </div>
          <Button
            variant="ghost"
            size="icon"
            class="h-8 w-8 rounded-full hover:bg-destructive/10 hover:text-destructive"
            @click="removeExistingFile(file.name)"
          >
            <X class="size-4" />
          </Button>
        </div>
      </div>
    </div>
    <span v-if="errors?.length" class="text-sm text-destructive">
      {{ errors[0] }}
    </span>
  </div>
</template>