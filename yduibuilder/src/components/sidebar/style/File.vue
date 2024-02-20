<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.file') }}</div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.file.accept') }}</label>
      <div class="col-sm-9">
        <input type="text" class="form-control form-control-sm" :placeholder="t('style.file.acceptTip')" v-model="accept">
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.file.multiple') }}</label>
      <div class="col-sm-9 d-flex align-items-center">
        <input type="checkbox" v-model="multiple">
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'

export default {
  name: 'StyleFile',
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const accept = computed({
      get () {
        return info.getMeta('accept', 'custom') || ''
      },
      set (v) {
        info.setMeta('accept', v, 'custom')
      }
    })
    const multiple = computed({
      get () {
        return info.getMeta('multiple', 'custom') || false
      },
      set (v) {
        info.setMeta('multiple', v, 'custom')
      }
    })

    return {
      ...info,
      multiple,
      accept,
      t
    }
  }
}
</script>
