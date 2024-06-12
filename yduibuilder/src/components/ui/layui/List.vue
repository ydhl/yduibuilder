<template>
  <ul :draggable='draggable' :class="[dragableCss, uiCss,'layui-list-group']"
      :style="uiStyle" :id="myId" :data-type="uiconfig.type"
      :data-pageid="pageid">
    <li :class="[{'layui-list-group-item': true}, itemClass]" :style="`${itemStyle};${item.checked ? activeItemStyle : ''}`" v-for="(item, index) in values"
        :key="index" :value="item.value">{{item.text}}</li>
  </ul>
</template>

<script lang="ts">
import List from '@/components/ui/js/List'
import { computed } from 'vue'
import ydhl from '@/lib/ydhl'
import { useStore } from 'vuex'

export default {
  name: 'Layui_List',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const store = useStore()
    const list = new List(props, context, store)
    const uiStyle = computed(() => {
      const style = list.getUIStyle()
      delete style.color
      delete style['background-color']
      return list.appendImportant(style)
    })
    const uiCss = computed(() => {
      const css = list.getUICss()
      delete css.backgroundTheme
      delete css.foregroundTheme
      return Object.values(css).join(' ')
    })
    const itemClass = computed(() => {
      const css: any = []
      const backgroundTheme = props.uiconfig.meta?.css?.backgroundTheme
      const cssMap = list.getUICss()
      if (backgroundTheme && backgroundTheme !== 'default') {
        css.push('layui-list-group-item-' + backgroundTheme)
      }
      if (cssMap.foregroundTheme) {
        css.push(cssMap.foregroundTheme)
      }
      return css.join(' ')
    })
    const itemStyle = computed(() => {
      const style: any = []
      const styleMap = list.getUIStyle()
      if (styleMap.color) {
        style.push(`color: ${styleMap.color} !important`)
      }
      if (styleMap['background-color']) {
        style.push(`background-color: ${styleMap['background-color']} !important`)
      }
      return style.join(';')
    })
    const activeItemStyle = computed(() => {
      const style = list.getUIStyle()
      const newStyle: any = []

      let bgcolor = style?.['background-color']
      if (!bgcolor) {
        const backgroundTheme = props.uiconfig.meta?.css?.backgroundTheme
        if (backgroundTheme && backgroundTheme !== 'default') {
          bgcolor = store.getters.translate('themeColor', backgroundTheme)
        }
      }
      if (bgcolor) {
        const rgba = ydhl.getRgbaInfo(bgcolor)
        rgba.a *= 0.75
        newStyle.push(`background-color:rgba(${rgba.r},${rgba.g},${rgba.b},${rgba.a}) !important`)
      }
      return newStyle.join(';')
    })

    return {
      ...list.setup(),
      itemClass,
      itemStyle,
      activeItemStyle,
      uiStyle,
      uiCss
    }
  }
}

</script>
