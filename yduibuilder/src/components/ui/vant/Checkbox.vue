<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, bodyCss, uiCss,{'overflow-hidden':true, 'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <div class="van-checkbox-group van-checkbox-group--horizontal" v-for="(item, index) in values" :key="index">
      <div class="van-checkbox van-checkbox--horizontal">
        <div :class="{'van-checkbox__icon van-checkbox__icon--square': true, 'van-radio__icon--checked':item.checked}">
          <i :class="['van-badge__wrapper van-icon van-icon-success', item.checked ? checkedTheme : uncheckedTheme]" :style="item.checked ? checkedStyle : uncheckedStyle"></i>
        </div>
        <span class="van-radio__label">{{item.name}}</span>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import Checkbox from '@/components/ui/js/Checkbox'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Vant_Checkbox',
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
    const checkedStyle = computed(() => {
      if (props.uiconfig.meta?.form?.state === 'readonly' || props.uiconfig.meta?.form?.state === 'disabled') {
        return 'border-color:var(--van-gray-6);background-color:var(--van-gray-6)'
      }
      const uistyle = checkbox.getUIStyle()
      if (uistyle.color) {
        return `border-color:${uistyle.color};background-color:${uistyle.color}`
      }
      return ''
    })
    const uncheckedStyle = computed(() => {
      const uistyle = checkbox.getUIStyle()
      if (props.uiconfig.meta?.form?.state === 'readonly' || props.uiconfig.meta?.form?.state === 'disabled') {
        return 'border-color:var(--van-gray-6);'
      }
      if (uistyle.color) {
        return `border-color:${uistyle.color};`
      }
      return ''
    })
    const checkedTheme = computed(() => {
      const style = checkbox.getUIStyle()
      const css = checkbox.getUICss()
      if (!style.color && css.foregroundTheme) {
        const css: any = []
        css.push(store.getters.translate('backgroundTheme', props.uiconfig.meta?.css?.foregroundTheme))
        css.push(store.getters.translate('borderColorClass', props.uiconfig.meta?.css?.foregroundTheme))
        return css.join(' ')
      }
      return ''
    })
    const uncheckedTheme = computed(() => {
      const style = checkbox.getUIStyle()
      const css = checkbox.getUICss()
      if (!style.color && css.foregroundTheme) {
        const css: any = []
        css.push(store.getters.translate('borderColorClass', props.uiconfig.meta?.css?.foregroundTheme))
        return css.join(' ')
      }
      return ''
    })

    const bodyCss = computed(() => {
      const arr: any = []

      arr.push('van-h-auto')
      return arr
    })
    return {
      ...checkbox.setup(),
      bodyCss,
      checkedTheme,
      uncheckedTheme,
      uncheckedStyle,
      checkedStyle
    }
  }
}

</script>
