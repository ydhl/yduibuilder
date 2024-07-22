<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, uiCss,{'weui-slider-box':true, 'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <div class="weui-slider">
      <div :style="bgStyle" :class="bgTheme">
        <div :style="trackStyle" :class="trackTheme"></div>
        <div :style="handleStyle" :class="handleTheme"></div>
      </div>
    </div>
    <div class="weui-slider-box__value">{{ uiconfig.meta.value||50 }}</div>
  </div>
</template>

<script lang="ts">
import RangeInput from '@/components/ui/js/RangeInput'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Weui_RangeInput',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const store = useStore()
    const rangeinput = new RangeInput(props, context, store)
    const defaultValue = computed(() => props.uiconfig.meta.value === undefined ? 50 : props.uiconfig.meta.value)
    /**
     * 滑块主题样式
     */
    const handleStyle = computed(() => {
      const style: any = []
      style.push(`left: ${defaultValue.value}%; !important;`)
      return style.join(';')
    })
    const handleTheme = computed(() => {
      const css: any = ['weui-slider__handler']
      return css.join(' ')
    })
    /**
     * 前景色样式，已滑动距离
     */
    const trackTheme = computed(() => {
      const css: any = ['weui-slider__track']
      if (props.uiconfig.meta?.css?.foregroundTheme) {
        css.push(store.getters.translate('backgroundTheme', props.uiconfig.meta?.css?.foregroundTheme))
      }
      return css.join(' ')
    })
    const trackStyle = computed(() => {
      const style: any = [`width: ${defaultValue.value}% !important;`]
      if (props.uiconfig.meta?.style?.color) {
        style.push('background-color:' + props.uiconfig.meta?.style?.color + ' !important;')
      }
      return style.join(';')
    })
    /**
     * 背景色样式，底色
     */
    const bgTheme = computed(() => {
      const css: any = ['weui-slider__inner']
      const cssMap = rangeinput.getUICss()
      if (cssMap.backgroundTheme) {
        css.push(cssMap.backgroundTheme)
      }
      return css.join(' ')
    })
    const bgStyle = computed(() => {
      const styleMap = rangeinput.getUIStyle()
      const style: any = {}
      if (styleMap['background-color']) style['background-color'] = styleMap['background-color']
      if (styleMap.height) {
        style.height = styleMap.height
      }
      return rangeinput.appendImportant(style)
    })

    const uiStyle = computed(() => {
      const style = rangeinput.getUIStyle()
      delete style.color
      delete style['background-color']
      delete style.height
      return rangeinput.appendImportant(style)
    })
    const uiCss = computed(() => {
      const css = rangeinput.getUICss()
      delete css.backgroundTheme
      delete css.foregroundTheme
      return Object.values(css).join(' ')
    })
    return {
      ...rangeinput.setup(),
      uiStyle,
      uiCss,
      bgTheme,
      bgStyle,
      trackTheme,
      trackStyle,
      handleStyle,
      handleTheme
    }
  }
}

</script>
