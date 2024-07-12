<template>
  <div :draggable='draggable'
       :class="[dragableCss, uiCss]"
       :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid">
    {{uiconfig.meta.value}}
  </div>
</template>

<script lang="ts">
import Hr from '@/components/ui/js/Hr'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Vant_Hr',
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
    const uiStyle = computed(() => {
      const myStyle = hr.getUIStyle()
      delete myStyle?.height
      let height = props.uiconfig?.meta.style?.height || '1px'
      const style = props.uiconfig.meta?.custom?.style || 'solid'
      if (style === 'double' && parseInt(height) < 3) {
        height = '3px'
      }
      myStyle['--border-width'] = height
      // 背景色是边框颜色
      if (myStyle?.['background-color']) {
        myStyle['border-color'] = myStyle?.['background-color']
      }

      delete myStyle?.['background-color']
      if (!myStyle?.width) {
        myStyle.width = '100%'
      }

      return hr.appendImportant(myStyle)
    })
    const uiCss = computed(() => {
      const css = hr.getUICss()
      delete css.backgroundTheme
      delete css.foregroundTheme
      const _ = Object.values(css) || []
      _.push('van-divider van-divider--content-center')
      switch (props.uiconfig.meta?.custom?.style) {
        case 'dotted':
          _.push('van-divider--dotted')
          break
        case 'dashed':
          _.push('van-divider--dashed')
          break
        case 'double':
          _.push('van-divider--double')
          break
        case 'solid':
        default:
          _.push('van-divider--hairline')
          break
      }
      // 前景色是文字颜色
      // 背景色是边框颜色
      if (props.uiconfig.meta?.css?.backgroundTheme) {
        _.push(`van-border-${props.uiconfig.meta?.css?.backgroundTheme}`)
      }
      if (props.uiconfig.meta?.css?.foregroundTheme) {
        _.push(`van-text-${props.uiconfig.meta?.css?.foregroundTheme}`)
      }
      return _.join(' ')
    })

    return {
      ...setup,
      uiCss,
      uiStyle
    }
  }
}

</script>
