<script setup lang="ts">
defineOptions({
  name: 'TableHeader'
});
</script>

<template>
  <thead class="[&_tr]:border-b bg-gray-50 dark:bg-gray-700/50">
    <slot />
  </thead>
</template>