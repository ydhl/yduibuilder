<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.icon') }}</div>
  <div class="style-body d-none">
    <IconSetting :hide-position="true"></IconSetting>
    <div class="row mt-1">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.icon.size') }}</label>
      <div class="col-sm-9">
        <input type="text" class="form-control form-control-sm" :placeholder="t('style.layout.widthTip')" v-model="fontSize">
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import IconSetting from '@/components/sidebar/style/IconSetting.vue'
import { computed } from 'vue'

export default {
  name: 'StyleButton',
  components: { IconSetting },
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const fontSize = computed({
      get () {
        return info.getMeta('font-size', 'style') || ''
      },
      set (v) {
        info.setMeta('font-size', v, 'style')
      }
    })
    return {
      ...info,
      fontSize,
      t
    }
  }
}
</script>
