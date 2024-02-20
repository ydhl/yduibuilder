<template>
    <ol :draggable='draggable'
        :class="['breadcrumb', uiCss, dragableCss]" :style="uiStyle" :id="myId"
        :data-type="uiconfig.type"
        :data-pageid="pageid">
      <li :class="{'breadcrumb-item': true, 'active':item.checked}"
          v-for="(item, index) in values" :key="index">
        <a v-if="!item.checked" :href="item.value" :class="foregroundCss" :style="foregroundStyle">{{item.text}}</a>
        <template v-if="item.checked">{{item.text}}</template>
      </li>
    </ol>
</template>

<script lang="ts">
import Breadcrumb from '../js/Breadcrumb'
import { computed } from 'vue'

export default {
  name: 'Bootstrap_Breadcrumb',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const breadcrumb = new Breadcrumb(props, context)
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
        newStyle.push(`color:${style.color} !important`)
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
