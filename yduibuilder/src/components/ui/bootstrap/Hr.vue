<template>
    <div :draggable='draggable'
          :class="[dragableCss, uiCss, 'd-flex justify-content-center align-items-center']" :style="uiStyle" :id="myId" :data-type="uiconfig.type"
         :data-pageid="pageid">
      <div :class="['flex-grow-1', lineCss]" :style="lineStyle"></div>
      <div v-if="uiconfig.meta.value" :class="['flex-shrink-0 ps-2 pe-2', textCss]" :style="textStyle">{{uiconfig.meta.value}}</div>
      <div :class="['flex-grow-1', lineCss]" :style="lineStyle"></div>
    </div>
</template>

<script lang="ts">
import Hr from '@/components/ui/js/Hr'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Bootstrap_Hr',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const hr = new Hr(props, context, useStore())
    const setup = hr.setup()
    const store = useStore()
    const uiStyle = computed(() => {
      const myStyle = hr.getUIStyle()
      delete myStyle?.height
      delete myStyle?.['background-color']
      delete myStyle.color

      if (!myStyle?.width) {
        myStyle.width = '100%'
      }

      return hr.appendImportant(myStyle)
    })
    const uiCss = computed(() => {
      const css = hr.getUICss()
      delete css.backgroundTheme
      delete css.foregroundTheme
      return Object.values(css).join(' ')
    })

    const lineStyle = computed(() => {
      const myStyle = hr.getUIStyle()
      const newStyle: any = {}
      let height = myStyle?.height || '1px'
      const style = props.uiconfig.meta?.custom?.style || 'solid'
      if (style === 'double' && parseInt(height) < 3) {
        height = '3px'
      }
      newStyle['border-top-width'] = height
      newStyle['border-top-style'] = style

      if (!myStyle?.['background-color'] && !props.uiconfig.meta?.css?.backgroundTheme) {
        newStyle['border-top-color'] = 'rgba(0,0,0,.1)'
      } else if (myStyle?.['background-color']) {
        newStyle['border-top-color'] = myStyle?.['background-color']
      }
      return hr.appendImportant(newStyle)
    })
    const lineCss = computed(() => {
      const css = hr.getUICss()
      if (!props.uiconfig.meta?.style?.['background-color'] && css?.backgroundTheme) {
        return store.getters.translate('borderColorClass', props.uiconfig.meta?.css?.backgroundTheme)
      }
      return ''
    })
    const textStyle = computed(() => {
      const myStyle = hr.getUIStyle()
      const newStyle: any = {}

      if (myStyle?.color) {
        newStyle.color = myStyle?.color
      }
      return hr.appendImportant(newStyle)
    })
    const textCss = computed(() => {
      const css = hr.getUICss()
      return props.uiconfig.meta?.style?.color ? '' : css?.foregroundTheme
    })
    return {
      ...setup,
      uiCss,
      uiStyle,
      lineCss,
      lineStyle,
      textCss,
      textStyle
    }
  }
}

</script>
