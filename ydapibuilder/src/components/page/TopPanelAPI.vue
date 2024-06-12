<template>
  <TopPanelBase activeMenu="apibox"></TopPanelBase>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import TopPanelBase from '@/components/page/TopPanelBase.vue'

export default {
  name: 'TopPanel',
  components: { TopPanelBase },
  setup (props: any, ctx: any) {
    const { t } = useI18n()

    return {
      t
    }
  }
}
</script>
