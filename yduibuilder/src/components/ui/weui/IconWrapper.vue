<template>
  <template v-if="!icon"><slot></slot></template>
  <template v-else>
    <template v-if="position==='top'">
      <div><i :class="icon"></i></div>
      <slot></slot>
    </template>
    <template v-else-if="position==='bottom'">
      <slot></slot>
      <div><i :class="icon"></i></div>
    </template>
    <template v-else-if="position==='right'">
      <slot></slot><i :class="icon"></i></template>
    <template v-else>
      <i :class="icon"></i><slot></slot>
    </template>
  </template>
</template>

<script lang="ts">
import { computed } from 'vue'

export default {
  name: 'IconWrapper',
  props: {
    uiconfig: Object
  },
  setup (props: any, context: any) {
    const icon = computed(() => props.uiconfig.meta?.custom?.icon)
    const position = computed(() => props.uiconfig.meta?.custom?.['icon-position'])
    return {
      icon,
      position
    }
  }
}
</script>
