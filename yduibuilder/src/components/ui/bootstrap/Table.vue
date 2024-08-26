<template>
  <table :draggable='!inlineEditItemId' @dblclick="inlineEditItemId = uiconfig.meta.id"
         :class="['table', dragableCss, tableCss, uiCss]" :style="uiStyle" :id="myId" :data-type="uiconfig.type"
         :data-pageid="pageid" :data-isContainer="true">
    <thead v-if="inlineEditItemId==uiconfig.meta.id">
    <tr>
      <th class="bg-light" style="width: 20px"></th>
      <template v-for="column in columnCount" :key="column">
        <th class="bg-light" :style="size?.width?.[column-1] ? `width:${size.width[column-1]}px` : ''"
            @mouseenter="showToolbar($event, 'column', 0, column)">{{column}}</th>
      </template>
    </tr>
    </thead>
    <thead v-if="!uiconfig.meta.custom?.headless" :class="headerCss" :style="headerStyle">
      <tr v-for="row in headerRow" :key="row" :style="size?.height?.[row-1] ? `height:${size.height[row-1]}px` : ''">
        <th class="bg-light" v-if="inlineEditItemId==uiconfig.meta.id"  @mouseenter="showToolbar($event, 'row', row, 0)">{{row}}</th>
        <template v-for="column in columnCount" :key="column">
          <th v-if="!columnConfig?.[row-1]?.[column-1]?.isMerged"
              :style="size?.width?.[column-1] ? `width:${size.width[column-1]}px` : ''"
              :colspan="columnConfig?.[row-1]?.[column-1]?.colspan"
              :rowspan="columnConfig?.[row-1]?.[column-1]?.rowspan" :class="{[alignCss]: true, 'dragenter-subcontainer': isDragIn && dragoverInParent==`${row-1}-${column-1}`, 'subui': true}"
              :data-placeInParent="`${row-1}-${column-1}`"
              @mouseenter="inlineEditItemId==uiconfig.meta.id ? showToolbar($event, 'td', row, column) : null">
            <template v-if="!columnItems?.[row-1]?.[column-1]">{{rowData?.[row-1]?.[column-1]?.name || ''}}</template>
            <UIBase v-for="(item, index) in columnItems?.[row-1]?.[column-1]" :key="index"
                    :is-readonly="myIsReadonly || inlineEditItemId===uiconfig.meta.id" :is-lock="myIsLock || inlineEditItemId===uiconfig.meta.id"
                    :uiconfig="item" :pageid="pageid"></UIBase>
          </th>
        </template>
      </tr>
    </thead>
    <tbody>
    <tr v-for="row in bodyRow" :key="row" :style="size?.height?.[row-1+headerRow] ? `height:${size.height[row-1+headerRow]}px` : ''">
      <td class="bg-light" v-if="inlineEditItemId==uiconfig.meta.id"  @mouseenter="showToolbar($event, 'row', row+headerRow, 0)">{{row+headerRow}}</td>
      <template v-for="column in columnCount" :key="column">
        <td v-if="!columnConfig?.[row-1+headerRow]?.[column-1]?.isMerged"
            :style="size?.width?.[column-1] ? `width:${size.width[column-1]}px` : ''"
            :colspan="columnConfig?.[row-1+headerRow]?.[column-1]?.colspan"
            :rowspan="columnConfig?.[row-1+headerRow]?.[column-1]?.rowspan"
            :class="{[alignCss]: true, 'dragenter-subcontainer': isDragIn && dragoverInParent==`${row-1+headerRow}-${column-1}`, 'subui': true}"
            :data-placeInParent="`${row-1+headerRow}-${column-1}`"
            @mouseenter="inlineEditItemId==uiconfig.meta.id ? showToolbar($event, 'td', row+headerRow, column) : null">
          <template v-if="!columnItems?.[row-1+headerRow]?.[column-1]">{{rowData?.[row-1+headerRow]?.[column-1]?.name || ''}}</template>
          <UIBase v-for="(item, index) in columnItems?.[row-1+headerRow]?.[column-1]" :key="index"
                  :is-readonly="myIsReadonly || inlineEditItemId===uiconfig.meta.id" :is-lock="myIsLock || inlineEditItemId===uiconfig.meta.id"
                  :uiconfig="item" :pageid="pageid"></UIBase>
        </td>
      </template>
    </tr>
    </tbody>
    <tfoot v-if="!uiconfig.meta.custom?.footless" :class="footerCss" :style="footerStyle">
    <tr v-for="row in footerRow" :key="row" :style="size?.height?.[row-1+headerRow+bodyRow] ? `height:${size.height[row-1+headerRow+bodyRow]}px` : ''">
      <th class="bg-light" v-if="inlineEditItemId==uiconfig.meta.id"  @mouseenter="showToolbar($event, 'row', row+headerRow+bodyRow, 0)">{{row+headerRow+bodyRow}}</th>
      <template v-for="column in columnCount" :key="column">
        <th v-if="!columnConfig?.[row-1+headerRow+bodyRow]?.[column-1]?.isMerged"
            :colspan="columnConfig?.[row-1+headerRow+bodyRow]?.[column-1]?.colspan"
            :rowspan="columnConfig?.[row-1+headerRow+bodyRow]?.[column-1]?.rowspan"
            :class="{[alignCss]: true, 'dragenter-subcontainer': isDragIn && dragoverInParent==`${row-1+headerRow+bodyRow}-${column-1}`, 'subui': true}"
            :data-placeInParent="`${row-1+headerRow+bodyRow}-${column-1}`" @mouseenter="inlineEditItemId==uiconfig.meta.id ? showToolbar($event, 'td', row+headerRow+bodyRow, column) : null">
          <template v-if="!columnItems?.[row-1+headerRow+bodyRow]?.[column-1]">{{rowData?.[row-1+headerRow+bodyRow]?.[column-1]?.name || ''}}</template>
          <UIBase v-for="(item, index) in columnItems?.[row-1+headerRow+bodyRow]?.[column-1]" :key="index"
                  :is-readonly="myIsReadonly || inlineEditItemId===uiconfig.meta.id" :is-lock="myIsLock || inlineEditItemId===uiconfig.meta.id" :uiconfig="item" :pageid="pageid"></UIBase>
        </th>
      </template>
    </tr>
    </tfoot>
  </table>
  <div v-if="tableToolbarVisible" @mouseleave="closeToolbar" class="table-toolbar" :style="`${tableToolbarStyle}`">
    <div v-if="tableToolbarType=='column'" class="table-toolbar-column">
      <div class="split-tool-x" :data-target="tableToolbarKey"><i class="iconfont icon-resize-column"></i></div>
    </div>
    <div v-else-if="tableToolbarType=='row'" class="table-toolbar-row">
      <div class="split-tool-y" :data-target="tableToolbarKey"><i class="iconfont icon-resize-row"></i></div>
    </div>
    <div v-else-if="tableToolbarType=='td'" class="table-toolbar-td">
      <div class="tool" v-if="canSplitColumn" @click.stop.prevent="splitColumn"><i class="iconfont icon-split-column"></i></div>
      <div class="tool" v-if="canSplitRow" @click.stop.prevent="splitRow"><i class="iconfont icon-split-row"></i></div>
      <div class="tool" v-if="canSpanColumn" @click.stop.prevent="spanColumn"><i class="iconfont icon-span-column"></i></div>
      <div class="tool" v-if="canSpanRow" @click.stop.prevent="spanRow"><i class="iconfont icon-span-row"></i></div>
    </div>
  </div>
