<template>
  <LayuiFormGroup :uiconfig="uiconfig" :pageid="pageid"
                      :draggable='draggable' :dragableCss="dragableCss">
    <div :class="bodyCss" :style="bodyStyle">
      <input type="file" :id="uiconfig.meta.id+uiconfig.type"
             :disabled="uiconfig.meta?.form?.state==='disabled'"
             :readonly="uiconfig.meta?.form?.state==='readonly'"
             :required="uiconfig.meta?.form?.required"
             :name="uiconfig.meta?.form?.inputName"
             :accept="uiconfig.meta?.custom?.accept || '*/*'"
             :multiple="uiconfig.meta?.custom?.multiple">
    </div>
  </LayuiFormGroup>
</template>

<script lang="ts">
import LayuiFormGroup from '@/components/ui/layui/FormGroup.vue'
import File from '@/components/ui/js/File'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Layui_File',
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
    const file = new File(props, context, useStore())
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
      const style = file.getUIStyle()
      const newStyle = {}
      for (const styleKey in style) {
        if (styleKey.match(/^height/)) {
          newStyle[styleKey] = style[styleKey]
        }
      }
      return file.appendImportant(newStyle)
    })
    return {
      ...file.setup(),
      bodyCss,
      bodyStyle
    }
  }
}

</script>
