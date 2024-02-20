<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, bodyCss, uiCss,{'overflow-hidden':true, 'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <textarea class="w-100 border-0" :style="uiconfig.meta.custom?.autoRow ? 'resize: none' : ''" :id="uiconfig.meta.id+uiconfig.type" :name="uiconfig.meta?.form?.inputName"
              :disabled="uiconfig.meta?.form?.state==='disabled'"
              :readonly="uiconfig.meta?.form?.state==='readonly'"
              :required="uiconfig.meta?.form?.required"  style="font-style: inherit"
              :placeholder="uiconfig.meta?.form?.placeholder" :rows="uiconfig.meta.custom?.row" v-model="defaultValue"></textarea>
    <div v-if="uiconfig.meta?.custom?.wordCountVisible" class="ml-3">0{{uiconfig.meta?.custom?.maxLength ? '/' + uiconfig.meta?.custom?.maxLength : ''}}</div>
    <div v-if="uiconfig.meta?.custom?.clearButtonVisible" class="cursor ml-3">×</div>
  </div>
</template>

<script lang="ts">
import Textarea from '@/components/ui/js/Textarea'
import { computed } from 'vue'

export default {
  name: 'Bootstrap_Textarea',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const textarea = new Textarea(props, context)
    const bodyCss = computed(() => {
      const arr = ['form-control d-flex justify-content-between align-items-end h-auto']
      if (props.uiconfig.meta?.css?.formSizing && props.uiconfig.meta?.css?.formSizing !== 'normal') {
        arr.push('form-control-' + props.uiconfig.meta.css.formSizing)
      }
      if (props.uiconfig.meta?.form?.state === 'disabled') {
        arr.push('disabled')
      }
      if (props.uiconfig.meta?.form?.state === 'readonly') {
        arr.push('readonly')
      }
      return arr
    })

    return {
      ...textarea.setup(),
      bodyCss
    }
  }
}

</script>
