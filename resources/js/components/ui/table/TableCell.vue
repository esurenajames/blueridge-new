<script setup lang="ts">
defineOptions({
  name: 'TableCell'
});
</script>

<template>
  <td class="p-4 py-2 align-middle [&:has([role=checkbox])]:pr-0">
    <slot />
  </td>
</template>