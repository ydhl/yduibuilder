<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.modal') }}</div>
  <div class="style-body d-none">
    <div class="row">
      <div class="col-sm-9 offset-sm-3">
        <label class=" form-check-label text-truncate d-block">
          <input type="checkbox" v-model="headless" value="1"> {{ t('style.modal.headless') }}</label>
        <label class=" form-check-label text-truncate d-block">
          <input type="checkbox" v-model="footless" value="1"> {{ t('style.modal.footless') }}</label>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.modal.esc') }}</label>
      <div class="col-sm-9">
        <label class=" form-control-plaintext">
          <input type="checkbox" v-model="esc" value="1">
          <span class="text-muted"> {{t('style.modal.escTip')}}</span>
        </label>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.modal.backdrop') }}</label>
      <div class="col-sm-9">
        <label class="form-control-plaintext">
          <AdvanceSelect :options="backdrops" :default-text="backdrop || t('action.notSet')" @change="(option) => backdrop = option.value"></AdvanceSelect>
        </label>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.modal.position') }}</label>
      <div class="col-sm-9">
        <Position v-model="position"></Position>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import Position from '@/components/common/Position.vue'
import AdvanceSelect from '@/components/common/AdvanceSelect.vue'

export default {
  name: 'StyleModal',
  components: { AdvanceSelect, Position },
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const headless = info.computedWrap('headless', 'custom', false)
    const footless = info.computedWrap('footless', 'custom', false)
    const esc = info.computedWrap('esc', 'custom', true)
    const backdrop = info.computedWrap('backdrop', 'custom', 'yes')
    const position = info.computedWrap('position', 'custom', [])
    const backdrops = [
      { name: 'Yes', value: 'yes', desc: t('style.modal.backdropYes') },
      { name: 'No', value: 'no', desc: t('style.modal.backdropNo') },
      { name: 'Static', value: 'static', desc: t('style.modal.backdropStatic') }
    ]
    return {
      ...info,
      footless,
      headless,
      backdrops,
      esc,
      backdrop,
      position,
      t
    }
  }
}
</script>
