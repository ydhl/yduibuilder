<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.hr') }}</div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.form.title') }}</label>
      <div class="col-sm-9">
        <input class="form-control form-control-sm" type="text" v-model="text">
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'

export default {
  name: 'StyleButton',
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const text = computed({
      get () {
        return info.getMeta('value') || ''
      },
      set (v) {
        info.setMeta('value', v)
      }
    })
    return {
      ...info,
      text,
      t
    }
  }
}
</script>
