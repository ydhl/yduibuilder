<template>
    <div v-attr="attrs" :class="css" :style="style" data-root :data-index="iterateIndex">
      <select :multiple="multiple" v-attr="formAttrs" :class="selectCss" v-model="myValues">
        <option v-for="(item, index) in items" :key="index" 
        @dblclick="y.emit($el, 'y-dblclick', $event, myValues, item)"
        @input="y.emit($el, 'y-input', $event, myValues, item)"
        @mousedown="y.emit($el, 'y-mousedown', $event, myValues, item)"
        @mouseup="y.emit($el, 'y-mouseup', $event, myValues, item)"
        @mouseover="y.emit($el, 'y-mouseover', $event, myValues, item)"
        @mouseout="y.emit($el, 'y-mouseout', $event, myValues, item)"
        @mousemove="y.emit($el, 'y-mousemove', $event, myValues, item)"
        @mouseenter="y.emit($el, 'y-mouseenter', $event, myValues, item)"
        @mouseleave="y.emit($el, 'y-mouseleave', $event, myValues, item)"
        @click="y.emit($el, 'y-click', $event, myValues, item);"
        @blur="y.emit($el, 'y-blur', $event, myValues, item)"
        @focus="y.emit($el, 'y-focus', $event, myValues, item)"
        :value="getItemValue(index, item)">{{ getItemTitle(item) }}</option>
      </select>
    </div>
</template>
<script lang="ts" setup>
import y from '@/lib/ydecloud'
import {ref, onMounted, watch} from 'vue'
const { iterateIndex, attrs, formAttrs, css, selectCss, multiple, style, items } = defineProps({
// 该组件被迭代时的索引
iterateIndex: Number,
// 父组件被迭代时的索引
parentIterateIndex: Number,
attrs: Object,
formAttrs: Object,
multiple: Boolean,
css: {
    type: [Object , String]
},
selectCss: {
    type: [Object , String]
},
style: String,
items:{
    required: true,
    type:[Array<string|number>, Array<{ [key: string]: string | number | boolean | undefined }>]
},
})
const model = defineModel()
const myValues = ref<Array<string>|string>()
let modelIsArray = false
const emit = defineEmits(['blur','change','click','dblclick','focus','input','mousedown','mouseup','mouseover','mouseout','mousemove','mouseenter','mouseleave'])

watch(myValues, (newValues, old) => {
  if (multiple && Array.isArray(newValues)){// 多值
    model.value = modelIsArray ? newValues : newValues?.[0]
  }else{// 单值
    model.value = modelIsArray ? [newValues] : newValues
  }

  emit('change', newValues, old)
})

function getItemTitle(item: string|number|{ [key: string]: string | number | boolean | undefined }) {
  return String(typeof item === 'object' ? (item.name || item.value) : item)
}

function getItemValue(index: number, item: string|number|{ [key: string]: string | number | boolean | undefined }) {
  return String(typeof item === 'object' ? (item.value || item.name || index) : item)
}
onMounted(() => {
  modelIsArray = model.value==undefined || Array.isArray(model.value)
  if(multiple){
    myValues.value = []
  }else{
    myValues.value = ''
  }
  for(let index=0; index<items.length; index++){
    const item = items[index]
    if (typeof item !== 'object' || !item.checked) continue
    if(multiple){
      (myValues.value as Array<string>).push(getItemValue(index, item))
    }else{
      myValues.value = getItemValue(index, item)
    }
  }
  if (myValues.value){
    if (modelIsArray){
      model.value = multiple ? myValues.value : [myValues.value]
    }else{
      model.value = multiple ? myValues.value?.[0] : myValues.value
    }
  }
})
</script>
<style scoped>
select{
  font:inherit;color:inherit
}
</style>