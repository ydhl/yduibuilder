<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.checkbox') }}</div>
  <div class="style-body d-none">
    <StyleFormValueList></StyleFormValueList>
  </div>
</template>

<script lang="ts">
import StyleFormValueList from '@/components/sidebar/style/ValueList.vue'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'

export default {
  name: 'StyleCheckbox',
  components: { StyleFormValueList },
  setup (props: any, context: any) {
    const store = useStore()
    const { t } = useI18n()
    const isMobile = computed(() => store.state.design.endKind === 'mobile')
    return {
      t,
      isMobile
    }
  }
}
</script>
