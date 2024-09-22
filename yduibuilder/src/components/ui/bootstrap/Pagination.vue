<template>
  <nav :draggable='!inlineEditItemId' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid" :class="['d-flex gap-2 align-items-center', dragableCss, uiCss]">
    <ul :class="'pagination mb-0 pagination-'+size">
      <template  v-for="(page, index) in totalPage" :key="index">
        <li :class="{'page-item': true, [activeItemCss]: page===1}">
          <a :class="{[activeLinkCss]: page===1, [linkCss]: page!==1, 'page-link': true}" :style="page===1 ? activeLinkStyle : linkStyle" href="javascript:;">{{page}}</a>
        </li>
      </template>
    </ul>
    <div>{{t('ui.pagi.total', [total])}}</div>
    <select :class="'form-select form-select-' + size" style="width: 100px">
      <option>{{pageSize}} {{t('ui.pagi.itemPerPage')}}</option>
    </select>
    <div class="d-flex align-items-center gap-1">
      <input type="number" min="1" :class="'form-control form-control-' + size" style="width: 100px">
      <button type="button" :class="'btn btn-light btn-' + size">{{t('common.ok')}}</button>
    </div>
  </nav>
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
    const total = computed(() => Math.max(parseInt(props.uiconfig.meta?.custom?.total || 100), 1))
    const pageSize = computed(() => Math.max(parseInt(props.uiconfig.meta?.custom?.pageSize || 10), 1))
    const size = computed(() => props.uiconfig.meta?.css?.paginationSizing || '')
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
      const page = Math.ceil(total / size)
      const pages: any = []
      for (let i = 1; i <= Math.min(10, page); i++) {
        pages.push(i)
      }
      return pages
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
      activeItemCss,
      size,
      total,
      pageSize
    }
  }
}
</script>
