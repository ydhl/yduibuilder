<template>
    <input type="range"
           :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
           :data-pageid="pageid"
           :class="[dragableCss, uiCss,{'form-control-range':true,'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]"
         :readonly="true"
         :placeholder="uiconfig.meta.title"
         :min="uiconfig.meta.custom?.min!==undefined ? uiconfig.meta.custom?.min : 1"
         :max="uiconfig.meta.custom?.max!==undefined ? uiconfig.meta.custom?.max : 100"
         :step="uiconfig.meta.custom?.step!==undefined ? uiconfig.meta.custom?.step : 1"
         :required="uiconfig.meta?.form?.required"
         :value="uiconfig.meta.value!==undefined ? uiconfig.meta.value : 0">
</template>

<script lang="ts">
import RangeInput from '@/components/ui/js/RangeInput'
import { useStore } from 'vuex'
import { computed } from 'vue'

export default {
  name: 'Bootstrap_RangeInput',
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
    const getMyCss = (uiconfig, css) => {
      const range: any = []
      if (uiconfig.meta?.css?.formSizing && uiconfig.meta?.css?.formSizing !== 'normal') {
        range.push('form-control-range-' + uiconfig.meta.css.formSizing)
      }
      const color = uiconfig.meta?.style?.color
      if (uiconfig.meta?.css?.foregroundTheme && uiconfig.meta?.css?.foregroundTheme !== 'default' && !color) {
        range.push('range-' + uiconfig.meta.css.foregroundTheme)
      }

      if (range.length) css.my = range.join(' ')
      return css
    }

    const getMyStyle = (uiconfig, style) => {
      const background:any = []
      const backgroundSize:any = ['50%', '100%']
      const color = uiconfig.meta?.style?.color
      if (color) {
        style.border = `1px solid ${color}`
        background.push(`-webkit-linear-gradient(top, ${color}, ${color})`)
      }
      if (background.length > 0) style['background-image'] = background.join(',')

      const minValue = uiconfig.meta.custom?.min === undefined ? 1 : uiconfig.meta.custom?.min
      const defaultValue = uiconfig.meta.value === undefined ? 0 : parseFloat(uiconfig.meta.value)

      const maxValue = uiconfig.meta.custom?.max === undefined ? 100 : uiconfig.meta.custom?.max
      backgroundSize[0] = (defaultValue === 0 || maxValue === minValue) ? '0%' : ((defaultValue - minValue) / (maxValue - minValue) * 100) + '%'
      style['background-size'] = backgroundSize.join(' ')
      // console.log(style)
      return style
    }
    const uiCss = computed(() => {
      let css = rangeinput.getUICss()
      delete css.foregroundTheme

      const previewItem = rangeinput.hasActiveState() ? store.state.page.previewStyleItem : null
      css.range = 'form-control-range'
      css = getMyCss(props.uiconfig, css)
      if (previewItem) {
        css = getMyCss(previewItem, css)
      }

      return Object.values(css).join(' ')
    })

    const uiStyle = computed(() => {
      let style = rangeinput.getUIStyle()
      delete style.color
      style = getMyStyle(props.uiconfig, style)
      const previewItem = rangeinput.hasActiveState() ? store.state.page.previewStyleItem : null
      if (previewItem) {
        style = getMyStyle(previewItem, style)
      }
      return rangeinput.appendImportant(style)
    })
    return {
      ...rangeinput.setup(),
      uiStyle,
      uiCss
    }
  }
}

</script>
