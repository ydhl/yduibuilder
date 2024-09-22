<template>
    <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
        :data-pageid="pageid" :class="['layui-box layui-laypage', dragableCss, uiCss]">
      <template v-for="page in totalPage" :key="page">
        <template v-if="page===1">
          <span class="layui-laypage-curr">
            <em :class="['layui-laypage-em', activeLinkCss]" :style="activeLinkStyle"></em>
            <em :style="foregroundStyle" :class="foregroundCss">{{page}}</em>
          </span>
        </template>
        <template v-else>
          <a href="javascript:;">{{page}}</a>
        </template>
      </template>
      <span class="layui-laypage-count">{{t('ui.pagi.total',[total])}}</span>
      <span class="layui-laypage-limits">
        <select lay-ignore="">
          <option selected>{{ pageSize }} {{ t('ui.pagi.itemPerPage') }}</option>
        </select>
      </span>
      <span class="layui-laypage-skip">{{ t('ui.pagi.goto') }}<input type="text" min="1" value="1" class="layui-input">{{ t('ui.pagi.page') }}<button type="button" class="layui-laypage-btn">{{ t('common.ok') }}</button></span>
    </div>
</template>

<script lang="ts">
import Pagination from '@/components/ui/js/Pagination'
import { useStore } from 'vuex'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

export default {
  name: 'Layui_Pagination',
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
    const { t } = useI18n()
    const uiStyle = computed(() => {
      const style = pagination.getUIStyle()
      delete style['background-color']
      delete style.color
      return pagination.appendImportant(style)
    })
    const activeLinkStyle = computed(() => {
      const style = pagination.getUIStyle()
      return style['background-color'] ? 'background-color:' + style['background-color'] + ' !important' : ''
    })
    const uiCss = computed(() => {
      const cssMap = pagination.getUICss()
      delete cssMap.backgroundTheme
      delete cssMap.foregroundTheme
      return Object.values(cssMap).join(' ')
    })
    const activeLinkCss = computed(() => {
      const cssMap = pagination.getUICss()
      const style = pagination.getUIStyle()
      if (!cssMap.backgroundTheme || style?.['background-color']) return ''
      return cssMap.backgroundTheme
    })
    const foregroundCss = computed(() => {
      const cssMap = pagination.getUICss()
      const style = pagination.getUIStyle()
      if (!cssMap.foregroundTheme || style?.color) return ''
      return cssMap.foregroundTheme
    })
    const foregroundStyle = computed(() => {
      const style = pagination.getUIStyle()
      return style?.color ? 'color:' + style.color + ' !important' : ''
    })
    const pageSize = computed(() => Math.max(parseInt(props.uiconfig.meta?.custom?.pageSize || 10), 1))
    const total = computed(() => Math.max(parseInt(props.uiconfig.meta?.custom?.total || 100), 1))
    const totalPage = computed(() => {
      return Math.ceil(total.value / pageSize.value)
    })
    return {
      ...pagination.setup(),
      t,
      totalPage,
      total,
      pageSize,
      uiCss,
      uiStyle,
      activeLinkCss,
      foregroundCss,
      foregroundStyle,
      activeLinkStyle
    }
  }
}
</script>
