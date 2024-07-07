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
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.form.pagination') }}</label>
      <div class="col-sm-9">
        <div class="input-group input-group-sm ">
          <span class="input-group-text p-1">{{ t('style.form.total') }}</span>
          <input type="number" class="form-control  p-1 form-control-sm" minlength="1" v-model="total">
          <span class="input-group-text p-1">{{ t('style.form.pageSize') }}</span>
          <input type="number" class="form-control  p-1 form-control-sm" minlength="1" v-model="pageSize">
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'

export default {
  name: 'StylePagination',
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const sizing = info.computedWrap('paginationSizing', 'css', 'normal')
    const total = info.computedWrap('total', 'custom', '100')
    const pageSize = info.computedWrap('pageSize', 'custom', '10')
    return {
      ...info,
      sizing,
      total,
      pageSize,
      t
    }
  }
}
</script>
