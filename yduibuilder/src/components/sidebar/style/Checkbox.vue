<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.checkbox') }}</div>
  <div class="style-body d-none">
    <StyleFormValueList></StyleFormValueList>
  </div>
</template>

<script lang="ts">
import StyleFormValueList from '@/components/sidebar/style/ValueList.vue'
import { useI18n } from 'vue-i18n'

export default {
  name: 'StyleCheckbox',
  components: { StyleFormValueList },
  setup (props: any, context: any) {
    const { t } = useI18n()
    return {
      t
    }
  }
}
</script>
