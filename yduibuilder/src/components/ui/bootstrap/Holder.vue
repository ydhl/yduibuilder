<template>
  <div :draggable='!inlineEditItemId' :class="[dragableCss, uiCss]" :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid">
  </div>
</template>

<script lang="ts">
import Holder from '@/components/ui/js/Holder'

export default {
  name: 'Bootstrap_Holder',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const container = new Holder(props, context)
    return {
      ...container.setup()
    }
  }
}

</script>
