<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, myCss,{'overflow-hidden':true, 'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <select :size="size" :multiple="isMultiple" :placeholder="uiconfig.meta?.form?.placeholder"
            :disabled="uiconfig.meta?.form?.state==='disabled'"
            :readonly="uiconfig.meta?.form?.state==='readonly'"
            :required="uiconfig.meta?.form?.required"
            :id="uiconfig.meta.id+uiconfig.type" :class="bodyCss" :style="bodyStyle" :name="uiconfig.meta?.form?.inputName">
      <option v-for="(item, index) in uiconfig.meta.values" :selected="item.checked" :key="index" :value="item.value">{{item.text}}</option>
    </select>
  </div>
</template>

<script lang="ts">
import Select from '@/components/ui/js/Select'
import { computed } from 'vue'

export default {
  name: 'Bootstrap_Select',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const select = new Select(props, context)
    const myCss = computed(() => {
      const css = select.getUICss()
      delete css.formSizing
      return Object.values(css).join(' ')
    })
    const bodyCss = computed(() => {
      const arr: any = ['form-control']
      if (props.uiconfig.meta?.css?.formSizing && props.uiconfig.meta?.css?.formSizing !== 'normal') {
        arr.push('form-control-' + props.uiconfig.meta.css.formSizing)
      }
      return arr
    })
    const bodyStyle = computed(() => {
      const style = select.getUIStyle()
      const newStyle = {}
      for (const styleKey in style) {
        if (styleKey.match(/height/)) {
          newStyle[styleKey] = style[styleKey]
        }
      }
      return select.appendImportant(newStyle)
    })
    return {
      ...select.setup(),
      bodyStyle,
      myCss,
      bodyCss
    }
  }
}

</script>
