<template>
    <nav v-attr="attrs" :class="css" :style="style" data-root :data-index="iterateIndex">
      <ul class="pagination mb-0"
      @click="y.emit($el, 'y-click', $event, currPage, boundData)"
        @dblclick="y.emit($el, 'y-dblclick', $event, currPage, boundData)"
        @input="y.emit($el, 'y-input', $event, currPage, boundData)"
        @mousedown="y.emit($el, 'y-mousedown', $event, currPage, boundData)"
        @mouseup="y.emit($el, 'y-mouseup', $event, currPage, boundData)"
        @mouseover="y.emit($el, 'y-mouseover', $event, currPage, boundData)"
        @mouseout="y.emit($el, 'y-mouseout', $event, currPage, boundData)"
        @mousemove="y.emit($el, 'y-mousemove', $event, currPage, boundData)"
        @mouseenter="y.emit($el, 'y-mouseenter', $event, currPage, boundData)"
        @mouseleave="y.emit($el, 'y-mouseleave', $event, currPage, boundData)"
        @blur="y.emit($el, 'y-blur', $event, currPage, boundData)"
        @focus="y.emit($el, 'y-focus', $event, currPage, boundData)"
      >
      <li v-for="item in pageList" :key="item" :class="['page-item', currPage == item ? activeCss : '']" @click="currPage=item">
          <a :class="['page-link', currPage == item ? activeLinkCss : linkCss]" href="javascript:;"
          :style="currPage == item ? activeLinkStyle : linkStyle">{{item}}</a>
      </li>
      </ul>
      <div>{{total}} items</div>
      <select class="form-select " v-model="pageSize" style="width: 100px">
          <option value='10'>10 item/page</option>
          <option value='20'>20 item/page</option>
          <option value='30'>30 item/page</option>
          <option value='50'>50 item/page</option>
          <option value='100'>100 item/page</option>
      </select>
      <div class="d-flex align-items-center gap-1">goto<input type="number" min="1" v-model="myValue" class="form-control " style="width: 100px">
        <button type="button" @click="currPage=myValue" class="btn btn-light ">OK</button>
      </div>
  </nav>
</template>
<script lang="ts" setup>
import y from '@/lib/ydecloud'
import {ref, watch, computed, defineExpose} from 'vue'
const { iterateIndex, total, attrs, css, activeCss, boundData, activeLinkCss, linkCss, style, activeLinkStyle, linkStyle } = defineProps({
// 该组件被迭代时的索引
iterateIndex: Number,
// 父组件被迭代时的索引
parentIterateIndex: Number,
activeCss: String,
activeLinkCss: String,
linkCss: String,
activeLinkStyle: String,
linkStyle: String,
attrs: Object,
boundData: {
  type: [Object, Number, Boolean,String,Array]
},
css: {
    type: [Object , String]
},
total: Number,
style: String
})
const emit = defineEmits(['change'])
const model = defineModel<number|string>()
const currPage = ref(1)
const pageSize = ref(10)
const myValue = ref(model.value || 1)
const pageList = computed(() => {
  const pages = [];
  const maxPage = Math.ceil((total||0) / pageSize.value)
  let startPage = Number(myValue.value || 1);
  startPage = Math.max(startPage - 2, 1);
  const endPage = Math.min(startPage + 9, maxPage);
  if (endPage - startPage < 10){
      startPage = Math.max(endPage - 9, 1)
  }
  for (let i = startPage; i <= endPage; i++) {
      pages.push(i)
  }
  return pages;
})

defineExpose({initModelFromXInput})
// 由x-input指令调用
function initModelFromXInput(n: any){
  model.value = n
}

watch(currPage, (n, old) => {
  emit('change', n, old)
  model.value = n
})
</script>
  