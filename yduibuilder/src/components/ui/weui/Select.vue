<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, bodyCss, uiCss,{'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <div class="weui-cell__bd"><div :class="frontClass" :style="frontStyle">{{ defaultValue }}</div></div>
  </div>
</template>

<script lang="ts">
import Select from '@/components/ui/js/Select'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Weui_Select',
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
      const arr: any = ['weui-cell weui-cell_active weui-cell_select overflow-hidden']
      if (props.uiconfig.meta?.custom?.borderless) {
        arr.push('border-0')
      }

      if (props.uiconfig.meta?.form?.state === 'disabled') {
        arr.push('disabled')
      }
      if (props.uiconfig.meta?.form?.state === 'readonly') {
        arr.push('readonly')
      }
      return arr
    })
    const values = computed(() => {
      if (!props.uiconfig.meta.values || props.uiconfig.meta.values.length === 0) {
        return [{ name: 'Item 1', value: 'item 1' }]
      }
      return props.uiconfig.meta.values
    })
    const defaultValue = computed(() => {
      for (const item of values.value) {
        if (item.checked) return item.name
      }
      return 'item 1'
    })
    const frontClass = computed(() => {
      const css: any = ['weui-select']
      const cssInfo = select.getUICss()
      const styleInfo = select.getUIStyle()
      if (cssInfo.foregroundTheme && !styleInfo.color) css.push(cssInfo.foregroundTheme)
      return css.join(' ')
    })
    const frontStyle = computed(() => {
      const style: any = []
      const styleInfo = select.getUIStyle()
      if (styleInfo.color) style.push(`color: ${styleInfo.color}`)
      return style.join(';')
    })
    return {
      ...select.setup(),
      defaultValue,
      bodyCss,
      frontClass,
      frontStyle
    }
  }
}

</script>
