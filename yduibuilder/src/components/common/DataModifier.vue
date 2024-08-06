<template>
  {{ modiferValue }}
</template>

<script lang="ts">
import { computed } from 'vue'

export default {
  name: 'DataModifier',
  props: {
    dataName: {
      default: '',
      type: String
    },
    modifier: String
  },
  setup (props: any, context: any) {
    const modifierPosition = computed<'right'|'left'|'around'|'none'>(() => {
      if (!props.modifier) {
        return 'none'
      } else if (props.modifier?.match(/^.+@$/)) {
        return 'left'
      } else if (props.modifier?.match(/^@.+/)) {
        return 'right'
      } else {
        return 'around'
      }
    })
    const modiferValue = computed(() => {
      if (props.modifier?.match(/@/)) {
        return props.modifier?.replace(/@/, props.dataName)
      } else {
        return props.dataName
      }
    })
    return {
      modifierPosition,
      modiferValue
    }
  }
}
</script>
