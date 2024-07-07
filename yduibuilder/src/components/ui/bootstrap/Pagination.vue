<template>
    <ul :draggable='!inlineEditItemId' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
        :data-pageid="pageid" :class="['pagination', dragableCss, uiCss]">
      <li :class="{'page-item': true, [activeItemCss]: page===1}" v-for="page in totalPage" :key="page">
        <a :class="{[activeLinkCss]: page===1, [linkCss]: page!==1, 'page-link': true}" :style="page===1 ? activeLinkStyle : linkStyle" href="javascript:;">{{page}}</a>
      </li>
    </ul>
</template>

<script lang="ts">
import Pagination from '@/components/ui/js/Pagination'
import { computed } from 'vue'
import store from '@/store'
import { useStore } from 'vuex'

export default {
  name: 'Bootstrap_Pagination',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const pagination = new Pagination(props, context, useStore())
    const uiStyle = computed(() => {
      const style = pagination.getUIStyle()
      delete style['background-color']
      delete style.color
      return pagination.appendImportant(style)
    })
    const activeLinkStyle = computed(() => {
      const style = pagination.getUIStyle()
      return style['background-color'] ? 'border-color:' + style['background-color'] + ' !important;background-color:' + style['background-color'] + ' !important' : ''
    })
    const linkStyle = computed(() => {
      const style = pagination.getUIStyle()
      return style.color ? 'color:' + style.color + ' !important' : ''
    })
    const uiCss = computed(() => {
      const cssMap = pagination.getUICss()
      delete cssMap.backgroundTheme
      delete cssMap.foregroundTheme
      return Object.values(cssMap).join(' ')
    })
    const linkCss = computed(() => {
      const cssMap = pagination.getUICss()
      return cssMap.foregroundTheme
    })
    const activeLinkCss = computed(() => {
      const cssMap = pagination.getUICss()
      const style = pagination.getUIStyle()
      if (!cssMap.backgroundTheme || style?.['background-color']) return ''
      const backgroundTheme = props.uiconfig.meta?.css?.backgroundTheme
      return cssMap.backgroundTheme + ' text-light ' + store.getters.translate('borderColorClass', backgroundTheme)
    })
    const activeItemCss = computed(() => {
      const cssMap = pagination.getUICss()
      if (!cssMap.backgroundTheme) return 'active'
      return ''
    })
    const totalPage = computed(() => {
      const total = Math.max(parseInt(props.uiconfig.meta?.custom?.total || 100), 1)
      const size = Math.max(parseInt(props.uiconfig.meta?.custom?.pageSize || 10), 1)
      return Math.ceil(total / size)
    })
    return {
      ...pagination.setup(),
      totalPage,
      uiCss,
      uiStyle,
      linkCss,
      activeLinkCss,
      activeLinkStyle,
      linkStyle,
      activeItemCss
    }
  }
}
</script>
