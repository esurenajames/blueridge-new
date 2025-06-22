<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Upload, X, FileText, ZoomIn } from 'lucide-vue-next';
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

const showPreviewDialog = ref(false);
const selectedPreview = ref<{ url: string; type: string } | null>(null);

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

const previewFile = (file: File) => {
  if (!file) return;
  const url = URL.createObjectURL(file);
  selectedPreview.value = { url, type: file.type };
  showPreviewDialog.value = true;
};

const closePreview = () => {
  if (selectedPreview.value?.url) {
    URL.revokeObjectURL(selectedPreview.value.url);
  }
  selectedPreview.value = null;
  showPreviewDialog.value = false;
};
</script>

<template>
  <div class="space-y-4">
    <!-- Upload Section -->
    <div class="space-y-2">      
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
          class="flex items-center gap-2 p-3 bg-muted rounded-lg hover:bg-muted/80 transition-colors group cursor-pointer"
        >
          <FileText class="size-4 text-primary" />
          <div class="flex-1" @click.stop.prevent="previewFile(file)">
            <p class="text-sm font-medium">{{ file.name }}</p>
            <p class="text-xs text-muted-foreground">{{ formatFileSize(file.size) }}</p>
          </div>
          <div class="flex  items-center">
            <Button
              type="button"
              variant="ghost"
              size="icon"
              class="h-8 w-8 rounded-full opacity-0 group-hover:opacity-100"
              @click.stop.prevent="previewFile(file)"
            >
              <ZoomIn class="size-4" />
            </Button>
            <Button
              type="button"
              variant="ghost"
              size="icon"
              class="h-8 w-8 rounded-full hover:bg-destructive/10 hover:text-destructive opacity-0 group-hover:opacity-100"
              @click.stop="removeFile(index)"
            >
              <X class="size-4" />
            </Button>
          </div>
        </div>
      </div>

      <!-- Existing Files Section -->
      <div v-if="existingFiles?.length" class="space-y-2">
        <Label>Current Files</Label>
        <div class="space-y-2">
          <div v-for="file in existingFiles" 
            :key="file.name"
            class="flex items-center gap-2 p-3 bg-muted rounded-lg hover:bg-muted/80 transition-colors group cursor-pointer"
          >
            <FileText class="size-4 text-primary" />
            <div class="flex-1">
              <p class="text-sm font-medium">{{ file.name }}</p>
              <p class="text-xs text-muted-foreground">{{ formatFileSize(file.size) }}</p>
            </div>
            <Button
              variant="ghost"
              size="icon"
              class="h-8 w-8 rounded-full hover:bg-destructive/10 hover:text-destructive opacity-0 group-hover:opacity-100"
              @click.stop="removeExistingFile(file.name)"
            >
              <X class="size-4" />
            </Button>
          </div>
        </div>
      </div>
    </div>

    <span v-if="errors?.length" class="text-sm text-destructive">
      {{ errors[0] }}
    </span>

    <!-- Preview Dialog -->
    <Dialog v-model:open="showPreviewDialog" @update:open="closePreview">
      <DialogContent class="sm:max-w-[900px] sm:max-h-[80vh]">
        <div class="relative w-full h-full max-h-[60vh] overflow-auto">
          <img 
            v-if="selectedPreview?.type.startsWith('image')"
            :src="selectedPreview?.url" 
            class="w-full h-auto"
            alt="File preview" 
          />
          <iframe
            v-else-if="selectedPreview?.type === 'application/pdf'"
            :src="selectedPreview?.url"
            class="w-full h-full min-h-[500px]"
            type="application/pdf"
          ></iframe>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>