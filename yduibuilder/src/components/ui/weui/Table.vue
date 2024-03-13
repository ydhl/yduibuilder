<template>
  <table :draggable='draggable'
         :class="['table', dragableCss, uiCss]" :style="uiStyle" :id="myId" :data-type="uiconfig.type"
         :data-pageid="pageid">
    <thead v-if="!uiconfig.meta.custom?.headless" :style="headerStyle">
      <tr>
        <th v-for="(item, index) in header" :style="headerTHStyle(index)" :class="[alignCss, headerTHCss]" :key="index" v-html="item.text"></th>
      </tr>
    </thead>
    <tbody :class="tbodyCss" :style="tbodyStyle">
    <tr v-for="(r, rindex) in row" :key="rindex">
      <td v-for="(item, cindex) in r" :class="[alignCss, tdCss]" :style="tdStyle(rindex, cindex)" :key="cindex" v-html="item.text"></td>
    </tr>
    </tbody>
    <tfoot v-if="!uiconfig.meta.custom?.footless" :style="footerStyle">
    <tr>
      <th v-for="(item, index) in footer" :class="[alignCss, footerTHCss]" :style="footerTHStyle(index)" :key="index" v-html="item.text"></th>
    </tr>
    </tfoot>
  </table>
</template>

<script lang="ts">
import Table from '@/components/ui/js/Table'
import { computed, onMounted, watch } from 'vue'
import { useStore } from 'vuex'
import { YDJSStatic } from '@/lib/ydjs'
import ydhl from '@/lib/ydhl'
import { useI18n } from 'vue-i18n'
declare const YDJS: YDJSStatic

