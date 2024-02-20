<template>
  <div class="style-header">
    <i class="iconfont icon-tree-close"></i> {{t('style.form.form')}}
  </div>
  <div class="style-body d-none">
    <template v-if="!isMobile">
      <div class="row">
        <label class="col-sm-3 col-form-label text-end">{{ t('style.form.size') }}</label>
        <div class="col-sm-9">
          <select v-model="formSize" class="form-control form-control-sm">
            <option :value="key" :key="key" v-for="(name, key) in cssMap.formSizing">{{name}}</option>
          </select>
        </div>
      </div>
    </template>
    <template v-if="isSingleValue">
      <div class="row">
        <label for="form-value" class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.form.value') }}</label>
        <div class="col-sm-9">
          <input type="text" v-model="singleValue" class="form-control form-control-sm" id="form-value">
        </div>
      </div>
    </template>

    <div class="row" v-if="hasPlaceholder">
      <label for="form-placeholder" class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.form.placeholder') }}</label>
      <div class="col-sm-9">
        <input type="text" v-model="placeholderValue" class="form-control form-control-sm" id="form-placeholder">
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.form.state') }}</label>
      <div class="col-sm-9">
        <div class="overflow-auto">
          <div class="btn-group btn-group-sm">
            <button :class="{'btn btn-outline-primary': true, 'active': state=='normal'}" type="button" @click="state = 'normal'">{{ t('style.form.stateNormal') }}</button>
            <button :class="{'btn btn-outline-primary': true, 'active': state=='disabled'}" type="button" @click="state = 'disabled'">{{ t('style.form.stateDisabled') }}</button>
            <button :class="{'btn btn-outline-primary': true, 'active': state=='readonly'}" type="button" @click="state = 'readonly'">{{ t('style.form.stateReadonly') }}</button>
            <button :class="{'btn btn-outline-primary': true, 'active': state=='hidden'}" type="button" @click="state = 'hidden'">{{ t('style.form.stateHidden') }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { computed } from 'vue'
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'

export default {
  name: 'StyleForm',
  setup (props: any, context: any) {
    const info = initUI()
    const store = useStore()
    const formSize = info.computedWrap('formSizing', 'css', 'default')
    const singleValue = info.computedWrap('value')
    const placeholderValue = info.computedWrap('placeholder', 'form')
    const isSingleValue = computed(() => {
      return /Input|Textarea/ig.test(info.selectedUIItem.value.type)
    })
    const isMultiValue = computed(() => {
      return /Select|Radio|Checkbox/ig.test(info.selectedUIItem.value.type)
    })
    const state = info.computedWrap('state', 'form', 'NORMAL')
    const hasPlaceholder = computed(() => {
      return info.selectedUIItem.value?.type === 'Input' || info.selectedUIItem.value?.type === 'Textarea'
    })
    const isMobile = computed(() => store.state.design.endKind === 'mobile')
    const { t } = useI18n()

    return {
      t,
      formSize,
      isSingleValue,
      isMultiValue,
      singleValue,
      placeholderValue,
      state,
      hasPlaceholder,
      isMobile,
      ...info
    }
  }
}
</script>
