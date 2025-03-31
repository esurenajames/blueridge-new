<script setup lang="ts">
defineOptions({
  name: 'TableRow'
});
</script>

<template>
  <tr
    class="border-b transition-colors data-[state=selected]:bg-muted"
  >
    <slot />
  </tr>
</template>