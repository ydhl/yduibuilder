<template>
  <LayuiFormGroup :uiconfig="uiconfig" :pageid="pageid"
                      :draggable='draggable' :dragableCss="dragableCss">
    <div :class="bodyCss" :style="bodyStyle">
      <input type="range" :id="uiconfig.meta.id+uiconfig.type"
             :class="rangeCss" :style="rangeStyle"
             :name="uiconfig.meta?.form?.inputName"
             :placeholder="uiconfig.meta.title"
             :disabled="uiconfig.meta?.form?.state==='disabled'"
             :readonly="uiconfig.meta?.form?.state==='readonly'"
             :min="uiconfig.meta.custom?.min||1"
             :max="uiconfig.meta.custom?.max||100"
             :step="uiconfig.meta.custom?.step||1"
             :required="uiconfig.meta?.form?.required"
             :value="uiconfig.meta.value||50">
    </div>
  </LayuiFormGroup>
</template>

<script lang="ts">
import RangeInput from '@/components/ui/js/RangeInput'
import LayuiFormGroup from '@/components/ui/layui/FormGroup.vue'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Layui_RangeInput',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  components: { LayuiFormGroup },
  setup (props: any, context: any) {
    const rangeinput = new RangeInput(props, context, useStore())

    const bodyCss = computed(() => {
      const arr: any = ['layui-pl-0 layui-border-0 layui-d-flex layui-align-items-center']
      if (props.uiconfig.meta?.css?.formSizing && props.uiconfig.meta?.css?.formSizing !== 'normal') {
        arr.push('layui-form-' + props.uiconfig.meta.css.formSizing)
      }
      if (props.uiconfig.meta?.form?.state === 'disabled') {
        arr.push('layui-disabled')
      }
      if (props.uiconfig.meta?.form?.state === 'readonly') {
        arr.push('layui-disabled')
      }
      return arr.join(' ')
    })
    const bodyStyle = computed(() => {
      const style = rangeinput.getUIStyle()
      const newStyle = {}
      for (const styleKey in style) {
        if (styleKey.match(/height/)) {
          newStyle[styleKey] = style[styleKey]
        }
      }
      return rangeinput.appendImportant(newStyle)
    })

    const rangeCss = computed(() => {
      const css: any = ['layui-form-control-range layui-w-100']
      if (props.uiconfig.meta?.css?.formSizing && props.uiconfig.meta?.css?.formSizing !== 'normal') {
        css.push('layui-form-control-range-' + props.uiconfig.meta.css.formSizing)
      }
      if (props.uiconfig.meta?.custom?.theme && props.uiconfig.meta?.custom?.theme !== 'default') {
        css.push('layui-range-' + props.uiconfig.meta.custom.theme)
      }
      return css.length > 0 ? css.join(' ') : ''
    })
    const rangeStyle = computed(() => {
      const style: any = []
      const background:any = []
      const backgroundSize:any = ['50%', '100%']
      const color = props.uiconfig.meta?.custom?.color
      const backgroundColor = props.uiconfig.meta?.custom?.backgroundColor
      if (color) {
        style.push(`border: 1px solid ${color} !important`)
        background.push(`-webkit-linear-gradient(${color}, ${color}) no-repeat`)
      }
      if (backgroundColor) background.push(backgroundColor)
      if (background.length > 0) style.push('background:' + background.join(',') + ' !important')

      const minValue = props.uiconfig.meta.custom?.min === undefined ? 1 : props.uiconfig.meta.custom?.min
      const defaultValue = props.uiconfig.meta.value === undefined ? 50 : props.uiconfig.meta.value
      const maxValue = props.uiconfig.meta.custom?.max === undefined ? 100 : props.uiconfig.meta.custom?.max
      backgroundSize[0] = defaultValue === 0 ? '0%' : ((defaultValue - minValue) / (maxValue - minValue) * 100) + '%'
      style.push('background-size:' + backgroundSize.join(' ') + '!important')
      // console.log(style.join(';'))
      return style.join(';')
    })
    return {
      ...rangeinput.setup(),
      bodyCss,
      bodyStyle,
      rangeCss,
      rangeStyle
    }
  }
}

</script>
