<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.radio') }}</div>
  <div class="style-body d-none">
    <StyleFormValueList></StyleFormValueList>
  </div>
</template>

<script lang="ts">

import StyleFormValueList from '@/components/sidebar/style/ValueList.vue'
import initUI from '@/components/Common'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'
export default {
  name: 'StyleRadio',
  components: { StyleFormValueList },
  setup (props: any, context: any) {
    const info = initUI()
    const store = useStore()
    const inline = computed({
      get () {
        return info.getMeta('inline', 'custom') || false
      },
      set (v) {
        return info.setMeta('inline', v, 'custom')
      }
    })
    const { t } = useI18n()
    const isMobile = computed(() => store.state.design.endKind === 'mobile')
    return {
      t,
      inline,
      isMobile
    }
  }
}
</script>
