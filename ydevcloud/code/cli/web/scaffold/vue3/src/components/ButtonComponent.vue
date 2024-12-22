<template>
  <template v-if="buttonType=='link'">
    <a v-attr="attrs" :class="css" :style="style" :data-index="iterateIndex"
        @click="y.emit($el, 'y-click', $event, boundValue, boundData)"
        @dblclick="y.emit($el, 'y-dblclick', $event, boundValue, boundData)"
        @scroll="y.emit($el, 'y-scroll', $event, boundValue, boundData)"
        @mousedown="y.emit($el, 'y-mousedown', $event, boundValue, boundData)"
        @mouseup="y.emit($el, 'y-mouseup', $event, boundValue, boundData)"
        @mouseover="y.emit($el, 'y-mouseover', $event, boundValue, boundData)"
        @mouseout="y.emit($el, 'y-mouseout', $event, boundValue, boundData)"
        @mousemove="y.emit($el, 'y-mousemove', $event, boundValue, boundData)"
        @mouseenter="y.emit($el, 'y-mouseenter', $event, boundValue, boundData)"
        @mouseleave="y.emit($el, 'y-mouseleave', $event, boundValue, boundData)"
        @blur="y.emit($el, 'y-blur', $event, boundValue, boundData)"
        @focus="y.emit($el, 'y-focus', $event, boundValue, boundData)" >
      <slot></slot>
    </a>
  </template>
  <template v-else>
    <button v-attr="attrs" :class="css" :style="style" :data-index="iterateIndex"
        @click="y.emit($el, 'y-click', $event, boundValue, boundData)"
        @dblclick="y.emit($el, 'y-dblclick', $event, boundValue, boundData)"
        @scroll="y.emit($el, 'y-scroll', $event, boundValue, boundData)"
        @mousedown="y.emit($el, 'y-mousedown', $event, boundValue, boundData)"
        @mouseup="y.emit($el, 'y-mouseup', $event, boundValue, boundData)"
        @mouseover="y.emit($el, 'y-mouseover', $event, boundValue, boundData)"
        @mouseout="y.emit($el, 'y-mouseout', $event, boundValue, boundData)"
        @mousemove="y.emit($el, 'y-mousemove', $event, boundValue, boundData)"
        @mouseenter="y.emit($el, 'y-mouseenter', $event, boundValue, boundData)"
        @mouseleave="y.emit($el, 'y-mouseleave', $event, boundValue, boundData)"
        @blur="y.emit($el, 'y-blur', $event, boundValue, boundData)"
        @focus="y.emit($el, 'y-focus', $event, boundValue, boundData)">
      <slot></slot>
    </button>
  </template>
</template>
<script lang="ts" setup>
import y from '@/lib/ydecloud'
const { iterateIndex, attrs, boundData, css, boundValue, style, buttonType } = defineProps({
// 该组件被迭代时的索引
iterateIndex: Number,
// 父组件被迭代时的索引
parentIterateIndex: Number,
buttonType: String,
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
style: {
  type: [Object , String, Array]
}
})
</script>
