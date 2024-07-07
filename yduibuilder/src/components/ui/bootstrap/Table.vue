<template>
  <div :draggable='draggable'
       :class="[dragableCss, uiCss]" :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid">
    <table  :class="['table', dragableCss, tableCss]" :style="tableStyle">
      <thead v-if="!uiconfig.meta.custom?.headless" :class="headerCss" :style="headerStyle">
        <tr>
          <th v-for="(item, index) in header" :class="[alignCss]" :key="index" v-html="item.name"></th>
        </tr>
      </thead>
      <tbody>
      <tr v-for="(r, rindex) in row" :key="rindex">
        <td v-for="(item, cindex) in r" :class="[alignCss]" :key="cindex" v-html="item.name"></td>
      </tr>
      </tbody>
      <tfoot v-if="!uiconfig.meta.custom?.footless" :class="footerCss" :style="footerStyle">
      <tr>
        <th v-for="(item, index) in footer" :class="[alignCss]" :key="index" v-html="item.name"></th>
      </tr>
      </tfoot>
    </table>
  </div>
</template>

<script lang="ts">
import Table from '@/components/ui/js/Table'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Bootstrap_Table',
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
    const header = computed(() => {
      const header = props.uiconfig.meta?.custom?.data?.header
      if (header && header.length > 0) return header
      // 非无头模式下，没有头部数据就用第一行作为头部
      const row = table.getMeta('row', 'custom')
      if (row && row.length > 0 && !props.uiconfig.meta?.custom?.headless) return row[0]
      return [
        { name: 'Header 1' },
        { name: 'Header 2' },
        { name: 'Header 3' }
      ]
    })
    const footer = computed(() => {
      const footer = props.uiconfig.meta?.custom?.data?.footer
      if (footer && footer.length > 0) return footer
      // 非无脚模式下，没有脚数据就用最后一行作为脚
      const row = table.getMeta('row', 'custom')
      if (row && row.length > 0 && !props.uiconfig.meta?.custom?.footless) return row[row.length - 1]
      return [
        { name: 'Footer 1' },
        { name: 'Footer 2' },
        { name: 'Footer 3' }
      ]
    })
    const row = computed(() => {
      const row = props.uiconfig.meta?.custom?.data?.row
      // console.log(row)
      if (row && row.length > 0) {
        return row
      }
      return [[
        { name: 'row 1 column 1' },
        { name: 'row 1 column 2' },
        { name: 'row 1 column 3' }
      ],
      [
        { name: 'row 2 column 1' },
        { name: 'row 2 column 2' },
        { name: 'row 2 column 3' }
      ],
      [
        { name: 'row 3 column 1' },
        { name: 'row 3 column 2' },
        { name: 'row 3 column 3' }
      ],
      [
        { name: 'row 4 column 1<br/> new line' },
        { name: 'row 4 column 2' },
        { name: 'row 4 column 3' }
      ]
      ]
    })
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
      for (const styleKey in style) {
        if (styleKey.match(/^background/)) delete style[styleKey]
        if (styleKey.match(/^color/)) delete style[styleKey]
      }
      style.overflow = 'hidden'
      return table.appendImportant(style)
    })

    const tableStyle = computed(() => {
      const style = table.getUIStyle()
      const newStyle:any = {}
      for (const styleKey in style) {
        if (styleKey.match(/^background/)) newStyle[styleKey] = style[styleKey]
        if (styleKey.match(/^color/)) newStyle[styleKey] = style[styleKey]
        if (styleKey.match(/^border/)) newStyle[styleKey] = style[styleKey]
      }
      newStyle.margin = '0px'
      return table.appendImportant(newStyle)
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
      header,
      footer,
      uiCss,
      tableCss,
      uiStyle,
      tableStyle,
      headerCss,
      headerStyle,
      footerCss,
      footerStyle,
      alignCss,
      row
    }
  }
}

</script>
