<script setup lang="ts">
import { computed } from 'vue';
import { Pagination, PaginationList, PaginationListItem, PaginationFirst, PaginationLast, PaginationNext, PaginationPrev } from '@/components/ui/pagination';

interface Props {
  currentPage: number;
  totalPages: number;
  onPageChange: (page: number) => void;
}

const props = defineProps<Props>();

const isFirstPage = computed(() => props.currentPage === 1);
const isLastPage = computed(() => props.currentPage === props.totalPages);
</script>

<template>
  <div class="flex justify-center p-2">
    <Pagination>
      <PaginationList class="flex items-center space-x-2">
        <PaginationFirst 
          @click="onPageChange(1)" 
          :disabled="isFirstPage" 
          class="px-2" 
        />
        <PaginationPrev 
          @click="onPageChange(currentPage - 1)" 
          :disabled="isFirstPage" 
          class="px-2" 
        />
        <div class="flex items-center space-x-2">
          <template v-for="page in totalPages" :key="page">
            <PaginationListItem
              :value="String(page)"
              :disabled="false"
              @click="onPageChange(page)"
              :active="currentPage === page"
              class="h-9 w-9 rounded-md text-sm transition-colors hover:bg-muted"
              :class="{
                'bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground': currentPage === page,
                'hover:bg-transparent hover:text-primary': currentPage !== page
              }"
            >
              {{ page }}
            </PaginationListItem>
          </template>
        </div>
        <PaginationNext 
          @click="onPageChange(currentPage + 1)" 
          :disabled="isLastPage" 
          class="px-2" 
        />
        <PaginationLast 
          @click="onPageChange(totalPages)" 
          :disabled="isLastPage" 
          class="px-2" 
        />
      </PaginationList>
    </Pagination>
  </div>
</template>