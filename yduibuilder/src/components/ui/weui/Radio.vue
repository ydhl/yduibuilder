<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, uiCss,{'overflow-hidden w-100 weui-cells weui-cells_radio':true, 'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <template v-for="(item, index) in values" :key="index" >
      <div class="weui-cell weui-cell_active weui-check__label" style="color: inherit !important;"  :for="uiconfig.meta.id+item.value">
        <div class="weui-cell__bd">
          <p>{{item.name}}</p>
        </div>
        <div class="weui-cell__ft">
          <input type="radio" :checked="item.checked" class="weui-check"
                 :id="uiconfig.meta.id+item.value"
                 :disabled="uiconfig.meta?.form?.state==='disabled'"
                 :value="item.value" :name="uiconfig.meta?.form?.inputName">
          <i v-if="item.checked" :class="iconClass" :style="iconStyle"></i>
        </div>
      </div>
    </template>
  </div>
</template>

<script lang="ts">
import Radio from '@/components/ui/js/Radio'
import { useStore } from 'vuex'
import { computed } from 'vue'

export default {
  name: 'Weui_Radio',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const radio = new Radio(props, context, useStore())
    const store = useStore()

    const iconStyle = computed(() => {
      const uiStyle = radio.getUIStyle()
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
      ...radio.setup(),
      iconClass,
      iconStyle
    }
  }
}

</script>