export default {
  name: 'Weui_Table',
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
    const { t } = useI18n()
    const tableSetup = table.setup()
    const header = computed(() => {
      const header = store.state.page.extraInfo[props.uiconfig?.meta?.id]?.header
      if (header && header.length > 0) return header
      // 非无头模式下，没有头部数据就用第一行作为头部
      const row = table.getMeta('row', 'custom')
      if (row && row.length > 0 && !props.uiconfig.meta?.custom?.headless) return row[0]
      return [
        { text: 'Header 1' },
        { text: 'Header 2' },
        { text: 'Header 3' }
      ]
    })
    const footer = computed(() => {
      const footer = store.state.page.extraInfo[props.uiconfig?.meta?.id]?.footer
      if (footer && footer.length > 0) return footer
      // 非无脚模式下，没有脚数据就用最后一行作为脚
      const row = table.getMeta('row', 'custom')
      if (row && row.length > 0 && !props.uiconfig.meta?.custom?.footless) return row[row.length - 1]
      return [
        { text: 'Footer 1' },
        { text: 'Footer 2' },
        { text: 'Footer 3' }
      ]
    })
    const row = computed(() => {
      const row = store.state.page.extraInfo[props.uiconfig?.meta?.id]?.row
      // console.log(row)
      if (row && row.length > 0) {
        const rowCopy = JSON.parse(JSON.stringify(row))
        if (!props.uiconfig.meta?.custom?.headless) {
          rowCopy.splice(0, 1)
        }
        if (!props.uiconfig.meta?.custom?.footless) {
          rowCopy.splice(rowCopy.length - 1, 1)
        }
        return rowCopy
      }
      return [[
        { text: 'row 1 column 1' },
        { text: 'row 1 column 2' },
        { text: 'row 1 column 3' }
      ],
      [
        { text: 'row 2 column 1' },
        { text: 'row 2 column 2' },
        { text: 'row 2 column 3' }
      ],
      [
        { text: 'row 3 column 1' },
        { text: 'row 3 column 2' },
        { text: 'row 3 column 3' }
      ],
      [
        { text: 'row 4 column 1<br/> new line' },
        { text: 'row 4 column 2' },
        { text: 'row 4 column 3' }
      ]
      ]
    })
    const backgroundTheme = computed(() => props.uiconfig.meta?.css?.backgroundTheme === 'default' ? '' : props.uiconfig.meta?.css?.backgroundTheme)
    const alignCss = computed(() => {
      const css = table.getUICss()
      const newCss = [css?.verticalAlignment, css?.textAlignment]
      return newCss.join(' ')
    })
    const rounded = computed(() => {
      const hasRadiusStyle = props.uiconfig.meta?.style && Object.keys(props.uiconfig.meta.style).find((item) => item.match(/^border.*-radius/))
      // console.log(hasStyle)
      if (hasRadiusStyle) return true
      return false
    })
    const uiCss = computed(() => {
      const css = table.getUICss()
      const newCss: any = {}
      delete css?.textAlignment
      delete css?.verticalAlignment
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
      if (rounded.value) {
        newCss.rounded = 'table-rounded'
      } else { // 如果是圆角table，则样式通过table-rounded进行了重写，并且backround需要应用到tdCss上
        if (props.uiconfig.meta?.custom?.grid === 'bordered') {
          newCss.grid = 'table-bordered'
        } else if (props.uiconfig.meta?.custom?.grid === 'borderless') {
          newCss.grid = 'table-borderless'
        }
      }

      for (const cssKey in css) {
        if (rounded.value && (cssKey === 'backgroundTheme' || cssKey === 'border')) {
          continue
        }
        if (cssKey === 'backgroundTheme' && backgroundTheme.value) {
          newCss[cssKey] = 'table-' + backgroundTheme.value
        } else {
          newCss[cssKey] = css[cssKey]
        }
      }
      // console.log(newCss)
      return Object.values(newCss).join(' ')
    })
    const uiStyle = computed(() => {
      const style = table.getUIStyle()
      const newStyle = {}
      for (const key in style) {
        if (rounded.value && (key.match(/^border/) || key.match(/^background-color/))) {
          continue
        } else {
          newStyle[key] = style[key]
        }
      }
      // console.log(newStyle)
      return table.appendImportant(newStyle)
    })
    const grid = computed(() => props.uiconfig.meta?.custom?.grid)
    const defaultRadius = computed(() => {
      return props.uiconfig.meta?.style?.['border-radius'] || '0.25rem'
    })
    const headerTHStyle = (index) => {
      // console.log(rounded.value)
      // 圆角的情况下，控制第一和最后一个的样式
      if (!rounded.value) return ''
      const newStyle: any = []
      // console.log(grid.value)

      if (index === 0) {
        const width = props.uiconfig.meta?.style?.['border-left-width'] || props.uiconfig.meta?.style?.['border-width'] || ''
        const style = props.uiconfig.meta?.style?.['border-left-style'] || props.uiconfig.meta?.style?.['border-style'] || ''
        const color = props.uiconfig.meta?.style?.['border-left-color'] || props.uiconfig.meta?.style?.['border-color'] || ''
        newStyle.push(`border-top-left-radius:${props.uiconfig.meta?.style?.['border-top-left-radius'] || defaultRadius.value}`)
        newStyle.push(`border-left:${width} ${style} ${color}`)
      } else if (index === header.value.length - 1) {
        const width = props.uiconfig.meta?.style?.['border-right-width'] || props.uiconfig.meta?.style?.['border-width'] || ''
        const style = props.uiconfig.meta?.style?.['border-right-style'] || props.uiconfig.meta?.style?.['border-style'] || ''
        const color = props.uiconfig.meta?.style?.['border-right-color'] || props.uiconfig.meta?.style?.['border-color'] || ''
        newStyle.push(`border-top-right-radius:${props.uiconfig.meta?.style?.['border-top-right-radius'] || defaultRadius.value}`)
        newStyle.push(`border-right:${width} ${style} ${color}`)
      }

      const width = props.uiconfig.meta?.style?.['border-top-width'] || props.uiconfig.meta?.style?.['border-width'] || ''
      const style = props.uiconfig.meta?.style?.['border-top-style'] || props.uiconfig.meta?.style?.['border-style'] || ''
      const color = props.uiconfig.meta?.style?.['border-top-color'] || props.uiconfig.meta?.style?.['border-color'] || ''
      newStyle.push(`border-top:${width} ${style} ${color}`)
      // console.log(newStyle.join(';'))
      return newStyle.join(';')
    }
    const headerTHCss = computed(() => {
      const css: any = []
      css.push(props.uiconfig.meta?.css?.header && props.uiconfig.meta?.css?.header !== 'default' ? 'table-' + props.uiconfig.meta.css.header : '')
      if (rounded.value) {
        if (grid.value === 'bordered') {
          css.push('border')
        } else if (grid.value === 'borderless') {
          css.push('border-0')
        }
      }
      return css.join(' ')
    })
    const headerStyle = computed(() => {
      const style = table.getUIStyle()
      if (props.uiconfig.meta?.custom?.header) {
        return `background-color:${props.uiconfig.meta?.custom?.header}`
      } else {
        return `background-color:${style['background-color']}`
      }
    })
    const tbodyStyle = computed(() => {
      const style = table.getUIStyle()
      if (rounded.value && style?.['background-color']) {
        return `background-color:${style?.['background-color']}`
      }
      return ''
    })
    const footerTHStyle = (index) => {
      if (!rounded.value) return ''
      const newStyle: any = []
      // console.log(grid.value)

      if (index === 0) {
        newStyle.push(`border-bottom-left-radius:${props.uiconfig.meta?.style?.['border-bottom-left-radius'] || defaultRadius.value}`)
        const width = props.uiconfig.meta?.style?.['border-left-width'] || props.uiconfig.meta?.style?.['border-width'] || ''
        const style = props.uiconfig.meta?.style?.['border-left-style'] || props.uiconfig.meta?.style?.['border-style'] || ''
        const color = props.uiconfig.meta?.style?.['border-left-color'] || props.uiconfig.meta?.style?.['border-color'] || ''
        newStyle.push(`border-left:${width} ${style} ${color}`)
      }
      if (index === header.value.length - 1) {
        newStyle.push(`border-bottom-right-radius:${props.uiconfig.meta?.style?.['border-bottom-right-radius'] || defaultRadius.value}`)
        const width = props.uiconfig.meta?.style?.['border-right-width'] || props.uiconfig.meta?.style?.['border-width'] || ''
        const style = props.uiconfig.meta?.style?.['border-right-style'] || props.uiconfig.meta?.style?.['border-style'] || ''
        const color = props.uiconfig.meta?.style?.['border-right-color'] || props.uiconfig.meta?.style?.['border-color'] || ''
        newStyle.push(`border-right:${width} ${style} ${color}`)
      }
      const width = props.uiconfig.meta?.style?.['border-bottom-width'] || props.uiconfig.meta?.style?.['border-width'] || ''
      const style = props.uiconfig.meta?.style?.['border-bottom-style'] || props.uiconfig.meta?.style?.['border-style'] || ''
      const color = props.uiconfig.meta?.style?.['border-bottom-color'] || props.uiconfig.meta?.style?.['border-color'] || ''
      newStyle.push(`border-bottom:${width} ${style} ${color}`)
      return newStyle.join(';')
    }
    const footerTHCss = computed(() => {
      const css: any = []
      css.push(props.uiconfig.meta?.css?.footer && props.uiconfig.meta?.css?.footer !== 'default' ? 'table-' + props.uiconfig.meta.css.footer : '')
      if (rounded.value) {
        if (grid.value === 'bordered') {
          css.push('border')
        } else if (grid.value === 'borderless') {
          css.push('border-0')
        }
      }
      return css.join(' ')
    })
    const footerStyle = computed(() => {
      const style = table.getUIStyle()
      if (props.uiconfig.meta?.custom?.footer) {
        return `background-color:${props.uiconfig.meta?.custom?.footer}`
      } else {
        return `background-color:${style['background-color']}`
      }
    })
    const tdStyle = (rindex, cindex) => {
      if (!rounded.value) {
        return ''
      }
      const newStyle: any = []
      // console.log(grid.value)
      const firstRowLength = row.value?.[0].length - 1
      const rowLength = row.value.length - 1
      const colomnLength = row.value?.[rindex].length - 1
      const lastRowLength = row.value?.[rowLength].length - 1
      const headless = props.uiconfig.meta.custom?.headless
      const footless = props.uiconfig.meta.custom?.footless
      if (rindex === 0 && cindex === 0 && headless) { // 无头第一行第一列
        newStyle.push(`border-top-left-radius:${props.uiconfig.meta?.style?.['border-top-left-radius'] || defaultRadius.value}`)
      }
      if (rindex === 0 && cindex === firstRowLength && headless) { // 无头第一行最后一列
        newStyle.push(`border-top-right-radius:${props.uiconfig.meta?.style?.['border-top-right-radius'] || defaultRadius.value}`)
      }
      if (rindex === rowLength && cindex === 0 && footless) { // 无脚最后一行第一列
        newStyle.push(`border-bottom-left-radius:${props.uiconfig.meta?.style?.['border-bottom-left-radius'] || defaultRadius.value}`)
      }
      if (rindex === rowLength && cindex === lastRowLength && footless) { // 无脚最后一行最后一列
        newStyle.push(`border-bottom-right-radius:${props.uiconfig.meta?.style?.['border-bottom-right-radius'] || defaultRadius.value}`)
      }
      if (rindex === 0 && headless) {
        const topWidth = props.uiconfig.meta?.style?.['border-top-width'] || props.uiconfig.meta?.style?.['border-width'] || ''
        const topStyle = props.uiconfig.meta?.style?.['border-top-style'] || props.uiconfig.meta?.style?.['border-style'] || ''
        const topColor = props.uiconfig.meta?.style?.['border-top-color'] || props.uiconfig.meta?.style?.['border-color'] || ''
        newStyle.push(`border-top:${topWidth} ${topStyle} ${topColor}`)
      }
      if (rindex === rowLength && footless) {
        const bottomWidth = props.uiconfig.meta?.style?.['border-bottom-width'] || props.uiconfig.meta?.style?.['border-width'] || ''
        const bottomStyle = props.uiconfig.meta?.style?.['border-bottom-style'] || props.uiconfig.meta?.style?.['border-style'] || ''
        const bottomColor = props.uiconfig.meta?.style?.['border-bottom-color'] || props.uiconfig.meta?.style?.['border-color'] || ''
        newStyle.push(`border-bottom:${bottomWidth} ${bottomStyle} ${bottomColor}`)
      }
      if (cindex === 0) {
        const width = props.uiconfig.meta?.style?.['border-left-width'] || props.uiconfig.meta?.style?.['border-width'] || ''
        const style = props.uiconfig.meta?.style?.['border-left-style'] || props.uiconfig.meta?.style?.['border-style'] || ''
        const color = props.uiconfig.meta?.style?.['border-left-color'] || props.uiconfig.meta?.style?.['border-color'] || ''
        newStyle.push(`border-left:${width} ${style} ${color}`)
      }

      if (colomnLength === cindex) {
        const width = props.uiconfig.meta?.style?.['border-right-width'] || props.uiconfig.meta?.style?.['border-width'] || ''
        const style = props.uiconfig.meta?.style?.['border-right-style'] || props.uiconfig.meta?.style?.['border-style'] || ''
        const color = props.uiconfig.meta?.style?.['border-right-color'] || props.uiconfig.meta?.style?.['border-color'] || ''
        newStyle.push(`border-right:${width} ${style} ${color}`)
      }
      // console.log(newStyle.join(';'))
      return newStyle.join(';')
    }
    const tdCss = computed(() => {
      const css: any = []
      if (rounded.value) {
        if (grid.value === 'bordered') {
          css.push('border')
        } else if (grid.value === 'borderless') {
          css.push('border-0')
        }
      }
      return css.join(' ')
    })
    const tbodyCss = computed(() => {
      const newCss: any = []
      if (rounded.value && backgroundTheme.value) {
        newCss.push('table-' + backgroundTheme.value)
      }
      return newCss.join(' ')
    })
    const project = computed(() => store.state.page.project)
    const currExcelID = computed(() => {
      const files = table.getMeta('datasource', 'files') || []
      return files[0]?.id
    })
    watch(currExcelID, (newV, oldV) => {
      if (oldV !== newV) parseExcel(newV)
    })
    const parseExcel = (fid) => {
      if (!fid) return
      const loadingid = YDJS.loading(t('page.loading'))
      ydhl.get('api/parseexcel', { pid: project.value.id, fid }, (rst) => {
        YDJS.hide_dialog(loadingid)
        if (!rst || !rst.success) {
          YDJS.alert(rst?.msg || t('table.canNotParseExcel'), 'Oops')
          return
        }
        const _props = {}
        _props[props.uiconfig?.meta?.id] = {
          header: rst.data.header || [],
          footer: rst.data.footer || [],
          row: rst.data.row || []
        }
        store.commit('updateExtraInfo', _props)
      }, 'json')
    }
    onMounted(() => {
      parseExcel(currExcelID.value)
    })
    return {
      ...tableSetup,
      header,
      footer,
      uiCss,
      uiStyle,
      headerTHCss,
      headerTHStyle,
      headerStyle,
      tdCss,
      tbodyCss,
      tdStyle,
      footerTHCss,
      footerTHStyle,
      footerStyle,
      tbodyStyle,
      alignCss,
      row
    }
  }
}

</script>
