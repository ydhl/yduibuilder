<template>
  <div :draggable='draggable' :class="[dragableCss, uiCss]" :style="uiStyle" :id="myId" :data-type="uiconfig.type"
  :data-pageid="pageid" :alt="uiconfig.meta.title">
    <img :src="uiconfig.meta.value||'/logo.jpg'" class="van-image__img" :style="imgStyle" />
  </div>
</template>

<script lang="ts">
import Image from '@/components/ui/js/Image'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Vant_Image',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const image = new Image(props, context, useStore())
    const uiStyle = computed(() => {
      const style = image.getUIStyle()
      delete style['object-fit']
      delete style['object-position']
      if (!props.uiconfig.meta?.style?.width) {
        style.width = '100%'
      }
      style.overflow = 'hidden'
      return image.appendImportant(style)
    })
    const imgStyle = computed(() => {
      const style = image.getUIStyle()
      const imgStyle = []
      if (style?.['object-fit']) imgStyle['object-fit'] = style['object-fit']
      if (style?.['object-position']) imgStyle['object-position'] = style['object-position']
      return image.appendImportant(imgStyle)
    })
    const uiCss = computed(() => {
      const css = image.getUICss()
      const _ = Object.values(css)
      _.push('van-image')
      return _.join(' ')
    })
    return {
      ...image.setup(),
      uiCss,
      uiStyle,
      imgStyle
    }
  }
}

</script>
