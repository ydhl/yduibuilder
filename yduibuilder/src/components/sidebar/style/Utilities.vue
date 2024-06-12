<template>
  <div class="style-header">
    <i class="iconfont icon-tree-close"></i> {{ t('ui.utilities') }}
    <i class="iconfont icon-point text-danger" v-if="hasSet"></i>
    <i class="iconfont icon-point text-success" v-if="hasInherit"></i>
  </div>
  <div class="style-body d-none">
    <template v-if="isNotPage">
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
      <div class="row mt-2">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.utilities.opacity') }}</label>
      <div class="col-sm-9">
        <input type="number" min="0" max="1" step="0.01" v-model="opacity" class="form-control form-control-sm">
      </div>
    </div>
    </template>
    <div class="row mt-2">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.utilities.cursor') }}</label>
      <div class="col-sm-9">
        <div class="dropdown">
          <button class="btn btn-light btn-sm dropdown-toggle" :style="'cursor:' + cursor" type="button" data-bs-toggle="dropdown">{{cursor}}</button>
          <ul class="dropdown-menu">
            <li v-for="(csr, index) in cursors" :key="index">
              <a href="javascript:void(0)" :style="'cursor:' + csr" @click="cursor = csr" class="dropdown-item">{{ csr }}</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, toRef, watch } from 'vue'
import initUI from '../../Common'
import ColorPicker from '@/components/common/ColorPicker.vue'
import { useStore } from 'vuex'

export default {
  name: 'StyleUtilities',
  components: { ColorPicker },
  props: {
    previewMode: Boolean
  },
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const previewMode = toRef(props, 'previewMode')
    const hshadow = info.computedWrap('boxShadowH', 'custom', '', false, previewMode)
    const vshadow = info.computedWrap('boxShadowV', 'custom', '', false, previewMode)
    const blur = info.computedWrap('boxShadowBlur', 'custom', '', false, previewMode)
    const spread = info.computedWrap('boxShadowSpread', 'custom', '', false, previewMode)
    const inset = info.computedWrap('boxShadowInset', 'custom', '', false, previewMode)
    const color = info.computedWrap('boxShadowColor', 'custom', '', false, previewMode)
    const boxShadow = info.computedWrap('box-shadow', 'style', '', false, previewMode)
    const cursors = ['auto', 'crosshair', 'pointer', 'move', 'e-resize', 'ne-resize', 'nw-resize', 'n-resize', 'se-resize', 'sw-resize', 's-resize', 'w-resize', 'text', 'wait', 'help']
    watch([hshadow, vshadow, blur, color, inset, spread], (v) => {
      const box = `${hshadow.value} ${vshadow.value} ${blur.value} ${spread.value} ${color.value} ${inset.value}`
      // console.log('box-shadow change in Utilities:' + box)
      if (boxShadow.value !== box) {
        info.setMeta('box-shadow', `${hshadow.value} ${vshadow.value} ${blur.value} ${spread.value} ${color.value} ${inset.value}`, 'style', false, previewMode)
      }
    })
    const opacity = info.computedWrap('opacity', 'style', 100, false, previewMode)
    const cursor = info.computedWrap('cursor', 'style', 'auto', false, previewMode)

    const isNotPage = computed(() => {
      return info.selectedUIItem?.value?.type !== 'Page'
    })
    const hasInherit = computed(() => {
      return info.hasInheritStyle('style', ['opacity', 'cursor'], previewMode) ||
        info.hasInheritStyle(
          'custom',
          ['boxShadowH', 'boxShadowV', 'boxShadowBlur', 'boxShadowSpread', 'boxShadowInset', 'boxShadowColor'], previewMode
        )
    })

    const hasSet = computed(() => {
      return info.hasSetStyle('style', ['opacity', 'cursor'], previewMode) ||
        info.hasSetStyle(
          'custom',
          ['boxShadowH', 'boxShadowV', 'boxShadowBlur', 'boxShadowSpread', 'boxShadowInset', 'boxShadowColor'], previewMode
        )
    })

    const store = useStore()
    const previewStyleItem = computed(() => {
      return store.state.design.previewStyleItem
    })
    return {
      ...info,
      previewStyleItem,
      hasSet,
      hasInherit,
      t,
      isNotPage,
      opacity,
      cursors,
      cursor,
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
