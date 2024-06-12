<template>
  <nav :draggable='draggable'
       :class="['layui-breadcrumb', uiCss, dragableCss]" :style="uiStyle" :id="myId"
       :data-type="uiconfig.type"
       :data-pageid="pageid">
    <template v-for="(item, index) in values" :key="index" >
      <a href="javascript:;">
        <template v-if="!item.checked"><span :class="foreTheme" :style="foreStyle">{{item.text}}</span></template>
        <template v-if="item.checked"><cite :style="activeStyle">{{item.text}}</cite></template>
      </a>
      <span v-if="index!==values.length-1" lay-separator="/"  :class="foreTheme" :style="foreStyle">/</span>
    </template>
  </nav>
</template>

<script lang="ts">
import Breadcrumb from '../js/Breadcrumb'
import { computed } from 'vue'
import { useStore } from 'vuex'
import ydhl from '@/lib/ydhl'

export default {
  name: 'Layui_Breadcrumb',
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
    const breadcrumb = new Breadcrumb(props, context, store)

    const uiCss = computed(() => {
      const css = breadcrumb.getUICss()
      delete css.foregroundTheme
      return Object.values(css).join(' ')
    })
    const uiStyle = computed(() => {
      const style = breadcrumb.getUIStyle()
      style.visibility = 'visible'
      delete style.color
      return breadcrumb.appendImportant(style)
    })
    const foreTheme = computed(() => {
      const css = breadcrumb.getUICss()
      return css.foregroundTheme
    })
    const foreStyle = computed(() => {
      const style = breadcrumb.getUIStyle()
      return style.color ? `color:${style.color} !important` : ''
    })

    const activeStyle = computed(() => {
      const css = breadcrumb.getUICss()
      const style = breadcrumb.getUIStyle()
      if (!css.foregroundTheme && !style.color) return ''
      const color = style.color || store.getters.translate('themeColor', props.uiconfig.meta?.css?.foregroundTheme)
      if (!color) return
      const rgba = ydhl.getRgbaInfo(color)
      rgba.a = rgba.a * 0.70
      return `color: rgba(${rgba.r},${rgba.g},${rgba.b},${rgba.a}) !important`
    })
    return {
      ...breadcrumb.setup(),
      uiCss,
      uiStyle,
      foreTheme,
      foreStyle,
      activeStyle
    }
  }
}

</script>
