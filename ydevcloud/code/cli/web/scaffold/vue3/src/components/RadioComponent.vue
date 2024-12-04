<template>
    <div v-attr="attrs" :class="css" :style="style" data-root :data-index="iterateIndex" :data-value="myValue">
        <template v-for="(item, index) in items" :key="index">
            <div class="form-check d-flex me-3 align-items-center" @click="updateValue(index, item);y.emit($el, 'y-click', $event, myValue, item)"
              @dblclick="y.emit($el, 'y-dblclick', $event, myValue, item)"
              @input="y.emit($el, 'y-input', $event, myValue, item)"
              @mousedown="y.emit($el, 'y-mousedown', $event, myValue, item);"
              @mouseup="y.emit($el, 'y-mouseup', $event, myValue, item)"
              @mouseover="y.emit($el, 'y-mouseover', $event, myValue, item)"
              @mouseout="y.emit($el, 'y-mouseout', $event, myValue, item)"
              @mousemove="y.emit($el, 'y-mousemove', $event, myValue, item)"
              @mouseenter="y.emit($el, 'y-mouseenter', $event, myValue, item)"
              @mouseleave="y.emit($el, 'y-mouseleave', $event, myValue, item)">
              <input :id="getItemId(index)" :name="uuid + '-' + (iterateIndex || '')" type="radio"
              @blur="y.emit($el, 'y-blur', $event, myValue, item)"
              @focus="y.emit($el, 'y-focus', $event, myValue, item)" 
                class="form-check-input mt-0" v-attr="formAttrs" :value="index" :checked="index === myValueIndex">
              <label @click.stop :for="getItemId(index)">{{ getMenuTitle(item) }}</label>
            </div>
        </template>
    </div>
</template>
<script lang="ts" setup>
import y from '@/lib/ydecloud'
import {ref, watch, onMounted, computed} from 'vue'
const { iterateIndex, parentIterateIndex, attrs, formAttrs, css, style, items } = defineProps({
// 该组件被迭代时的索引
iterateIndex: Number,
// 父组件被迭代时的索引
parentIterateIndex: Number,
attrs: Object,
formAttrs: Object,
css: {
    type: [Object , String]
},
style: String,
items:{
    required: true,
    type:[Array<string|number>, Array<{ [key: string]: string | number | boolean | undefined }>]
},
})
const emit = defineEmits(['blur','change','click','dblclick','focus','input','mousedown','mouseup','mouseover','mouseout','mousemove','mouseenter','mouseleave'])
const model = defineModel()
const myValue = ref<string>('')
const myValueTitle = ref<string>('')
const myValueIndex = ref<number>(-1)
const uuid = computed(() => attrs?.['data-uiid'])

watch(myValue, (n, old) => {
  emit('change', n, old)
  model.value = n
})
function getItemId(index: number){
  return uuid.value + '-' + (iterateIndex||parentIterateIndex||"") + '-' + index
}
function getDefaultValue(){
    const item = items.find((item) => typeof item === 'object' && item.checked)
    return String((typeof item === 'object' ? (item.value || item.name) : item) || '')
}
function getMenuTitle(menu: string|number|{ [key: string]: string | number | boolean | undefined }) {
  return String(typeof menu === 'object' ? (menu.name || menu.value) : menu)
}
function updateValue(index: number, menu: string|number|{ [key: string]: string | number | boolean | undefined }) {
  myValue.value = String(typeof menu === 'object' ? (menu.value || menu.name || index) : menu)
  myValueIndex.value = index
  myValueTitle.value = String(typeof menu === 'object' ? (menu.name || menu.value) : menu)
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
  