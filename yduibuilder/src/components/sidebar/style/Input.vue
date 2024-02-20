<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.input') }}</div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.form.inputType') }}</label>
      <div class="col-sm-9">
        <div class="overflow-auto">
          <div class="btn-group btn-group-sm">
            <button :class="{'btn btn-outline-primary': true, 'active': currType==type}" type="button"
                    @click="currType = type" v-for="(type) in types" :key="type">{{ t('style.form.inputType'+type) }}</button>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-9 offset-3">
        <div class="form-check form-check-inline">
          <input type="checkbox" class="form-check-input" id="form-clear-button" v-model="clearButtonVisible">
          <label for="form-clear-button" class=" form-check-label text-truncate">{{ t('style.form.clearButton') }}</label>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-9 offset-3">
        <div class="form-check form-check-inline">
          <input type="checkbox" class="form-check-input" id="form-word-count" v-model="wordCountVisible">
          <label for="form-word-count" class=" form-check-label text-truncate">{{ t('style.form.wordCount') }}</label>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-9 offset-3">
        <div class="form-check form-check-inline">
          <input type="checkbox" class="form-check-input" id="form-borderless" v-model="borderless">
          <label for="form-borderless" class=" form-check-label text-truncate">{{ t('style.form.borderless') }}</label>
        </div>
      </div>
    </div>

    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.form.autocomplete') }}</label>
      <div class="col-sm-9">
        <select v-model="autocomplete" class="form-control form-control-sm">
          <option value="on">On</option>
          <option value="off">Off</option>
        </select>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.form.maxLength') }}</label>
      <div class="col-sm-9">
        <input v-model="maxLength" type="number" class="form-control form-control-sm">
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('ui.icon') }}</label>
      <div class="col-sm-9">
        <IconSetting></IconSetting>
      </div>
    </div>

  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import initUI from '@/components/Common'
import IconSetting from '@/components/sidebar/style/IconSetting.vue'
export default {
  name: 'StyleInput',
  components: { IconSetting },
  setup (props: any, context: any) {
    const { t } = useI18n()
    const info = initUI()
    const types = ['Text', 'Color', 'Date', 'Email', 'Number', 'Password', 'URL']

    const borderless = info.computedWrap('borderless', 'custom', false)
    const maxLength = info.computedWrap('maxLength', 'custom')
    const wordCountVisible = info.computedWrap('wordCountVisible', 'custom', 0)
    const clearButtonVisible = info.computedWrap('clearButtonVisible', 'custom', 0)
    const autocomplete = info.computedWrap('autocomplete', 'custom')
    const currType = info.computedWrap('custom', 'custom', 'Text')
    return {
      t,
      currType,
      types,
      autocomplete,
      maxLength,
      wordCountVisible,
      borderless,
      clearButtonVisible
    }
  }
}
</script>
