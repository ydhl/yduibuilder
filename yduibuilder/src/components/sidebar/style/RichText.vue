<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.richText') }}</div>
  <div class="style-body d-none">
    {{t('style.empty')}}
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'

export default {
  name: 'StyleText',
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()

    return {
      ...info,
      t
    }
  }
}
</script>
