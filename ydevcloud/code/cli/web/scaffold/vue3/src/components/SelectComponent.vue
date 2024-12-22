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
        :value="valueList.getItemValue(index, item)">{{ valueList.getItemTitle(item) }}</option>
      </select>
    </div>
</template>
<script lang="ts" setup>
import y from '@/lib/ydecloud'
import ValueList from './ValueList'
import {ref, onMounted, watch, defineExpose} from 'vue'
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
const model = defineModel<any>()
const myValues = ref<any>()
let modelIsArray = false
const emit = defineEmits(['change'])
const valueList = new ValueList(items)
let needEmitChange = true

defineExpose({initModelFromXInput})
// 由x-input指令调用
function initModelFromXInput(n: any){
  model.value = n
}
watch(myValues, (newValues, old) => {
  if (multiple && Array.isArray(newValues)){// 多值
    model.value = modelIsArray ? newValues : newValues?.[0]
  }else{// 单值
    model.value = modelIsArray ? [newValues] : newValues
  }

  if (!needEmitChange) {
    needEmitChange = true
    return
  }
  emit('change', newValues, old)
})

onMounted(() => {
  needEmitChange = model.value === undefined
  modelIsArray = model.value !== undefined || Array.isArray(model.value)
  // 绑定的输入有值，优先用输入值
  if (!valueList.isEmpty(model.value)){
    if (multiple) {
      myValues.value = modelIsArray ? model.value : [model.value]
    }else{
      myValues.value = modelIsArray ? model.value?.[0] : model.value
    }
    return
  }
  if (multiple) {
    myValues.value = valueList.getDefaultValues()
  }else{
    myValues.value = valueList.getDefaultValue()
  }
})
</script>
<style scoped>
select{
  font:inherit;color:inherit
}
</style>