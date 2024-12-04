<template>
  <div v-attr="attrs" :class="css" :style="style" :data-index="iterateIndex">
      <div class='model-position'>
        <div class='modal-dialog'>
          <div :class="contentCss" :style="contentStyle">
            <div class='modal-header align-items-center' v-if="$slots.header">
              <div class='d-flex move-handler'>
                <slot name="header"></slot>
              </div>
              <button type="button" onclick="y.emit($el, 'y-close')" data-bs-dismiss="modal" class="btn-close" ></button>
            </div>
            <div class='modal-body'
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
              <slot name="body"></slot>
            </div>
            <div class='modal-footer' v-if="$slots.footer">
              <slot name="footer"></slot>
            </div>
          </div>
        </div>
      </div>
  </div>
</template>
<script lang="ts" setup>
import y from '@/lib/ydecloud'
const { iterateIndex, attrs, boundData, css, boundValue, style, contentCss, contentStyle } = defineProps({
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
},
contentCss: {
  type: [Object , String, Array]
},
contentStyle: {
  type: [Object , String, Array]
}
})
const emit = defineEmits(['blur','click','close','dblclick','scroll','focus','mousedown','mouseup','mouseover','mouseout','mousemove','mouseenter','mouseleave'])
</script>
