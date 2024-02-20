<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.breadcrumb') }}</div>
  <div class="style-body d-none">
    <StyleValueList></StyleValueList>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
// import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import StyleValueList from '@/components/sidebar/style/ValueList.vue'
// import { useStore } from 'vuex'

export default {
  name: 'StyleCard',
  components: { StyleValueList },
  setup (props: any, context: any) {
    const info = initUI()
    // const selectedUIItem = info.selectedUIItem
    const { t } = useI18n()

    return {
      ...info,
      t
    }
  }
}
</script>