</template>

<script lang="ts">
import Table from '@/components/ui/js/Table'
import { computed } from 'vue'
import { useStore } from 'vuex'
import UIBase from '@/components/ui/UIBase.vue'

export default {
  name: 'Bootstrap_Table',
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
    const table = new Table(props, context, store)
    const tableSetup = table.setup()
    const backgroundTheme = computed(() => props.uiconfig.meta?.css?.backgroundTheme === 'default' ? '' : props.uiconfig.meta?.css?.backgroundTheme)
    const alignCss = computed(() => {
      const css = table.getUICss()
      const newCss = [css?.verticalAlignment, css?.textAlignment]
      return newCss.join(' ')
    })
    const uiCss = computed(() => {
      const css = table.getUICss()
      delete css?.textAlignment
      delete css?.verticalAlignment
      delete css?.backgroundTheme
      delete css?.foregroundTheme
      delete css?.header
      delete css?.footer

      // console.log(newCss)
      return Object.values(css).join(' ')
    })
    const tableCss = computed(() => {
      const css = table.getUICss()
      const newCss: any = {}
      // console.log(css)
      if (props.uiconfig.meta?.custom?.stripedRow) {
        newCss.striped = 'table-striped'
      }
      if (props.uiconfig.meta?.custom?.hoverableRow) {
        newCss.hoverable = 'table-hover'
      }
      if (props.uiconfig.meta?.custom?.small) {
        newCss.sm = 'table-sm'
      }
      newCss.rounded = 'overflow-hidden'
      if (props.uiconfig.meta?.custom?.grid === 'bordered') {
        newCss.grid = 'table-bordered'
      } else if (props.uiconfig.meta?.custom?.grid === 'borderless') {
        newCss.grid = 'table-borderless'
      }

      for (const cssKey in css) {
        if (cssKey === 'backgroundTheme' && backgroundTheme.value) {
          newCss[cssKey] = 'table-' + backgroundTheme.value
        } else if (cssKey.match(/^border/)) {
          newCss[cssKey] = css[cssKey]
        } else if (cssKey === 'foregroundTheme') {
          newCss[cssKey] = css[cssKey]
        }
      }
      // console.log(newCss)
      return Object.values(newCss).join(' ')
    })
    const uiStyle = computed(() => {
      const style = table.getUIStyle()
      style.overflow = 'hidden'
      style.margin = '0px'
      return table.appendImportant(style)
    })
    /**
     * Header的css和style
     */
    const headerStyle = computed(() => {
      const style = table.getUIStyle()
      if (props.uiconfig.meta?.custom?.header) {
        return `background-color:${props.uiconfig.meta?.custom?.header}`
      } else {
        return `background-color:${style['background-color']}`
      }
    })
    /**
     * Header TH的css和style
     */
    const headerCss = computed(() => {
      const css: any = []
      css.push(props.uiconfig.meta?.css?.header && props.uiconfig.meta?.css?.header !== 'default' ? 'table-' + props.uiconfig.meta.css.header : '')
      return css.join(' ')
    })
    /**
     * Footer的css和style
     */
    const footerStyle = computed(() => {
      const style = table.getUIStyle()
      if (props.uiconfig.meta?.custom?.footer) {
        return `background-color:${props.uiconfig.meta?.custom?.footer}`
      } else {
        return `background-color:${style['background-color']}`
      }
    })
    /**
     * Footer的css和style
     */
    const footerCss = computed(() => {
      const css: any = []
      css.push(props.uiconfig.meta?.css?.footer && props.uiconfig.meta?.css?.footer !== 'default' ? 'table-' + props.uiconfig.meta.css.footer : '')
      return css.join(' ')
    })

    return {
      ...tableSetup,
      uiCss,
      tableCss,
      uiStyle,
      headerCss,
      headerStyle,
      footerCss,
      footerStyle,
      alignCss
    }
  }
}

</script>
