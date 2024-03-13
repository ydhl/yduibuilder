<template>
  <div :draggable='draggable'
      :class="['nav',dragableCss, uiCss, navTypeCss,
      { 'nav-justified': uiconfig.meta.custom?.justified,
      'nav-fill': uiconfig.meta.custom?.filled,
      'card-header-tabs': parentIsCard && uiconfig.meta.custom?.type==='tab',
      'card-header-pills': parentIsCard && uiconfig.meta.custom?.type==='pill'}]"
      :style="uiStyle" :id="myId" :data-type="uiconfig.type"
      :data-isContainer="true"
      :data-pageid="pageid">
    <div :class="[{'nav-item pointer-event-none': true}]" v-for="(item, index) in values"
        :key="index"><a :class="[{'nav-link':true}, (item.checked ? activeItemCss : itemCss)]" :style="item.checked ? activeItemStyle : itemStyle" href="#">{{item.text}}</a></div>
    <UIBase v-for="(item, index) in uiconfig.items" :key="index" :is-readonly="myIsReadonly" :is-lock="myIsLock" :uiconfig="item" :pageid="pageid"></UIBase>
  </div>
</template>

<script lang="ts">
import UIBase from '@/components/ui/UIBase.vue'
import Nav from '@/components/ui/js/Nav'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Bootstrap_Nav',
  components: { UIBase },
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
    const navTypeCss = computed(() => {
      if (props.uiconfig.meta.custom?.type === 'tab') return 'nav-tabs'
      if (props.uiconfig.meta.custom?.type === 'pill') return 'nav-pills'
      return ''
    })
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
      let theme = props.uiconfig?.meta?.css?.foregroundTheme
      theme = theme && theme !== 'default' ? theme : 'primary'
      // 转成对应都前景主题
      return store.getters.translate('foregroundTheme', theme)
    })
    const activeItemCss = computed(() => {
      const style = nav.getUIStyle()
      if (style?.color) return '' // 有自定义颜色，则忽略预定义样式
      let theme = props.uiconfig?.meta?.css?.foregroundTheme
      theme = theme && theme !== 'default' ? theme : 'primary'
      return store.getters.translate('backgroundTheme', theme) + ' text-white'
    })
    const itemStyle = computed(() => {
      const style = nav.getUIStyle()
      return `color:${style.color} !important`
    })
    const activeItemStyle = computed(() => {
      const style = nav.getUIStyle()
      return `background-color:${style.color} !important;color:#fff;`
    })
    return {
      ...nav.setup(),
      uiCss,
      navTypeCss,
      uiStyle,
      activeItemCss,
      activeItemStyle,
      itemStyle,
      itemCss
    }
  }
}

</script>
