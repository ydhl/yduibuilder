<template>
  <LayuiFormGroup :uiconfig="uiconfig" :pageid="pageid"
                      :draggable='draggable' :dragableCss="dragableCss">
    <div :class="bodyCss" :style="bodyStyle">
      <textarea class="layui-input-custom" :style="uiconfig.meta.custom?.autoRow ? 'resize: none' : ''" :id="uiconfig.meta.id+uiconfig.type" :name="uiconfig.meta?.form?.inputName"
                :disabled="uiconfig.meta?.form?.state==='disabled'"
                :readonly="uiconfig.meta?.form?.state==='readonly'"
                :required="uiconfig.meta?.form?.required"
                :placeholder="uiconfig.meta?.form?.placeholder" :rows="uiconfig.meta.custom?.row" v-model="defaultValue"></textarea>
      <div v-if="uiconfig.meta?.custom?.wordCountVisible" class="layui-ml-3">0{{uiconfig.meta?.custom?.maxLength ? '/' + uiconfig.meta?.custom?.maxLength : ''}}</div>
      <div v-if="uiconfig.meta?.custom?.clearButtonVisible" class="layui-ml-3">×</div>
    </div>
  </LayuiFormGroup>
</template>

<script lang="ts">
import Textarea from '@/components/ui/js/Textarea'
import LayuiFormGroup from '@/components/ui/layui/FormGroup.vue'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Layui_Textarea',
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
    const textarea = new Textarea(props, context, useStore())
    const bodyCss = computed(() => {
      const arr = ['layui-textarea layui-d-flex layui-align-items-end']

      if (props.uiconfig.meta?.form?.state === 'disabled') {
        arr.push('layui-disabled')
      }
      if (props.uiconfig.meta?.form?.state === 'readonly') {
        arr.push('layui-disabled')
      }
      return arr
    })
    const bodyStyle = computed(() => {
      const style = textarea.getUIStyle()
      const newStyle = {}
      for (const styleKey in style) {
        if (styleKey.match(/height/)) {
          newStyle[styleKey] = style[styleKey]
        }
      }
      return textarea.appendImportant(newStyle)
    })

    return {
      ...textarea.setup(),
      bodyStyle,
      bodyCss
    }
  }
}

</script>
