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
              <label @click.stop :for="getItemId(index)">{{ valueList.getItemTitle(item) }}</label>
            </div>
        </template>
    </div>
</template>
<script lang="ts" setup>
import y from '@/lib/ydecloud'
import ValueList from './ValueList'
import {ref, watch, onMounted, computed, defineExpose} from 'vue'
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
const emit = defineEmits(['change'])
const model = defineModel<any>()
const myValue = ref<string>('')
const myValueTitle = ref<string>('')
const myValueIndex = ref<number>(-1)
const uuid = computed(() => attrs?.['data-uiid'])
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
function getItemId(index: number){
  return uuid.value + '-' + (iterateIndex||parentIterateIndex||"") + '-' + index
}
function updateValue(index: number, item: string|number|{ [key: string]: string | number | boolean | undefined }) {
  myValueIndex.value = index
  myValue.value = valueList.getItemValue(index, item)
  myValueTitle.value = valueList.getItemTitle(item)
}
onMounted(() => {
  const defValue = !valueList.isEmpty(model.value) ? model.value : valueList.getDefaultValue()
  myValue.value = defValue || ''
  myValueIndex.value = valueList.getItemIndexByValue(defValue)
  if (myValueIndex.value !== -1){
    myValueTitle.value = valueList.getItemTitle(items[myValueIndex.value])
  }
})
</script>
  