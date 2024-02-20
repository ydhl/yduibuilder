<template>
  <div class="style-header">
    <i class="iconfont icon-tree-close"></i> {{ t('ui.utilities') }}
    <i class="iconfont icon-point text-danger" v-if="hasSet"></i>
    <i class="iconfont icon-point text-success" v-if="hasInherit"></i>
  </div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.utilities.shadow') }}</label>
      <div class="col-sm-9">
        <div class="input-group input-group-sm">
          <span class="input-group-text">{{ t('style.utilities.hShadow') }}</span>
          <input type="text" v-model="hshadow" class="form-control form-control-sm">
          <span class="input-group-text">{{ t('style.utilities.vShadow') }}</span>
          <input type="text" v-model="vshadow" class="form-control form-control-sm">
        </div>
        <div class="input-group input-group-sm">
          <span class="input-group-text">{{ t('style.utilities.blur') }}</span>
          <input type="text" v-model="blur" class="form-control form-control-sm">
          <span class="input-group-text">{{ t('style.utilities.spread') }}</span>
          <input type="text" v-model="spread" class="form-control form-control-sm">
        </div>
        <div class="input-group input-group-sm">
          <span class="input-group-text">{{ t('style.utilities.shadowType') }}</span>
          <select class="form-select form-select-sm" v-model="inset">
            <option value="">outset</option>
            <option value="inset">inset</option>
          </select>
        </div>
        <ColorPicker css="w-100" v-model="color"></ColorPicker>
      </div>
    </div>
    <div class="row mt-3">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.utilities.opacity') }}</label>
      <div class="col-sm-9">
        <input type="number" min="0" max="1" step="0.01" v-model="opacity" class="form-control form-control-sm">
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, watch } from 'vue'
import initUI from '../../Common'
import ColorPicker from '@/components/common/ColorPicker.vue'

export default {
  name: 'StyleUtilities',
  components: { ColorPicker },
  props: {
    previewMode: Boolean
  },
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const hshadow = info.computedWrap('boxShadowH', 'custom', '', false, props.previewMode)
    const vshadow = info.computedWrap('boxShadowV', 'custom', '', false, props.previewMode)
    const blur = info.computedWrap('boxShadowBlur', 'custom', '', false, props.previewMode)
    const spread = info.computedWrap('boxShadowSpread', 'custom', '', false, props.previewMode)
    const inset = info.computedWrap('boxShadowInset', 'custom', '', false, props.previewMode)
    const color = info.computedWrap('boxShadowColor', 'custom', '', false, props.previewMode)
    watch([hshadow, vshadow, blur, color, inset, spread], (v) => {
      info.setMeta('box-shadow', `${hshadow.value} ${vshadow.value} ${blur.value} ${spread.value} ${color.value} ${inset.value}`, 'style', false, props.previewMode)
    })
    const opacity = info.computedWrap('opacity', 'style', 1, false, props.previewMode)

    const hasInherit = computed(() => {
      return info.hasInheritStyle('style', ['opacity', 'box-shadow']) ||
        info.hasInheritStyle(
          'custom',
          ['boxShadowH', 'boxShadowV', 'boxShadowBlur', 'boxShadowSpread', 'boxShadowInset', 'boxShadowColor']
        )
    })

    const hasSet = computed(() => {
      return info.hasInheritStyle('style', ['opacity', 'box-shadow']) ||
        info.hasInheritStyle(
          'custom',
          ['boxShadowH', 'boxShadowV', 'boxShadowBlur', 'boxShadowSpread', 'boxShadowInset', 'boxShadowColor']
        )
    })
    return {
      ...info,
      hasSet,
      hasInherit,
      t,
      opacity,
      hshadow,
      vshadow,
      blur,
      spread,
      inset,
      color
    }
  }
}
</script>
