<template>
    <div v-attr="attrs" :class="css" :style="style" data-root :data-index="iterateIndex" :data-value="myValue">
        <template v-for="(item, index) in items" :key="index">
            <a href='javascript:;' 
            @click="updateValue(index, item);y.emit($el, 'y-click', $event, myValue, item)"
            @dblclick="y.emit($el, 'y-dblclick', $event, myValue, item)"
            @mousedown="y.emit($el, 'y-mousedown', $event, myValue, item)"
            @mouseup="y.emit($el, 'y-mouseup', $event, myValue, item)"
            @mouseover="y.emit($el, 'y-mouseover', $event, myValue, item)"
            @mouseout="y.emit($el, 'y-mouseout', $event, myValue, item)"
            @mousemove="y.emit($el, 'y-mousemove', $event, myValue, item)"
            @mouseenter="y.emit($el, 'y-mouseenter', $event, myValue, item)"
            @mouseleave="y.emit($el, 'y-mouseleave', $event, myValue, item)"
            @blur="y.emit($el, 'y-blur', $event, myValue, item)"
            @focus="y.emit($el, 'y-focus', $event, myValue, item)"
            :style="`${itemStyle};${index==myValueIndex ? checkedItemStyle : ''}`"
            :class="['list-group-item list-group-item-action', itemCss, index == myValueIndex ? checkedItemCss : '', {active:index == myValueIndex}]"
            >{{ valueList.getItemTitle(item) }}</a>
        </template>
    </div>
</template>
<script lang="ts" setup>
import y from '@/lib/ydecloud'
import ValueList from './ValueList'
import {ref, watch, onMounted,defineExpose} from 'vue'
const { iterateIndex, itemStyle, checkedItemStyle, itemCss, checkedItemCss, attrs, css, style, items } = defineProps({
// 该组件被迭代时的索引
iterateIndex: Number,
// 父组件被迭代时的索引
parentIterateIndex: Number,
attrs: Object,
css: {
    type: [Object , String]
},
itemCss: {
    type: [Object , String]
},
itemStyle: String,
style: String,
checkedItemStyle: String,
checkedItemCss: {
    type: [Object , String]
},
items:{
    required: true,
    type:[Array<string|number>, Array<{ [key: string]: string | number | boolean | undefined }>]
},
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
  