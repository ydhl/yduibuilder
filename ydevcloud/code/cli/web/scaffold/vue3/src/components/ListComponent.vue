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
            >{{ getItemTitle(item) }}</a>
        </template>
    </div>
</template>
<script lang="ts" setup>
import y from '@/lib/ydecloud'
import {ref, watch, onMounted} from 'vue'
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
const model = defineModel()
const myValue = ref<string>('')
const myValueTitle = ref<string>('')
const myValueIndex = ref<number>(-1)
const emit = defineEmits(['blur','change','click','dblclick','focus','input','mousedown','mouseup','mouseover','mouseout','mousemove','mouseenter','mouseleave'])

watch(myValue, (n, old) => {
  emit('change', n, old)
  model.value = n
})
function getDefaultValue(){
    const item = items.find((item) => typeof item === 'object' && item.checked)
    return String((typeof item === 'object' ? (item.value || item.name) : item) || '')
}
function getItemTitle(item: string|number|{ [key: string]: string | number | boolean | undefined }) {
  return String(typeof item === 'object' ? (item.name || item.value) : item)
}
function updateValue(index: number, item: string|number|{ [key: string]: string | number | boolean | undefined }) {
  myValue.value = String(typeof item === 'object' ? (item.value || item.name || index) : item)
  myValueIndex.value = index
  myValueTitle.value = String(typeof item === 'object' ? (item.name || item.value) : item)
}
onMounted(() => {
  const defValue = getDefaultValue()
  myValue.value = defValue ? String(defValue) : ''
  myValueIndex.value = items.findIndex((item) => {
    const isObject = typeof item === 'object'
    if (isObject){
      return item.value === defValue || item.name === defValue
    }
    return item === defValue
  })
  const item = items.find((item) => {
    const isObject = typeof item === 'object'
    if (isObject){
      return item.value === defValue || item.name === defValue
    }
    return item === defValue
  })
  myValueTitle.value = String(typeof item === 'object' ? (item.name || item.value) : item || '')
})
</script>
  