<template>
  <div :draggable='draggable'
      :class="['weui-navbar',dragableCss, uiCss]"
      :style="uiStyle" :id="myId" :data-type="uiconfig.type"
      :data-pageid="pageid">
    <div :class="['weui-navbar__item', item.checked ? activeItemCss : itemCss]" v-for="(item, index) in values"
        :key="index" :style="item.checked ? activeItemStyle : itemStyle">{{item.text}}</div>
  </div>
</template>

<script lang="ts">
import Nav from '@/components/ui/js/Nav'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Weui_Nav',
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
    const nav = new Nav(props, context, store)
    const uiCss = computed(() => {
      const cssMap = nav.getUICss()
      delete cssMap.foregroundTheme
      const css: any = Object.values(cssMap)
      return css.length > 0 ? css.join(' ') : ''
    })
    const uiStyle = computed(() => {
      const style = nav.getUIStyle()
      delete style.color
      return nav.appendImportant(style)
    })

    const itemCss = computed(() => {
      const style = nav.getUIStyle()
      if (style?.color) return '' // 有自定义颜色，则忽略预定义样式
      const theme = props.uiconfig?.meta?.css?.foregroundTheme
      if (!theme || theme === 'default') return 'text-dark'
      // 转成对应都前景主题
      return store.getters.translate('foregroundTheme', theme)
    })
    const activeItemCss = computed(() => {
      const style = nav.getUIStyle()
      if (style?.color) return '' // 有自定义颜色，则忽略预定义样式
      const theme = props.uiconfig?.meta?.css?.foregroundTheme
      if (!theme || theme === 'default') return 'bg-light text-dark'
      return store.getters.translate('backgroundTheme', theme) + ' text-white'
    })
    const itemStyle = computed(() => {
      const style = nav.getUIStyle()
      return style.color ? `color:${style.color} !important` : ''
    })
    const activeItemStyle = computed(() => {
      const style = nav.getUIStyle()
      return style.color ? `background-color:${style.color} !important;color:#fff;` : ''
    })
    return {
      ...nav.setup(),
      uiCss,
      uiStyle,
      activeItemCss,
      activeItemStyle,
      itemStyle,
      itemCss
    }
  }
}

</script>
