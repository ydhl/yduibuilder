<template>
    <div v-attr="attrs" :class="css" :style="style" data-root :data-index="iterateIndex">
      <table :class="tableCss" v-if="datas && datas.length > 0"
        @click="y.emit($el, 'y-click', $event)"
        @dblclick="y.emit($el, 'y-dblclick', $event)"
        @scroll="y.emit($el, 'y-scroll', $event)"
        @mousedown="y.emit($el, 'y-mousedown', $event)"
        @mouseup="y.emit($el, 'y-mouseup', $event)"
        @mouseover="y.emit($el, 'y-mouseover', $event)"
        @mouseout="y.emit($el, 'y-mouseout', $event)"
        @mousemove="y.emit($el, 'y-mousemove', $event)"
        @mouseenter="y.emit($el, 'y-mouseenter', $event)"
        @mouseleave="y.emit($el, 'y-mouseleave', $event)">
        <thead v-if="!headless" :class="headerCss">
          <tr v-for="(headerRow, idxOfHeaderRow) in header" :key="idxOfHeaderRow">
            <th v-for="(itemOfHeader, idxOfHeader) in headerRow" :key="idxOfHeader" :class="tdCss">{{ typeof itemOfHeader == 'object' ? ((itemOfHeader as Record<string, string>).name || JSON.stringify(itemOfHeader)) : itemOfHeader  }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, idxOfRow) in body" :key="idxOfRow">
            <td v-for="(itemOfColumn, idxOfColumn) in row" :key="idxOfColumn" :class="tdCss">{{ typeof itemOfColumn == 'object' ? ((itemOfColumn as Record<string, string>).name || JSON.stringify(itemOfColumn)) : itemOfColumn  }}</td>
          </tr>
        </tbody>
        <tfoot v-if="!footless" :class="footerCss">
          <tr v-for="(footerRow, idxOfFooterRow) in footer" :key="idxOfFooterRow">
            <th v-for="(itemOfFooter, idxOfFooter) in footerRow" :key="idxOfFooter" :class="tdCss">{{ typeof itemOfFooter == 'object' ? ((itemOfFooter as Record<string, string>).name || JSON.stringify(itemOfFooter)) : itemOfFooter  }}</th>
          </tr>
        </tfoot>
      </table>
      <table :class="tableCss" v-if="!datas || datas.length == 0"
        @click="y.emit($el, 'y-click', $event)"
        @dblclick="y.emit($el, 'y-dblclick', $event)"
        @scroll="y.emit($el, 'y-scroll', $event)"
        @mousedown="y.emit($el, 'y-mousedown', $event)"
        @mouseup="y.emit($el, 'y-mouseup', $event)"
        @mouseover="y.emit($el, 'y-mouseover', $event)"
        @mouseout="y.emit($el, 'y-mouseout', $event)"
        @mousemove="y.emit($el, 'y-mousemove', $event)"
        @mouseenter="y.emit($el, 'y-mouseenter', $event)"
        @mouseleave="y.emit($el, 'y-mouseleave', $event)">
        <slot></slot>
      </table>
    </div>
</template>
<script lang="ts" setup>
import y from '@/lib/ydecloud'
import {computed} from 'vue'
const { iterateIndex, headless, footless, headerCss, headerRow, footerRow, footerCss, tableCss, tdCss, attrs, css, style, datas } = defineProps({
// 该组件被迭代时的索引
iterateIndex: Number,
// 父组件被迭代时的索引
parentIterateIndex: Number,
attrs: Object,
css: {
    type: [Object , String]
},
tableCss: {
    type: [Object , String]
},
headerCss: {
    type: [Object , String]
},
footerCss: {
    type: [Object , String]
},
tdCss: {
    type: [Object , String]
},
headless: Boolean,
footless: Boolean,
headerRow: {
  required: true,
  type: Number,
  default: 0
},

footerRow: {
  required: true,
  type: Number,
  default: 0
},
style: String,

datas:{
    required: false,
    default: [],
    type:Array<Array<Record<string, any>|string>>
}
})
const emit = defineEmits(['click','dblclick','scroll','mousedown','mouseup','mouseover','mouseout','mousemove','mouseenter','mouseleave'])
const header = computed(() => {
  if(headless) return []
  return datas.slice(0, headerRow)
})
const footer = computed(() => {
  if(footless) return []
  return datas.slice(-footerRow)
})
const body = computed(() => {
  let from = 0
  let to = datas.length
  if(!headless) from = headerRow
  if(!footless) to = datas.length - from - footerRow
  return datas.slice(from, to)
})

</script>
  