<template>
  <img :draggable='draggable' :class="[dragableCss, uiCss]" :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid" :src="uiconfig.meta.value||'/logo.jpg'" :alt="uiconfig.meta.title"/>
</template>

<script lang="ts">
import Image from '@/components/ui/js/Image'
import { useStore } from 'vuex'

export default {
  name: 'Bootstrap_Image',
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
    return {
      ...image.setup()
    }
  }
}

</script>
