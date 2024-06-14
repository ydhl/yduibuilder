<template>
  <div :draggable='draggable'
       :class="[dragableCss, uiCss, 'van-divider van-divider--hairline van-divider--content-center']"
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
      // delete myStyle?.height

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
