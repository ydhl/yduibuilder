<template>
  <div v-attr="attrs" :class="css" :style="style" :data-index="iterateIndex"
      @click="y.emit($el, 'y-click', $event, boundValue, boundData)"
      @dblclick="y.emit($el, 'y-dblclick', $event, boundValue, boundData)"
      @mousedown="y.emit($el, 'y-mousedown', $event, boundValue, boundData)"
      @mouseup="y.emit($el, 'y-mouseup', $event, boundValue, boundData)"
      @mouseover="y.emit($el, 'y-mouseover', $event, boundValue, boundData)"
      @mouseout="y.emit($el, 'y-mouseout', $event, boundValue, boundData)"
      @mousemove="y.emit($el, 'y-mousemove', $event, boundValue, boundData)"
      @mouseenter="y.emit($el, 'y-mouseenter', $event, boundValue, boundData)"
      @mouseleave="y.emit($el, 'y-mouseleave', $event, boundValue, boundData)">
      <div role="progressbar" :class="barCss" :aria-valuenow="progress"
      :aria-valuemin="min" :aria-valuemax="max"
      :style="`width:${progress}%`"><slot></slot></div>
  </div>
</template>
<script lang="ts" setup>
import y from '@/lib/ydecloud'
const { iterateIndex, attrs, boundData, css, boundValue, style, barCss, progress, min, max } = defineProps({
// 该组件被迭代时的索引
iterateIndex: Number,
// 父组件被迭代时的索引
parentIterateIndex: Number,
progress: Number,
min: Number,
max: Number,
attrs: Object,
boundData: {
  type: [Object, Number, Boolean,String,Array]
},
boundValue: {
  type: [Object, Number, Boolean,String,Array]
},
css: {
  type: [Object , String, Array]
},
barCss: {
  type: [Object , String, Array]
},
style: {
  type: [Object , String, Array]
}
})
</script>
