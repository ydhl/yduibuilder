<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.collapse') }}</div>
  <div class="style-body d-none">
    <SubpageList :ui-item="selectedUIItem" :page-id="selectedPageId"></SubpageList>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import SubpageList from '@/components/sidebar/style/SubpageList.vue'

export default {
  name: 'StyleBadge',
  components: { SubpageList },
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
