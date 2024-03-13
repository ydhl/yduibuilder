<template>
  <div :draggable='draggable' :class="[dragableCss, uiCss, 'weui-cells']"
      :style="uiStyle" :id="myId" :data-type="uiconfig.type"
      :data-pageid="pageid">
    <div :class="['weui-cell', itemTheme]" v-for="(item, index) in values"
        :key="index" :value="item.value" :style="`${itemStyle}`">
      <span class="weui-cell__bd">
        <span>{{item.text}}</span>
      </span>
      <span v-if="item.checked" class="weui-cell__ft"><i class="weui-icon-success"></i></span>
    </div>
</div>
</template>

<script lang="ts">
import List from '@/components/ui/js/List'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Weui_List',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const list = new List(props, context, useStore())
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
      const css: any = ['']
      const cssMap = list.getUICss()
      if (cssMap?.backgroundTheme) css.push(cssMap?.backgroundTheme)
      if (cssMap?.foregroundTheme) css.push(cssMap?.foregroundTheme)
      return css.join(' ')
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
      itemStyle,
      uiStyle,
      uiCss
    }
  }
}

</script>
