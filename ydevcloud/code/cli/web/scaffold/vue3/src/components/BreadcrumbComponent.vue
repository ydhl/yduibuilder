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
          <a v-if="myValueIndex!==idxOfitem" :class="foregroundClass" :style="foregroundStyle" @click="updateValue(idxOfitem, itemOfitem)" href='javascript:;'>{{getItemTitle(itemOfitem)}}</a>
          <span v-if="myValueIndex==idxOfitem">{{getItemTitle(itemOfitem)}}</span>
      </li>
      </template>
  </ol>
</template>
<script lang="ts" setup>
import {ref, watch, onMounted} from 'vue'
import y from '@/lib/ydecloud'
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
  return String(typeof item === 'object' ? item.name || item.value : item)
}
function updateValue(index: number, item: string|number|{ [key: string]: string | number | boolean | undefined }) {
  myValue.value = String(typeof item === 'object' ? item.value || item.name || index : item)
  myValueIndex.value = index
  myValueTitle.value = String(typeof item === 'object' ? item.name || item.value : item)
}
onMounted(() => {
  const defValue = getDefaultValue()
  myValue.value = defValue || ''
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
  myValueTitle.value = String(typeof item === 'object' ? item.name || item.value : item)
})
</script>
