<template>
  <div :draggable='draggable'
      :class="['van-tabs van-tabs--card',dragableCss, uiCss]"
      :style="uiStyle" :id="myId" :data-type="uiconfig.type"
      :data-pageid="pageid">
    <div class="van-tabs__wrap">
      <div class="van-tabs__nav van-tabs__nav--card" :style="borderStyle">
        <div :class="['van-tab van-tab--card',item.checked ? activeItemCss : itemCss]" v-for="(item, index) in values"
            :key="index" :style="item.checked ? activeItemStyle : itemStyle">
          <span class="van-tab__text van-tab__text--ellipsis">{{item.text}}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import Nav from '@/components/ui/js/Nav'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Vant_Nav',
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
      delete cssMap.backgroundTheme
      const css: any = Object.values(cssMap)
      return css.length > 0 ? css.join(' ') : ''
    })
    const uiStyle = computed(() => {
      const style = nav.getUIStyle()
      delete style.color
      delete style['background-color']
      return nav.appendImportant(style)
    })

    const itemCss = computed(() => {
      const style = nav.getUIStyle()
      const foreTheme = props.uiconfig?.meta?.css?.foregroundTheme
      const backTheme = props.uiconfig?.meta?.css?.backgroundTheme
      const _:any = []
      // 定义了style 则忽略css
      if (!style?.color && foreTheme && foreTheme !== 'default') {
        _.push(store.getters.translate('foregroundTheme', foreTheme))
      }
      if (!style?.['background-color'] && backTheme && backTheme !== 'default') {
        _.push(store.getters.translate('backgroundTheme', backTheme))
      }
      return _.join(' ')
    })
    const activeItemCss = computed(() => {
      const style = nav.getUIStyle()
      if (style?.color) return '' // 有自定义颜色，则忽略预定义样式
      const theme = props.uiconfig?.meta?.css?.foregroundTheme
      if (!theme || theme === 'default') return 'van-tab--active'
      return store.getters.translate('backgroundTheme', theme) + ' van-text-white'
    })
    const itemStyle = computed(() => {
      const style = nav.getUIStyle()
      const _:any = []
      if (style.color) {
        _.push(`color:${style.color} !important;`)
      }
      if (style?.['background-color']) {
        _.push(`background-color:${style['background-color']} !important;`)
      }
      _.push(borderStyle.value)
      return _.join(';')
    })
    const activeItemStyle = computed(() => {
      const style = nav.getUIStyle()
      return (style.color ? `background-color:${style.color} !important;color:#fff;` : '') + borderStyle.value
    })
    const borderStyle = computed(() => {
      const style = nav.getUIStyle()
      if (style?.color) return `border-color: ${style?.color}`
      const theme = props.uiconfig?.meta?.css?.foregroundTheme
      if (!theme || theme === 'default') return ''
      return 'border-color: ' + store.getters.translate('themeColor', theme)
    })
    return {
      ...nav.setup(),
      uiCss,
      uiStyle,
      activeItemCss,
      activeItemStyle,
      itemStyle,
      itemCss,
      borderStyle
    }
  }
}

</script>
