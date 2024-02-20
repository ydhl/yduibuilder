<template>
  <div></div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'

export default {
  name: 'StyleUIComponent',
  setup (props: any, context: any) {
    const { t } = useI18n()
    return {
      t
    }
  }
}
</script>
