<template>
  <ul :draggable='draggable' :class="[dragableCss, uiCss, 'list-group']"
      :style="uiStyle" :id="myId" :data-type="uiconfig.type"
      :data-pageid="pageid">
    <li :class="[{'active': item.checked},itemTheme, item.checked ? activeItemTheme : '']" v-for="(item, index) in values"
        :key="index" :value="item.value" :style="`${itemStyle}; ${item.checked ? activeItemStyle : ''}`">{{item.text}}</li>
  </ul>
</template>

<script lang="ts">
import List from '@/components/ui/js/List'
import { computed } from 'vue'
import { useStore } from 'vuex'
import ydhl from '@/lib/ydhl'

export default {
  name: 'Bootstrap_List',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const list = new List(props, context)
    const store = useStore()
    const uiCss = computed(() => {
      const css = list.getUICss()
      delete css.backgroundTheme
      delete css.foregroundTheme
      return Object.values(css).join(' ')
    })
    const uiStyle = computed(() => {
      const style = list.getUIStyle()
      delete style?.color
      delete style?.['background-color']
      return list.appendImportant(style)
    })
    const itemTheme = computed(() => {
      const css: any = ['list-group-item']
      const cssMap = list.getUICss()
      const backgroundTheme = props.uiconfig.meta?.css?.backgroundTheme
      if (backgroundTheme && backgroundTheme !== 'default') css.push('list-group-item-' + backgroundTheme)
      if (cssMap?.foregroundTheme) css.push(cssMap?.foregroundTheme)
      return css.join(' ')
    })
    const activeItemTheme = computed(() => {
      const cssMap = list.getUICss()
      const arr: any = []
      if (cssMap?.backgroundTheme) {
        arr.push(cssMap?.backgroundTheme)
        arr.push(store.getters.translate('borderColorClass', props.uiconfig.meta?.css?.backgroundTheme))
      }
      return arr.join(' ')
    })
    const activeItemStyle = computed(() => {
      const style = list.getUIStyle()
      const newStyle: any = []
      const bgcolor = style?.['background-color']
      if (bgcolor) {
        const rgba = ydhl.getRgbaInfo(bgcolor)
        rgba.a *= 0.75
        newStyle.push(`background-color:rgba(${rgba.r},${rgba.g},${rgba.b},${rgba.a}) !important`)
      }
      return newStyle.join(';')
    })
    const itemStyle = computed(() => {
      const style = list.getUIStyle()
      const newStyle: any = []
      if (style?.color) {
        newStyle.push(`color:${style.color} !important`)
      }
      if (style?.['background-color']) {
        newStyle.push(`background-color:${style?.['background-color']} !important`)
        newStyle.push(`border-color:${style?.['background-color']} !important`)
      }
      return newStyle.join(';')
    })
    return {
      ...list.setup(),
      itemTheme,
      activeItemTheme,
      activeItemStyle,
      itemStyle,
      uiStyle,
      uiCss
    }
  }
}

</script>
