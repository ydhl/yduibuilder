<template>
  <div :draggable='draggable' :style="myStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, myCss,{'overflow-hidden':true, 'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <select :size="size" :multiple="isMultiple" :placeholder="uiconfig.meta?.form?.placeholder"
            :disabled="uiconfig.meta?.form?.state==='disabled'"
            :readonly="uiconfig.meta?.form?.state==='readonly'"
            :required="uiconfig.meta?.form?.required"
            :id="uiconfig.meta.id+uiconfig.type" :class="bodyCss" :style="bodyStyle" :name="uiconfig.meta?.form?.inputName">
      <option v-for="(item, index) in values" :selected="item.checked" :key="index" :value="item.value">{{item.name}}</option>
    </select>
  </div>
</template>

<script lang="ts">
import Select from '@/components/ui/js/Select'
import { computed } from 'vue'
import { useStore } from 'vuex'

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
    const select = new Select(props, context, useStore())
    const store = useStore()
    const myCss = computed(() => {
      const css = select.getUICss()
      delete css.formSizing
      delete css.backgroundTheme
      return Object.values(css).join(' ')
    })
    const myStyle = computed(() => {
      const style = select.getUIStyle()
      delete style['background-color']
      return select.appendImportant(style)
    })
    const bodyCss = computed(() => {
      const arr: any = ['form-select']
      if (props.uiconfig.meta?.css?.formSizing && props.uiconfig.meta?.css?.formSizing !== 'normal') {
        arr.push('form-select-' + props.uiconfig.meta.css.formSizing)
      }
      const backgroundTheme = props.uiconfig.meta?.css?.backgroundTheme
      if (backgroundTheme) {
        arr.push(store.getters.translate('backgroundTheme', backgroundTheme))
      }
      return arr
    })
    const bodyStyle = computed(() => {
      const style = select.getUIStyle()
      const newStyle = { font: 'inherit', color: 'inherit' }
      for (const styleKey in style) {
        if (styleKey.match(/height/)) {
          newStyle[styleKey] = style[styleKey]
        }
      }
      if (style['background-color']) {
        newStyle['background-color'] = style['background-color']
      }
      return select.appendImportant(newStyle)
    })
    return {
      ...select.setup(),
      bodyStyle,
      myStyle,
      myCss,
      bodyCss
    }
  }
}

</script>
