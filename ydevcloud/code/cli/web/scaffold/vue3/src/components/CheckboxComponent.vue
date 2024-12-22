<template>
    <div v-attr="attrs" :class="css" :style="style" data-root :data-index="iterateIndex">
      <template v-for="(item, index) in items" :key="index">
            <div class="form-check d-flex me-3 align-items-center"
            @click="updateChecked(index);y.emit($el, 'y-click', $event, myValues, item)"
            @dblclick="y.emit($el, 'y-dblclick', $event, myValues, item)"
              @input="y.emit($el, 'y-input', $event, myValues, item)"
              @mousedown="y.emit($el, 'y-mousedown', $event, myValues, item)"
              @mouseup="y.emit($el, 'y-mouseup', $event, myValues, item)"
              @mouseover="y.emit($el, 'y-mouseover', $event, myValues, item)"
              @mouseout="y.emit($el, 'y-mouseout', $event, myValues, item)"
              @mousemove="y.emit($el, 'y-mousemove', $event, myValues, item)"
              @mouseenter="y.emit($el, 'y-mouseenter', $event, myValues, item)"
              @mouseleave="y.emit($el, 'y-mouseleave', $event, myValues, item)">
              <input :id="getItemId(index)" type="checkbox" :value="valueList.getItemValue(index, item)"
              @blur="y.emit($el, 'y-blur', $event, myValues, item)"
              @focus="y.emit($el, 'y-focus', $event, myValues, item)" 
                class="form-check-input mt-0" v-attr="formAttrs" :checked="myValueIndexs.indexOf(index) !== -1">
              <label @click.stop :for="getItemId(index)">{{ valueList.getItemTitle(item) }}</label>
            </div>
        </template>
    </div>
</template>
<script lang="ts" setup>
import y from '@/lib/ydecloud'
import ValueList from './ValueList'
import {ref, onMounted, watch, computed,defineExpose} from 'vue'
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
const valueList = new ValueList(items)
const model = defineModel<any>()
const myValues = ref<Array<string>>([])
let modelIsArray = false
const myValueIndexs = ref<Array<number>>([])
const emit = defineEmits(['change'])
const uuid = computed(() => attrs?.['data-uiid'])
let needEmitChange = true

defineExpose({initModelFromXInput})
// 由x-input指令调用
function initModelFromXInput(n: any){
  model.value = n
}

function getItemId(index: number){
  return uuid.value + '-' + (iterateIndex||parentIterateIndex||"") + '-' + index
}
function updateChecked(index: number){
  if(modelIsArray){
    const existIndex = myValueIndexs.value.findIndex((item) => item == index)
    if (existIndex !== -1){
      myValueIndexs.value.splice(existIndex, 1)
    }else{
      myValueIndexs.value.push(index)
    }
  }else{
    myValueIndexs.value = [index]
  }
  const values = []
  for(const index of myValueIndexs.value){
    const item = items[index]
    let value
    if (typeof item === 'object') {
      value = String(item.value !== undefined ? item.value : (item.name || index))
    }else{
      value = String(item)
    }
    values.push(value)
  }
  myValues.value = values
  model.value = modelIsArray ? values : values?.[0]
}
watch(model, (n, old) => {
  if (!needEmitChange) {
    needEmitChange = true
    return
  }
  emit('change', n, old)
})
onMounted(() => {
  needEmitChange = model.value === undefined
  modelIsArray = model.value==undefined || Array.isArray(model.value)

  let defValues
  if (!valueList.isEmpty(model.value)){
    defValues = modelIsArray ? model.value : [model.value]
  }else{
    defValues = valueList.getDefaultValues()
  }
  if (!defValues || defValues.length === 0) return

  for(let i=0; i < items.length; i++){
    const item: any = items[i]
    const value = valueList.getItemValue(i, item)
    if (defValues.indexOf(value) !== -1)  myValueIndexs.value.push(i)
  }

  if (modelIsArray){
    myValues.value = defValues
    model.value = defValues
  }else{
    model.value = defValues?.[0]
    myValueIndexs.value.splice(1)
  }
})
</script>
  