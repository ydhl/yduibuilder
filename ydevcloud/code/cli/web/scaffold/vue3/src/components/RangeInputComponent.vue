<template>
  <input type='range' v-attr="attrs" :class="css" data-root :data-index="iterateIndex" v-model.number="myValue"
         :min="min" :max="max" :step="step"
         @click="y.emit($el, 'y-click', $event, myValue, boundData)"
        @dblclick="y.emit($el, 'y-dblclick', $event, myValue, boundData)"
        @input="y.emit($el, 'y-input', $event, myValue, boundData)"
        @mousedown="y.emit($el, 'y-mousedown', $event, myValue, boundData)"
        @mouseup="y.emit($el, 'y-mouseup', $event, myValue, boundData)"
        @mouseover="y.emit($el, 'y-mouseover', $event, myValue, boundData)"
        @mouseout="y.emit($el, 'y-mouseout', $event, myValue, boundData)"
        @mousemove="y.emit($el, 'y-mousemove', $event, myValue, boundData)"
        @mouseenter="y.emit($el, 'y-mouseenter', $event, myValue, boundData)"
        @mouseleave="y.emit($el, 'y-mouseleave', $event, myValue, boundData)"
        @blur="y.emit($el, 'y-blur', $event, myValue, boundData)"
        @focus="y.emit($el, 'y-focus', $event, myValue, boundData)"
         :style="`${style};background-size: ${(myValue - min) / (max - min) * 100 || 0}%`" :value="myValue">
</template>
<script lang="ts" setup>
import y from '@/lib/ydecloud'
import { onMounted, ref, watch, nextTick, defineExpose } from 'vue'
const { iterateIndex, attrs, css, style, min, max, step, defaultValue, boundData } = defineProps({
// 该组件被迭代时的索引
iterateIndex: Number,
// 父组件被迭代时的索引
parentIterateIndex: Number,
defaultValue: Number,
boundData: {
  type: [Object, Number, Boolean,String,Array]
},
min: {
  type: Number,
  default: 0
},
max: {
  type: Number,
  default: 100
},
step: {
  type: Number,
  default: 1
},
attrs: Object,
css: {
  type: [Object , String]
},
style: String
})
const myValue = ref(0)
const model = defineModel<number|string>()
const emit = defineEmits(['change'])
let needSyncModel = true

defineExpose({initModelFromXInput})
// 由x-input指令调用
function initModelFromXInput(n: any){
  model.value = n
}
watch(myValue, (n, old) => {
  emit('change', n, old)
  if (needSyncModel){
    model.value = n
  }
})

// 外面改变了model
watch(model, (n) => {
  needSyncModel = false
  myValue.value = Number(n)
  nextTick(() => {
    needSyncModel = true
  })
})

onMounted(() => {
  myValue.value = parseInt(String(model.value ||  defaultValue || min))
})
</script>