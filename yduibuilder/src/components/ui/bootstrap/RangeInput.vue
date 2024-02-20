<template>
    <input type="range"
           :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
           :data-pageid="pageid"
           :class="[dragableCss, uiCss,{'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]"
         :name="uiconfig.meta?.form?.inputName"
         :disabled="true"
         :placeholder="uiconfig.meta.title"
         :min="uiconfig.meta.custom?.min||1"
         :max="uiconfig.meta.custom?.max||100"
         :step="uiconfig.meta.custom?.step||1"
         :required="uiconfig.meta?.form?.required"
         :value="uiconfig.meta.value||50">
</template>

<script lang="ts">
import RangeInput from '@/components/ui/js/RangeInput'

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
    const rangeinput = new RangeInput(props, context)

    return {
      ...rangeinput.setup()
    }
  }
}

</script>
