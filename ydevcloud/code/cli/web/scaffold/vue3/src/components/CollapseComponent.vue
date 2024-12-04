<template>
  <div v-attr="attrs" :class="css" :style="style" :data-index="iterateIndex">
      <slot :click="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-click', event, value, bound)"
        :dblclick="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-dblclick', event, value, bound)"
        :mousedown="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-mousedown', event, value, bound)"
        :mouseup="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-mouseup', event, value, bound)"
        :mouseover="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-mouseover', event, value, bound)"
        :mouseout="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-mouseout', event, value, bound)"
        :mousemove="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-mousemove', event, value, bound)"
        :mouseenter="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-mouseenter', event, value, bound)"
        :mouseleave="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-mouseleave', event, value, bound)"
        :blur="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-blur', event, value, bound)"
        :focus="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-focus', event, value, bound)"></slot>
  </div>
</template>
<script lang="ts" setup>
import y from '@/lib/ydecloud'
const { iterateIndex, attrs, css, style } = defineProps({
// 该组件被迭代时的索引
iterateIndex: Number,
// 父组件被迭代时的索引
parentIterateIndex: Number,
buttonType: String,
attrs: Object,

css: {
  type: [Object , String, Array]
},
style: {
  type: [Object , String, Array]
}
})
const emit = defineEmits(['blur','click','dblclick','scroll','focus','mousedown','mouseup','mouseover','mouseout','mousemove','mouseenter','mouseleave'])
</script>
