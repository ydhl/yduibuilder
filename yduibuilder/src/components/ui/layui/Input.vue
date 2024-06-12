<template>
  <LayuiFormGroup :uiconfig="uiconfig" :pageid="pageid"
                      :draggable='draggable' :dragableCss="dragableCss">
    <div :class="bodyCss" :style="bodyStyle">
      <IconWrapper :uiconfig="uiconfig">
        <input :type="uiconfig.meta?.custom?.inputType || 'Text'" :id="uiconfig.meta.id+uiconfig.type" class="layui-input-custom"
               :name="uiconfig.meta?.form?.inputName" :placeholder="uiconfig.meta?.form?.placeholder"
               :disabled="uiconfig.meta?.form?.state==='disabled'"
               :readonly="uiconfig.meta?.form?.state==='readonly'"
               :required="uiconfig.meta?.form?.required"
               :value="uiconfig.meta.value">
      </IconWrapper>
      <div v-if="uiconfig.meta?.custom?.wordCountVisible" class="layui-ml-3">0{{uiconfig.meta?.custom?.maxLength ? '/' + uiconfig.meta?.custom?.maxLength : ''}}</div>
      <div v-if="uiconfig.meta?.custom?.clearButtonVisible" class="cursor layui-ml-3">×</div>
    </div>
  </LayuiFormGroup>
</template>

<script lang="ts">
import LayuiFormGroup from '@/components/ui/layui/FormGroup.vue'
import Input from '@/components/ui/js/Input'
import { computed } from 'vue'
import IconWrapper from '@/components/ui/bootstrap/IconWrapper.vue'
import { useStore } from 'vuex'

export default {
  name: 'Layui_Input',
  components: { IconWrapper, LayuiFormGroup },
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const input = new Input(props, context, useStore())
    const bodyCss = computed(() => {
      const arr: any = ['layui-input layui-pl-1 layui-pr-1 layui-d-flex layui-align-items-center']
      if (props.uiconfig.meta?.css?.formSizing && props.uiconfig.meta?.css?.formSizing !== 'normal') {
        arr.push('layui-form-' + props.uiconfig.meta.css.formSizing)
      }
      if (props.uiconfig.meta?.form?.state === 'disabled') {
        arr.push('disabled')
      }
      if (props.uiconfig.meta?.form?.state === 'readonly') {
        arr.push('readonly')
      }
      return arr
    })
    const bodyStyle = computed(() => {
      const style = input.getUIStyle()
      const newStyle = {}
      for (const styleKey in style) {
        if (styleKey.match(/height/)) {
          newStyle[styleKey] = style[styleKey]
        }
      }
      return input.appendImportant(newStyle)
    })
    return {
      ...input.setup(),
      bodyCss,
      bodyStyle
    }
  }
}

</script>
