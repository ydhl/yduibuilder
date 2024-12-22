<template>
  <ol v-attr="attrs" :class="css" :style="style" data-root :data-index="iterateIndex" :data-value="myValue">
      <template v-for="(itemOfitem, idxOfitem) in items" :key="idxOfitem">
      <li :class="{'breadcrumb-item': true, 'active':myValueIndex==idxOfitem}"
      @click="y.emit($el, 'y-click', $event, myValue, itemOfitem)"
      @dblclick="y.emit($el, 'y-dblclick', $event, myValue, itemOfitem)"
      @mousedown="y.emit($el, 'y-mousedown', $event, myValue, itemOfitem)"
      @mouseup="y.emit($el, 'y-mouseup', $event, myValue, itemOfitem)"
      @mouseover="y.emit($el, 'y-mouseover', $event, myValue, itemOfitem)"
      @mouseout="y.emit($el, 'y-mouseout', $event, myValue, itemOfitem)"
      @mousemove="y.emit($el, 'y-mousemove', $event, myValue, itemOfitem)"
      @mouseenter="y.emit($el, 'y-mouseenter', $event, myValue, itemOfitem)"
      @mouseleave="y.emit($el, 'y-mouseleave', $event, myValue, itemOfitem)"
      @blur="y.emit($el, 'y-blur', $event, myValue, itemOfitem)"
      @focus="y.emit($el, 'y-focus', $event, myValue, itemOfitem)">
          <a v-if="myValueIndex!==idxOfitem" :class="foregroundClass" :style="foregroundStyle" @click="updateValue(idxOfitem, itemOfitem)" href='javascript:;'>{{valueList.getItemTitle(itemOfitem)}}</a>
          <span v-if="myValueIndex==idxOfitem">{{valueList.getItemTitle(itemOfitem)}}</span>
      </li>
      </template>
  </ol>
</template>
<script lang="ts" setup>
import {ref, watch, onMounted, defineExpose} from 'vue'
import y from '@/lib/ydecloud'
import ValueList from './ValueList'

const { iterateIndex, attrs, css, style, items, foregroundClass, foregroundStyle } = defineProps({
  // 该组件被迭代时的索引
  iterateIndex: Number,
  // 父组件被迭代时的索引
  parentIterateIndex: Number,
  items:{
    required: true,
    type:[Array<string|number>, Array<{ [key: string]: string | number | boolean | undefined }>]
  },
  attrs: Object,
  foregroundClass: String,
  foregroundStyle: String,
  css: {
    type: [Object , String]
  },
  style: {
    type: [Object , String]
  }
})

const model = defineModel<any>()
const myValue = ref<string>('')
const myValueTitle = ref<string>('')
const myValueIndex = ref<number>(-1)
const emit = defineEmits(['change'])
const valueList = new ValueList(items)
let needEmitChange = true

defineExpose({initModelFromXInput})
// 由x-input指令调用
function initModelFromXInput(n: any){
  model.value = n
}
watch(myValue, (n, old) => {
  model.value = n
  if (!needEmitChange) {
    needEmitChange = true
    return
  }
  emit('change', n, old)
})
function updateValue(index: number, item: string|number|{ [key: string]: string | number | boolean | undefined }) {
  myValueIndex.value = index
  myValue.value = valueList.getItemValue(index, item)
  myValueTitle.value = valueList.getItemTitle(item)
}
onMounted(() => {
  needEmitChange = model.value === undefined
  const defValue = !valueList.isEmpty(model.value) ? model.value : valueList.getDefaultValue()
  myValue.value = defValue || ''
  myValueIndex.value = valueList.getItemIndexByValue(defValue)
  if (myValueIndex.value !== -1){
    myValueTitle.value = valueList.getItemTitle(items[myValueIndex.value])
  }
})
</script>
