<script setup lang="ts">
defineOptions({
  name: 'TableHead'
});
</script>

<template>
  <th
    class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0"
  >
    <slot />
  </th>
</template>