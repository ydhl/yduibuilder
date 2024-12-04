<template>
  <template v-if="!icon"><slot></slot></template>
  <template v-else-if="iconPosition=='top'">
    <div><i :class='icon'></i></div>
    <slot></slot>
  </template>
  <template v-else-if="iconPosition=='bottom'">
    <slot></slot>
    <div><i :class='icon'></i></div>
  </template>
  <template v-else-if="iconPosition=='right'">
    <slot></slot>
    <i :class='icon'></i>
  </template>
  <template v-else>
    <i :class='icon'></i>
    <slot></slot>
  </template>
</template>
<script lang="ts" setup>
  const { icon, iconPosition } = defineProps({
    icon: String,
    iconPosition: String
  })
</script>
  