<template>
  <FormGroup
    :uiconfig="uiconfig" :pageid="pageid"
    :draggable='draggable' :dragableCss="dragableCss">
    <select :multiple="isMultiple" :placeholder="uiconfig.meta?.form?.placeholder"
            :disabled="uiconfig.meta?.form?.state==='disabled'"
            :readonly="uiconfig.meta?.form?.state==='readonly'"
            :required="uiconfig.meta?.form?.required"
            :id="uiconfig.meta.id+uiconfig.type"
            :style="bodyStyle"
            :class="bodyCss" :name="uiconfig.meta?.form?.inputName">
      <option v-for="(item, index) in uiconfig.meta.values" :selected="item.checked" :key="index" :value="item.value">{{item.text}}</option>
    </select>
  </FormGroup>
</template>

<script lang="ts">
import Select from '@/components/ui/js/Select'
import FormGroup from '@/components/ui/weui/FormGroup.vue'
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
  components: { FormGroup },
  setup (props: any, context: any) {
    const select = new Select(props, context, useStore())
    const bodyCss = computed(() => {
      const css = select.getUICss()
      const arr: any = ['weui-select pl-0']
      if (css.foregroundTheme) arr.push(css.foregroundTheme)
      return arr
    })
    const bodyStyle = computed(() => {
      const style: any = {}
      const baseStyle = select.getUIStyle()
      style['border-top'] = '1px solid #e5e5e5'
      style['border-bottom'] = '1px solid #e5e5e5'
      if (baseStyle?.color) style.color = baseStyle?.color
      return select.appendImportant(style)
    })
    return {
      ...select.setup(),
      bodyCss,
      bodyStyle
    }
  }
}

</script>
