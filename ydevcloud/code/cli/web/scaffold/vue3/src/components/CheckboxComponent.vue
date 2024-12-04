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
              <input :id="getItemId(index)" type="checkbox" :value="getItemValue(index, item)"
              @blur="y.emit($el, 'y-blur', $event, myValues, item)"
              @focus="y.emit($el, 'y-focus', $event, myValues, item)" 
                class="form-check-input mt-0" v-attr="formAttrs" :checked="myValueIndexs.indexOf(index) !== -1">
              <label @click.stop :for="getItemId(index)">{{ getItemTitle(item) }}</label>
            </div>
        </template>
    </div>
</template>
<script lang="ts" setup>
import y from '@/lib/ydecloud'
import {ref, onMounted, watch, computed} from 'vue'
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
const model = defineModel()
const myValues = ref<Array<string>>([])
let modelIsArray = false
const myValueIndexs = ref<Array<number>>([])
const emit = defineEmits(['blur','change','click','dblclick','focus','input','mousedown','mouseup','mouseover','mouseout','mousemove','mouseenter','mouseleave'])
const uuid = computed(() => attrs?.['data-uiid'])
function getItemId(index: number){
  return uuid.value + '-' + (iterateIndex||parentIterateIndex||"") + '-' + index
}
function getDefaultValues(){
  const checkedItems = items.filter((item) => typeof item === 'object' && item.checked)
  const checkedValues: Array<string> = []
  for(const item of checkedItems){
    const i = item as { [key: string]: string }
    checkedValues.push(i.value || i.name)
  }
  return checkedValues
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
    const value = String(typeof item === 'object' ? (item.value || item.name || index) : item)
    values.push(value)
  }
  myValues.value = values
  model.value = modelIsArray ? values : values?.[0]
}
function getItemTitle(item: string|number|{ [key: string]: string | number | boolean | undefined }) {
  return String(typeof item === 'object' ? (item.name || item.value) : item)
}
function getItemValue(index: number, item: string|number|{ [key: string]: string | number | boolean | undefined }) {
  return String(typeof item === 'object' ? (item.value || item.name || index) : item)
}
watch(model, (n, old) => {
  emit('change', n, old)
})
onMounted(() => {
  modelIsArray = model.value==undefined || Array.isArray(model.value)
  const defValues = getDefaultValues()
  for(let index=0; index<items.length; index++){
    const item = items[index]
    const isObject = typeof item === 'object'
    if (isObject){
      if(defValues.findIndex((def) => item.value === def || item.name === def) !== -1){
        myValueIndexs.value.push(index)
      }
    }else{
      if (defValues.findIndex((def) => item === def) !== -1){
        myValueIndexs.value.push(index)
      }
    }
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
  