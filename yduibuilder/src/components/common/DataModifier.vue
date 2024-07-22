<template>
  <template v-if="modifierPosition == 'left'">
    {{modiferValue}}{{dataName}}
  </template>
  <template v-else-if="modifierPosition == 'right'">
    {{dataName}}{{modiferValue}}
  </template>
  <template v-else-if="modifierPosition=='around'">
    {{modiferValue}}({{dataName}})
  </template>
  <template v-else>
    {{dataName}}
  </template>
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
      } else if (props.modifier?.match(/^.+@/)) {
        return 'left'
      } else if (props.modifier?.match(/^@.+/)) {
        return 'right'
      } else {
        return 'around'
      }
    })
    const modiferValue = computed(() => {
      if (!props.modifier) {
        return ''
      } else if (props.modifier?.match(/^.+@/)) {
        return props.modifier?.replace(/@/, '')
      } else if (props.modifier?.match(/^@.+/)) {
        return props.modifier?.replace(/@/, '')
      } else {
        return props.modifier?.replace(/^\(.+\)/, '')
      }
    })
    return {
      modifierPosition,
      modiferValue
    }
  }
}
</script>
