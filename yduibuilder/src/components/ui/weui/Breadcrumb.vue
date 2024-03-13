<template>
    <ol :draggable='draggable'
        :class="['weui-breadcrumb', uiCss, dragableCss]" :style="uiStyle" :id="myId"
        :data-type="uiconfig.type"
        :data-pageid="pageid">
      <li :class="{'weui-breadcrumb-item': true, 'active':item.checked}"
          v-for="(item, index) in values" :key="index">
        <span v-if="!item.checked" :class="foregroundCss" :style="foregroundStyle">{{item.text}}</span>
        <template v-if="item.checked">{{item.text}}</template>
      </li>
    </ol>
</template>

<script lang="ts">
import Breadcrumb from '../js/Breadcrumb'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Weui_Breadcrumb',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const breadcrumb = new Breadcrumb(props, context, useStore())
    const foregroundCss = computed(() => {
      const css: any = []
      const cssMap = breadcrumb.getUICss()
      if (cssMap?.foregroundTheme) {
        css.push(cssMap?.foregroundTheme)
      }
      return css.join(' ')
    })
    const foregroundStyle = computed(() => {
      const style = breadcrumb.getUIStyle()
      const newStyle: any = []
      if (style?.color) {
        newStyle.push(`color:${style.color} !important;`)
      }
      return newStyle.join(';')
    })
    const uiCss = computed(() => {
      const cssMap = breadcrumb.getUICss()
      delete cssMap?.foregroundTheme
      return Object.values(cssMap).join(' ')
    })
    const uiStyle = computed(() => {
      const style = breadcrumb.getUIStyle()
      delete style.color
      return breadcrumb.appendImportant(style)
    })
    return {
      ...breadcrumb.setup(),
      uiCss,
      uiStyle,
      foregroundCss,
      foregroundStyle
    }
  }
}
</script>
