<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, bodyCss, uiCss,{'overflow-hidden':true, 'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <template v-for="(item, index) in values" :key="index" >
      <div :class="{'form-check d-flex mr-3': true}">
        <input type="checkbox" :checked="item.checked" class="form-check-input"
               :id="uiconfig.meta.id+item.value"
               :disabled="uiconfig.meta?.form?.state==='disabled'"
               :required="uiconfig.meta?.form?.required"
               :readonly="uiconfig.meta?.form?.state==='readonly'"
               :value="item.value" :name="uiconfig.meta?.form?.inputName">
        <label class="form-check-label" :for="uiconfig.meta.id+item.value">{{item.text}}</label>
      </div>
    </template>
  </div>
</template>

<script lang="ts">
import Checkbox from '@/components/ui/js/Checkbox'
import { computed } from 'vue'

export default {
  name: 'Bootstrap_Checkbox',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const checkbox = new Checkbox(props, context)
    const bodyCss = computed(() => {
      const arr: any = []
      arr.push('h-auto')
      if (props.uiconfig.meta?.css?.formSizing && props.uiconfig.meta?.css?.formSizing !== 'normal') {
        arr.push('form-control-' + props.uiconfig.meta.css.formSizing)
      }
      return arr
    })
    return {
      ...checkbox.setup(),
      bodyCss
    }
  }
}

</script>
