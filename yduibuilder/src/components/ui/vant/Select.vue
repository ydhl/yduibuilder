<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, uiCss,{'overflow-hidden':true, 'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <input type="text" :value="firstValue"
            :disabled="uiconfig.meta?.form?.state==='disabled'"
            readonly
            :required="uiconfig.meta?.form?.required"
            :id="uiconfig.meta.id+uiconfig.type"
            :style="bodyStyle"
            :class="bodyCss">
  </div>
</template>

<script lang="ts">
import Select from '@/components/ui/js/Select'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Vant_Select',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const select = new Select(props, context, useStore())
    const bodyCss = computed(() => {
      const css = select.getUICss()
      const arr: any = ['van-field__control']
      if (css.foregroundTheme) arr.push(css.foregroundTheme)
      return arr
    })
    const bodyStyle = computed(() => {
      const style: any = {}
      const baseStyle = select.getUIStyle()
      if (baseStyle?.color) style.color = baseStyle?.color
      return select.appendImportant(style)
    })
    const firstValue = computed(() => {
      const values = props.uiconfig.meta.values
      if (!values || values.length === 0) return 'Simple 1'
      for (const value of values) {
        if (value.checked) return value.text
      }
      return values[0].text
    })
    return {
      ...select.setup(),
      bodyCss,
      firstValue,
      bodyStyle
    }
  }
}

</script>
