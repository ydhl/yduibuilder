<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, bodyCss, uiCss,{'overflow-hidden':true, 'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
      <input type="file" :id="uiconfig.meta.id+uiconfig.type" class="d-block"
           :disabled="uiconfig.meta?.form?.state==='disabled'"
           :readonly="uiconfig.meta?.form?.state==='readonly'"
           :required="uiconfig.meta?.form?.required"
           :name="uiconfig.meta?.form?.inputName"
           :accept="uiconfig.meta?.custom?.accept || '*/*'"
           :multiple="uiconfig.meta?.custom?.multiple">
  </div>
</template>

<script lang="ts">
import File from '@/components/ui/js/File'
import { computed } from 'vue'

export default {
  name: 'Bootstrap_File',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const file = new File(props, context)
    const bodyCss = computed(() => {
      const arr: any = ['d-flex align-items-center']
      if (props.uiconfig.meta?.css?.formSizing && props.uiconfig.meta?.css?.formSizing !== 'normal') {
        arr.push('form-control-' + props.uiconfig.meta.css.formSizing)
      }
      if (props.uiconfig.meta?.form?.state === 'disabled') {
        arr.push('disabled')
      }
      if (props.uiconfig.meta?.form?.state === 'readonly') {
        arr.push('readonly')
      }
      return arr.join(' ')
    })
    return {
      ...file.setup(),
      bodyCss
    }
  }
}

</script>
