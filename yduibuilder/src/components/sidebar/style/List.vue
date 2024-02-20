<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.list') }}</div>
  <div class="style-body d-none">
    <StyleFormValueList></StyleFormValueList>
  </div>
</template>

<script lang="ts">
import StyleFormValueList from '@/components/sidebar/style/ValueList.vue'
import { useI18n } from 'vue-i18n'
import initUI from '@/components/Common'

export default {
  name: 'StyleList',
  components: { StyleFormValueList },
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
