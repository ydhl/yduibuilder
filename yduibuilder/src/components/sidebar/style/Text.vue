<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.text') }}</div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.form.value') }}</label>
      <div class="col-sm-9">
        <textarea class="form-control form-control-sm" type="text" v-model="value"></textarea>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.text.type') }}</label>
      <div class="col-sm-9">
        <select class="form-select form-select-sm" v-model="currType">
          <optgroup :label="t('style.text.'+key)" :key="key" v-for="(list, key) in textTypes">
            <option :value="value" v-for="(value, type) in list" :key="value" :selected="value===currType">{{t('style.text.'+type)}}</option>
          </optgroup>
        </select>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import { ref } from 'vue'

export default {
  name: 'StyleText',
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const value = info.computedWrap('value')

    const textTypes = ref({ Heading: { H1: 'H1', H2: 'H2', H3: 'H3', H4: 'H4', H5: 'H5', H6: 'H6' }, Paragraph: { Paragraph: 'p', Normal: 'span' } })

    const currType = info.computedWrap('type', 'custom', 'span')

    return {
      ...info,
      t,
      currType,
      textTypes,
      value
    }
  }
}
</script>
