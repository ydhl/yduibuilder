<template>
  <div style="position: relative;" data-root :data-index="iterateIndex">
    <textarea v-attr="attrs" :class="css" :style="[textareaStyle, style]" v-model="myValue"
      @blur="y.emit($el, 'y-blur', $event, myValue, boundData)"
      @click="y.emit($el, 'y-click', $event, myValue, boundData)"
      @dblclick="y.emit($el, 'y-dblclick', $event, myValue, boundData)"
      @focus="y.emit($el, 'y-focus', $event, myValue, boundData)" 
      @input="y.emit($el, 'y-input', $event, myValue, boundData)"
      @keyup="y.emit($el, 'y-keyup', $event, $event.code, myValue, boundData)"
      @keydown="y.emit($el, 'y-keydown', $event, $event.code, myValue, boundData)"
      @keypress="y.emit($el, 'y-keypress', $event, $event.code, myValue, boundData)"
      @mousedown="y.emit($el, 'y-mousedown', $event, myValue, boundData)"
      @mouseup="y.emit($el, 'y-mouseup', $event, myValue, boundData)"
      @mouseover="y.emit($el, 'y-mouseover', $event, myValue, boundData)"
      @mouseout="y.emit($el, 'y-mouseout', $event, myValue, boundData)"
      @mousemove="y.emit($el, 'y-mousemove', $event, myValue, boundData)"
      @mouseenter="y.emit($el, 'y-mouseenter', $event, myValue, boundData)"
      @mouseleave="y.emit($el, 'y-mouseleave', $event, myValue, boundData)"
    ></textarea>
    <template v-if="hasAction">
      <div :style="actionStyle" :class="actionClass">
        <template v-if="wordCountVisible"><span class="word-count">{{myValue?.length}}<template v-if="maxLength"> / {{maxLength}}</template></span></template>
        <div v-if="clearButtonVisible" @click='clear' class="cursor">×</div>
      </div>
    </template>
  </div>
</template>
<script lang="ts" setup>
import y from '@/lib/ydecloud'
import {ref,computed, watch, onMounted} from 'vue'
const { iterateIndex, color, autoRow, foregroundCss, wordCountVisible, clearButtonVisible, defaultValue, maxLength, attrs, css, style } = defineProps({
  // 该组件被迭代时的索引
  iterateIndex: Number,
  // 父组件被迭代时的索引
  parentIterateIndex: Number,
  defaultValue: {
    type: [String, Number]
  },
  boundData: {
    type: [Object, Number, Boolean,String,Array]
  },
  attrs: Object,
  // 前景颜色值
  color: String,
  // 前景颜色样式
  foregroundCss: String,
  // 是否显示字数统计
  wordCountVisible: Boolean,
  // 是否显示清空按钮
  clearButtonVisible: Boolean,
  // 最大字数
  maxLength: Number,
  autoRow: Boolean,
  style: String,
  css: {
    type: [Object , String]
  }
})
const emit = defineEmits(['blur','change','click','dblclick','focus','input','keyup','keydown','keypress','mousedown','mouseup','mouseover','mouseout','mousemove','mouseenter','mouseleave'])
const model = defineModel<string>()
const myValue = ref<string>('')
const hasAction = computed(() => wordCountVisible || clearButtonVisible ? true : false)
const actionStyle = computed(() => 'position:absolute;right:10px;top:0px;height:100%;align-items:center;display:flex;gap:10px;' + (color ? 'color:'+color : ''))
const actionClass = computed(() => (!color && foregroundCss) ? foregroundCss : null)
const textareaStyle = computed(() => {
  return autoRow ? 'resize: none' : ''
})
watch(myValue, (n, old) => {
  emit('change', n, old)
  model.value = n
})

function clear() {
  myValue.value = ''
}
onMounted(() => {
  // 如果默认有值，则用默认的值
  if (defaultValue){
    model.value = String(defaultValue)
    myValue.value = String(defaultValue)
  }else{
    myValue.value = model.value||''
  }
})
</script>
