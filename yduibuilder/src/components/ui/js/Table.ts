import UIBase from '@/components/ui/js/UIBase'
import { computed, onMounted, ref } from 'vue'
import split from '@/lib/split'
declare const ports: any

// declare const ports: any
export default class Table extends UIBase {
  private splitSize = 0
  private splitOrig = 0
  private splitType = ''
  private splitAtRow = 0
  private splitAtColumn = 0
  private currTDWidth = 0
  private currTDHeight = 0
  setup () {
    const props = this.props
    const setup = super.setup()

    const tableToolbarVisible = ref(false)
    const tableToolbarStyle = ref('')
    const tableToolbarType = ref('')
    const tableToolbarKey = ref('')
    const footerRow = computed(() => parseInt(props.uiconfig?.meta?.custom?.footless ? 0 : (props.uiconfig?.meta?.custom?.footerRow || 1)))
    const headerRow = computed(() => parseInt(props.uiconfig?.meta?.custom?.headless ? 0 : (props.uiconfig?.meta?.custom?.headerRow || 1)))
    const bodyRow = computed(() => parseInt(props.uiconfig?.meta?.custom?.bodyRow || 1))
    const columnCount = computed(() => parseInt(props.uiconfig?.meta?.custom?.columnCount || 2))
    const columnConfig = computed({
      get () {
        return props.uiconfig?.meta?.custom?.columnConfig || {}
      },
      set (v) {
        ports.parent({
          type: 'updateItemMeta',
          data: {
            itemid: props.uiconfig.meta.id,
            pageId: props.pageid,
            type: 'custom',
            props: { columnConfig: v }
          }
        })
      }
    })
    const size = computed({
      get () {
        return props.uiconfig?.meta?.custom?.size || {}
      },
      set (v) {
        ports.parent({
          type: 'updateItemMeta',
          data: {
            itemid: props.uiconfig.meta.id,
            pageId: props.pageid,
            type: 'custom',
            props: { size: v }
          }
        })
      }
    })
    const rowData = computed(() => {
      const row = props.uiconfig.meta?.custom?.data
      // console.log(row)
      if (row && row.length > 0) {
        return row
      }
      const header = headerRow.value
      const footer = footerRow.value
      const temp: any = []
      for (let row = 0; row < bodyRow.value + header + footer; row++) {
        temp[row] = []
        for (let column = 0; column < columnCount.value; column++) {
          temp[row].push({ name: `row ${row} column ${column}` })
        }
      }
      return temp
    })
    const columnItems = computed(() => {
      if (!props.uiconfig.items) return {}
      const items:any = {}
      for (const item of props.uiconfig.items) {
        const placeInParent = item.placeInParent
        if (!placeInParent) continue
        const [row, column] = placeInParent.split('-')

        if (!items[row]) items[row] = {}
        if (!items[row][column]) items[row][column] = []
        items[row][column].push(item)
      }
      return items
    })
    const canSplitColumn = computed(() => {
      if (!tableToolbarKey.value) return false
      const [type, row, column]: any = tableToolbarKey.value.split('-')
      if (type !== 'td') return false
      return columnConfig.value?.[row - 1]?.[column - 1]?.colspan > 1
    })
    const canSplitRow = computed(() => {
      if (!tableToolbarKey.value) return false
      const [type, row, column] : any = tableToolbarKey.value.split('-')
      if (type !== 'td') return false
      return columnConfig.value?.[row - 1]?.[column - 1]?.rowspan > 1
    })
    const canSpanRow = computed(() => {
      if (!tableToolbarKey.value) return false
      const [type, row, column] : any = tableToolbarKey.value.split('-')
      if (type !== 'td') return false
      // 还有后序行则可以跨行, header, body, footer之间不能跨行
      if (headerRow.value > 0 && row - 1 < headerRow.value) { // header
        return headerRow.value > parseInt(row) + (columnConfig.value?.[row - 1]?.[column - 1]?.rowspan || 1) - 1
      } else if (row - 1 < headerRow.value + bodyRow.value) { // body
        return headerRow.value + bodyRow.value > parseInt(row) + (columnConfig.value?.[row - 1]?.[column - 1]?.rowspan || 1) - 1
      }
      return headerRow.value + bodyRow.value + footerRow.value > parseInt(row) + (columnConfig.value?.[row - 1]?.[column - 1]?.rowspan || 1) - 1
    })
    const canSpanColumn = computed(() => {
      if (!tableToolbarKey.value) return false
      const [type, row, column]: any = tableToolbarKey.value.split('-')
      if (type !== 'td') return false
      // 还有后序列则可以跨列
      return columnCount.value > parseInt(column) + (columnConfig.value?.[row - 1]?.[column - 1]?.colspan || 1) - 1
    })
    const splitColumn = () => {
      if (!tableToolbarKey.value) return
      const [type, row, column]: any = tableToolbarKey.value.split('-')
      if (type !== 'td') return
      const colspan = columnConfig.value?.[row - 1]?.[column - 1]?.colspan || 1
      if (colspan <= 1) return

      const rowspan = columnConfig.value?.[row - 1]?.[column - 1]?.rowspan || 1
      const oldConfig = JSON.parse(JSON.stringify(columnConfig.value))
      for (let index = 0; index < rowspan; index++) {
        delete oldConfig?.[row - 1 + index]?.[column - 1 + colspan - 1]?.isMerged
      }
      oldConfig[row - 1][column - 1].colspan = colspan - 1
      columnConfig.value = oldConfig
    }
    const splitRow = () => {
      if (!tableToolbarKey.value) return
      const [type, row, column]: any = tableToolbarKey.value.split('-')
      if (type !== 'td') return
      const rowspan = columnConfig.value?.[row - 1]?.[column - 1]?.rowspan || 1
      if (rowspan <= 1) return

      const colspan = columnConfig.value?.[row - 1]?.[column - 1]?.colspan || 1
      const oldConfig = JSON.parse(JSON.stringify(columnConfig.value))
      for (let index = 0; index < colspan; index++) {
        delete oldConfig?.[row - 1 + rowspan - 1]?.[column - 1 + index]?.isMerged
      }
      oldConfig[row - 1][column - 1].rowspan = rowspan - 1
      columnConfig.value = oldConfig
    }
    const spanRow = () => {
      if (!tableToolbarKey.value) return
      const [type, row, column]: any = tableToolbarKey.value.split('-')
      if (type !== 'td') return
      // 向下合并一行， 如果下行的列已经右跨行了，则合并
      let merged = 2
      if (columnConfig.value?.[row - 1]?.[column]?.rowspan) {
        merged += columnConfig.value?.[row - 1]?.[column]?.rowspan
      }

      const oldConfig = JSON.parse(JSON.stringify(columnConfig.value))
      if (!oldConfig[row - 1]) oldConfig[row - 1] = {}
      if (!oldConfig[row]) oldConfig[row] = {}
      if (!oldConfig[row - 1][column - 1]) oldConfig[row - 1][column - 1] = {} // 当前列
      // 对当前所跨的列都跨行
      const colspan = columnConfig.value?.[row - 1]?.[column - 1]?.colspan || 1
      for (let index = 0; index < colspan; index++) {
        if (!oldConfig[row][column - 1 + index]) oldConfig[row][column - 1 + index] = {} // 下行列
        oldConfig[row][column - 1 + index].isMerged = true
      }

      oldConfig[row - 1][column - 1].rowspan = merged
      columnConfig.value = oldConfig
    }
    const spanColumn = () => {
      if (!tableToolbarKey.value) return
      const [type, row, column]: any = tableToolbarKey.value.split('-')
      if (type !== 'td') return
      // 向右合并一列， 如果右列已经右跨列了，则合并
      let merged = 0
      if (columnConfig.value?.[row - 1]?.[column - 1]?.colspan) {
        merged = columnConfig.value[row - 1][column - 1].colspan + 1
      } else {
        merged = 2
      }

      const oldConfig = JSON.parse(JSON.stringify(columnConfig.value))
      if (!oldConfig[row - 1]) oldConfig[row - 1] = {} // 当前行
      if (!oldConfig[row - 1][column - 1]) oldConfig[row - 1][column - 1] = {} // 本列
      for (let index = 1; index < merged; index++) {
        const nextColumn = column - 1 + index
        if (!oldConfig[row - 1][nextColumn]) oldConfig[row - 1][nextColumn] = {} // 下列
        oldConfig[row - 1][nextColumn].isMerged = true
      }

      oldConfig[row - 1][column - 1].colspan = merged
      columnConfig.value = oldConfig
    }

    const updateToolbarStyle = (td: any) => {
      const { width, height, top, left } = td.getBoundingClientRect()
      this.currTDWidth = width
      this.currTDHeight = height
      tableToolbarStyle.value = `width:${width - 4}px;height:${height - 4}px;transform:translate(${left + 2}px,${top + 2}px);`
    }
    const showToolbar = (event: any, type: string, row: number, column: number) => {
      if (tableToolbarKey.value === `${type}-${row}-${column}`) return
      tableToolbarType.value = type
      const td = event.target.closest('td') || event.target.closest('th')
      updateToolbarStyle(td)
      tableToolbarVisible.value = true
      tableToolbarKey.value = `${type}-${row}-${column}`
    }
    const closeToolbar = () => {
      tableToolbarVisible.value = false
      tableToolbarKey.value = ''
    }
    const splitCallback = (event: any) => {
      const target = event.target.closest('[data-target]')
      if (!target) return {}
      const [type, row, column] = target.dataset.target.split('-')
      this.splitType = type
      this.splitAtRow = row - 1
      this.splitAtColumn = column - 1
      this.splitOrig = type === 'column' ? props.uiconfig.meta?.custom?.size?.width?.[this.splitAtColumn] : props.uiconfig.meta?.custom?.size?.height?.[this.splitAtRow]
      if (!this.splitOrig) {
        this.splitOrig = type === 'column' ? this.currTDWidth : this.currTDHeight
      }
      return {
        spliting: (dist: number) => {
          if (this.splitType === 'column') {
            target.style.cssText = `transform:translateX(${dist}px)`
          } else {
            target.style.cssText = `transform:translateY(${dist}px)`
          }
          // console.log(target.style.cssText)
          this.splitSize = dist
          return true
        },
        splited: () => {
          const oldSize = JSON.parse(JSON.stringify(size.value))
          if (this.splitType === 'column') {
            if (!oldSize.width) oldSize.width = {}
            oldSize.width[this.splitAtColumn] = this.splitOrig + this.splitSize
            if (oldSize.width[this.splitAtColumn] < 0) delete oldSize.width[this.splitAtColumn]
          } else {
            if (!oldSize.height) oldSize.height = {}
            oldSize.height[this.splitAtRow] = this.splitOrig + this.splitSize
            // console.log(oldSize)
            if (oldSize.height[this.splitAtRow] < 0) delete oldSize.height[this.splitAtRow]
          }
          size.value = oldSize
        }
      }
    }
    onMounted(() => {
      split('.split-tool-y', splitCallback, 'Y')
      split('.split-tool-x', splitCallback, 'X')
    })
    return {
      ...setup,
      bodyRow,
      columnCount,
      headerRow,
      footerRow,
      rowData,
      columnConfig,
      columnItems,
      canSplitColumn,
      canSplitRow,
      canSpanColumn,
      canSpanRow,
      splitColumn,
      splitRow,
      spanColumn,
      spanRow,
      showToolbar,
      closeToolbar,
      tableToolbarVisible,
      tableToolbarType,
      tableToolbarStyle,
      tableToolbarKey,
      size
    }
  }
}
