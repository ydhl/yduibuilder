<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.container') }}</div>
  <div class="style-body d-none">
    {{t('style.noSetting')}}
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'

export default {
  name: 'StyleContainer',
  setup (props: any, context: any) {
    const { t } = useI18n()
    return {
      t
    }
  }
}
</script>
