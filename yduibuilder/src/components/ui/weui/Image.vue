<template>
  <img :draggable='draggable' :class="[dragableCss, uiCss]" :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid" :src="uiconfig.meta.value||'/logo.jpg'" :alt="uiconfig.meta.title"/>
</template>

<script lang="ts">
import Image from '@/components/ui/js/Image'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Weui_Image',
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
      if (!props.uiconfig.meta?.style?.width) {
        style.width = '100%'
      }
      return image.appendImportant(style)
    })
    return {
      ...image.setup(),
      uiStyle
    }
  }
}

</script>
