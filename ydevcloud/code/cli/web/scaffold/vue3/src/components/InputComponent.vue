<template>
  <div style="position: relative;" data-root :data-index="iterateIndex">
    <template v-if="iconOnLeft">
      <div :class="icon" style="position:absolute;left:10px;top:0px;height:100%;align-items:center;display:flex;"></div>
    </template>
    <input v-attr="attrs"
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
      :class="css" :maxlength="maxLength" :style="[inputStyle, style]" v-model="myValue">
    <template v-if="hasAction">
      <div :style="actionStyle" :class="actionClass">
        <div v-if="iconOnRight" :class="icon"></div>
        <template v-if="wordCountVisible"><span class="word-count">{{myValue?.length}}<template v-if="maxLength"> / {{maxLength}}</template></span></template>
        <div v-if="clearButtonVisible" @click='clear' class="cursor">×</div>
      </div>
    </template>
  </div>
</template>
<script lang="ts" setup>

import {ref,computed, watch, onMounted, nextTick} from 'vue'
import y from '@/lib/ydecloud'
const { iterateIndex, icon, iconPosition, color, foregroundCss, boundData, wordCountVisible, clearButtonVisible, defaultValue, maxLength, attrs, css, style } = defineProps({
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
  // icon 样式
  icon: String,
  // icon位置，left top right bottom
  iconPosition: String,
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
  css: {
    type: [Object , String, Array]
  },
  style: String
})
let needSyncModel = true
const model = defineModel<any>()
const emit = defineEmits(['blur','change','click','dblclick','focus','input','keyup','keydown','keypress','mousedown','mouseup','mouseover','mouseout','mousemove','mouseenter','mouseleave'])
const myValue = ref<string>('')
const hasAction = computed(() => icon || wordCountVisible || clearButtonVisible ? true : false)
const iconOnRight = computed(() => iconPosition == 'right' || iconPosition == 'bottom')
const iconOnLeft = computed(() => iconPosition == 'left' || iconPosition == 'top')
const actionStyle = computed(() => 'position:absolute;right:10px;top:0px;height:100%;align-items:center;display:flex;gap:10px;' + (color ? 'color:'+color : ''))
const actionClass = computed(() => (!color && foregroundCss) ? foregroundCss : null)
const inputStyle = computed(() => {
  if (!iconPosition) return ''
  return ['bottom','right'].indexOf(iconPosition) !== -1 ? 'padding-right: 60px;' : 'padding-left: 30px;'
})
watch(myValue, (n, old) => {
  emit('change', n, old)
  if (needSyncModel){
    model.value = n
  }
})

watch(model, (n) => {
  needSyncModel = false
  myValue.value = n
  nextTick(() => {
    needSyncModel = true
  })
})

function clear() {
  myValue.value = ''
}
onMounted(() => {
  // 如果默认有值，则用默认的值
  if (defaultValue){
    model.value = defaultValue
    myValue.value = String(defaultValue)
  }else{
    myValue.value = model.value
  }
})
</script>
