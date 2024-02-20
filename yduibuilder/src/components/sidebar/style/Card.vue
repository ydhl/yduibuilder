<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.card') }}</div>
  <div class="style-body d-none">
    <div class="row">
      <div class="col-sm-9 offset-sm-3">
        <label class=" form-check-label text-truncate d-block">
          <input type="checkbox" v-model="headless" value="1"> {{ t('style.card.headless') }}</label>
        <label class=" form-check-label text-truncate d-block">
          <input type="checkbox" v-model="footless" value="1"> {{ t('style.card.footless') }}</label>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'

export default {
  name: 'StyleCard',
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const headless = computed({
      get: () => {
        return info.getMeta('headless', 'custom') || false
      },
      set: (v) => {
        info.setMeta('headless', v, 'custom')
      }
    })

    const footless = computed({
      get: () => {
        return info.getMeta('footless', 'custom') || false
      },
      set: (v) => {
        info.setMeta('footless', v, 'custom')
      }
    })
    return {
      ...info,
      footless,
      headless,
      t
    }
  }
}
</script>
