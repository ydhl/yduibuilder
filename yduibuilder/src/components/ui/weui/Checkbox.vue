<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, uiCss,{'overflow-hidden w-100 weui-cells weui-cells_checkbox':true, 'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <template v-for="(item, index) in values" :key="index" >
      <label class="weui-cell weui-cell_active weui-check__label" style="color: inherit !important;" >
        <div class="weui-cell__hd">
          <input type="checkbox" :checked="item.checked" class="weui-check"
                 :id="uiconfig.meta.id+item.value"
                 :disabled="uiconfig.meta?.form?.state==='disabled'"
                 :value="item.value" :name="uiconfig.meta?.form?.inputName">
          <i :class="iconClass" :style="iconStyle"></i>
        </div>
        <div class="weui-cell__bd">
          <p>{{item.name}}</p>
        </div>
      </label>
    </template>
  </div>
</template>

<script lang="ts">
import Checkbox from '@/components/ui/js/Checkbox'
import { useStore } from 'vuex'
import { computed } from 'vue'

export default {
  name: 'Weui_Checkbox',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const checkbox = new Checkbox(props, context, useStore())
    const store = useStore()
    const iconStyle = computed(() => {
      const uiStyle = checkbox.getUIStyle()
      const style: any = []
      if (uiStyle.color) {
        style.push(`color:${uiStyle.color}`)
      }
      return style.join(';')
    })
    const iconClass = computed(() => {
      const css: any = ['weui-icon-checked']
      const foregroundTheme = props.uiconfig.meta?.css?.foregroundTheme
      const color = props.uiconfig.meta?.style?.color
      if (!color && foregroundTheme && foregroundTheme !== 'default') {
        css.push(store.getters.translate('foregroundTheme', foregroundTheme))
      }
      return css.join(' ')
    })
    return {
      ...checkbox.setup(),
      iconStyle,
      iconClass
    }
  }
}

</script>
