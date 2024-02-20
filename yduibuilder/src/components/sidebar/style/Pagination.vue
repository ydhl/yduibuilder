<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.pagination') }}</div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.form.size') }}</label>
      <div class="col-sm-9">
        <select v-model="sizing" class="form-control form-control-sm">
          <option :value="key" :key="key" v-for="(name, key) in cssMap.paginationSizing">{{name}}</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'

export default {
  name: 'StylePagination',
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const sizing = computed({
      get: () => {
        return info.getMeta('paginationSizing', 'css') || ''
      },
      set: (v) => {
        info.setMeta('paginationSizing', v, 'css')
      }
    })
    return {
      ...info,
      sizing,
      t
    }
  }
}
</script>
