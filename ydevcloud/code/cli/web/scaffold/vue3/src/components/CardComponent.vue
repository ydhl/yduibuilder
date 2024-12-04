<template>
  <div v-attr="attrs" :class="css" :style="style" :data-index="iterateIndex"
        @click="y.emit($el, 'y-click', $event, boundValue, boundData)"
        @dblclick="y.emit($el, 'y-dblclick', $event, boundValue, boundData)"
        @scroll="y.emit($el, 'y-scroll', $event, boundValue, boundData)"
        @mousedown="y.emit($el, 'y-mousedown', $event, boundValue, boundData)"
        @mouseup="y.emit($el, 'y-mouseup', $event, boundValue, boundData)"
        @mouseover="y.emit($el, 'y-mouseover', $event, boundValue, boundData)"
        @mouseout="y.emit($el, 'y-mouseout', $event, boundValue, boundData)"
        @mousemove="y.emit($el, 'y-mousemove', $event, boundValue, boundData)"
        @mouseenter="y.emit($el, 'y-mouseenter', $event, boundValue, boundData)"
        @mouseleave="y.emit($el, 'y-mouseleave', $event, boundValue, boundData)">
    <div class='card-header' v-if="$slots.header">
        <slot name="header"></slot>
    </div>
    <div class='card-body' v-if="$slots.inbody">
      <slot name="inbody"></slot>
    </div>
    <slot name="outbody"></slot>
    <div class='card-footer' v-if="$slots.footer">
      <slot name="footer"></slot>
    </div>
  </div>
</template>
<script lang="ts" setup>
import y from '@/lib/ydecloud'
const { iterateIndex, attrs, boundData, css, boundValue, style } = defineProps({
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
const emit = defineEmits(['blur','click','dblclick','scroll','focus','mousedown','mouseup','mouseover','mouseout','mousemove','mouseenter','mouseleave'])
</script>
