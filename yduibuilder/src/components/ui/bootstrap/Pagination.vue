<template>
    <ul :draggable='!inlineEditItemId' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
        :data-pageid="pageid" :class="['pagination', dragableCss, uiCss]">
      <li class="page-item disabled">
        <a :class="['page-link']" href="#" tabindex="-1" aria-disabled="true">Previous</a>
      </li>
      <li class="page-item">
        <a :class="[linkCss, 'page-link']" :style="linkStyle" href="#">1</a>
      </li>
      <li :class="['page-item', activeItemCss]">
        <a :class="[activeLinkCss, 'page-link']" :style="activeLinkStyle" href="#">2</a>
      </li>
      <li class="page-item">
        <a :class="[linkCss, 'page-link']" :style="linkStyle" href="#">3</a>
      </li>
      <li class="page-item">
        <a :class="[linkCss, 'page-link']" :style="linkStyle" href="#">Next</a>
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
      if (!cssMap.backgroundTheme) return ''
      const backgroundTheme = props.uiconfig.meta?.css?.backgroundTheme
      return cssMap.backgroundTheme + ' text-light ' + store.getters.translate('borderColorClass', backgroundTheme)
    })
    const activeItemCss = computed(() => {
      const cssMap = pagination.getUICss()
      if (!cssMap.backgroundTheme) return 'active'
      return ''
    })
    return {
      ...pagination.setup(),
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
