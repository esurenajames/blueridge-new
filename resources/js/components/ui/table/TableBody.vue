<script setup lang="ts">
defineOptions({
  name: 'TableBody'
});
</script>

<template>
  <tbody class="[&_tr:last-child]:border-0">
    <slot />
  </tbody>
</template>