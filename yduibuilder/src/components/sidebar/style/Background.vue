<template>
  <div class="style-header">
    <i class="iconfont icon-tree-close"></i> {{t('style.background.backgroundForeground')}}
    <i class="iconfont icon-point text-danger" v-if="hasSet"></i>
    <i class="iconfont icon-point text-success" v-if="hasInherit"></i>
  </div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.background.foreground') }}</label>
      <div class="col-sm-9 d-flex align-items-start align-content-start">
          <select class="me-2 form-select form-select-sm" style="flex-shrink: 2" v-model="foregroundCss">
            <option :key="value" :value="value" v-for="value in cssMap['foregroundTheme']">{{value}}</option>
          </select>
        <ColorPicker css="w-100" v-model="foregroundColor"></ColorPicker>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.background.background') }}</label>
      <div class="col-sm-9 d-flex align-items-start align-content-start">
        <select class="me-2 form-select form-select-sm" style="flex-shrink: 2" v-model="backgroundCss">
          <option :key="value" :value="value" v-for="value in cssMap['backgroundTheme']">{{value}}</option>
        </select>
        <ColorPicker css="w-100" v-model="backgroundColor"></ColorPicker>
      </div>
    </div>

    <div class="row mb-1" :key="index" v-for="(image, index) in backgroundImages">
      <div class="col-sm-9 offset-3">
        <BackgroundImage :preview-model="previewMode" :index="index"></BackgroundImage>
      </div>
    </div>
    <div class="row mt-2 mb-1">
      <div class="col-sm-9 offset-3">
        <button class="btn btn-sm btn-outline-primary" type="button" @click="addImage">{{ t('style.background.addImage') }}</button>
      </div>
    </div>
    <div v-if="backgroundImages && backgroundImages.length>0" class="row mb-1">
      <label class="col-sm-3 text-truncate text-end">{{ t('style.background.blendMode') }}</label>
      <div class="col-sm-9">
        <select class="form-select form-select-sm" v-model="blendMode">
          <option v-for="(item, index) in blendModes" :key="index" :value="item">{{t('style.background.blend.' + item)}}</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import ColorPicker from '@/components/common/ColorPicker.vue'
import { useI18n } from 'vue-i18n'
import { computed, ref, toRef } from 'vue'
import BackgroundImage from '@/components/sidebar/style/BackgroundImage.vue'

export default {
  name: 'StyleBackground',
  components: { BackgroundImage, ColorPicker },
  props: {
    previewMode: Boolean
  },
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const blendModes = ref<Array<string>>([
      'normal',
      'multiply',
      'screen',
      'overlay',
      'darken',
      'lighten',
      'color-dodge',
      'saturation',
      'color',
      'luminosity'
    ])
    const previewMode = toRef(props, 'previewMode')

    const foregroundColor = info.computedWrap('color', 'style', undefined, false, previewMode)
    const foregroundCss = info.computedWrap('foregroundTheme', 'css', 'default', false, previewMode)
    const blendMode = info.computedWrap('background-blend-mode', 'style', undefined, false, previewMode)
    const backgroundCss = info.computedWrap('backgroundTheme', 'css', 'default', false, previewMode)
    const backgroundColor = info.computedWrap('background-color', 'style', undefined, false, previewMode)
    const backgroundImages = computed(() => {
      return info.getMeta('background-image', 'style', previewMode) || []
    })

    const addImage = (index) => {
      info.setMeta('background-image', [{ type: 'image' }], 'style', true, previewMode)
      // 对应的其他Background属性也对应的生成
      info.setMeta('background-size', ['auto'], 'style', true, previewMode)
      info.setMeta('background-repeat', ['repeat'], 'style', true, previewMode)
      info.setMeta('background-position', ['0% 0%'], 'style', true, previewMode)
      info.setMeta('background-clip', ['border-box'], 'style', true, previewMode)
      info.setMeta('background-origin', ['padding-box'], 'style', true, previewMode)
      info.setMeta('background-attachment', ['scroll'], 'style', true, previewMode)
    }

    const hasInherit = computed(() => {
      return info.hasInheritStyle('css', ['backgroundTheme', 'foregroundTheme'], previewMode) ||
        info.hasInheritStyle('style', ['background-image', 'background-color', 'background-blend-mode', 'color'], previewMode)
    })

    const hasSet = computed(() => {
      return info.hasSetStyle('css', ['backgroundTheme', 'foregroundTheme'], previewMode) ||
        info.hasSetStyle('style', ['background-image', 'background-color', 'background-blend-mode', 'color'], previewMode)
    })
    return {
      t,
      ...info,
      hasInherit,
      hasSet,
      foregroundColor,
      foregroundCss,
      backgroundCss,
      backgroundColor,
      backgroundImages,
      blendModes,
      blendMode,
      addImage
    }
  }
}
</script>
