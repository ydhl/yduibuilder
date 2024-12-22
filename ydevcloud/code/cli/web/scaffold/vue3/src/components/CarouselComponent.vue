<template>
    <div v-attr="attrs" :class="css" data-ride="carousel" :style="style"
    :id="`${uuid}${iterateIndex||''}`" data-root :data-index="iterateIndex">
      <div class="carousel-indicators" v-if="showIndicator">
          <button v-for="index in itemCount" :key="index-1"
            :data-bs-target="`#${uuid}${iterateIndex||''}`" 
            :data-bs-slide-to="index-1" type="button"
            :class="{'active': activeIndex == index-1}"></button>
      </div>
      <div class="carousel-inner">
        <slot name="slide" :activeIndex="activeIndex" 
        :click="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-click', event, value, bound)"
        :dblclick="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-dblclick', event, value, bound)"
        :mousedown="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-mousedown', event, value, bound)"
        :mouseup="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-mouseup', event, value, bound)"
        :mouseover="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-mouseover', event, value, bound)"
        :mouseout="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-mouseout', event, value, bound)"
        :mousemove="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-mousemove', event, value, bound)"
        :mouseenter="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-mouseenter', event, value, bound)"
        :mouseleave="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-mouseleave', event, value, bound)"
        :blur="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-blur', event, value, bound)"
        :focus="(event: any, value: any, bound: any=null) =>y.emit($el, 'y-focus', event, value, bound)"
        ></slot>
      </div>
      <template v-if="showControl"></template>
      <a class="carousel-control-prev" type="button" :data-bs-target="`#${uuid}${iterateIndex||''}`" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      </a>
      <a class="carousel-control-next" type="button" :data-bs-target="`#${uuid}${iterateIndex||''}`" data-bs-slide='next'>
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
      </a>
    </div>
</template>
<script lang="ts" setup>
import y from '@/lib/ydecloud'
import {ref, computed, watch} from 'vue'
const { iterateIndex, attrs, css, style, defaultActiveIndex, showIndicator, showControl, itemCount } = defineProps({
// 该组件被迭代时的索引
iterateIndex: Number,
// 父组件被迭代时的索引
parentIterateIndex: Number,
attrs: Object,
css: {
    type: [Object , String]
},
style: String,
showIndicator: Boolean,
showControl: Boolean,
itemCount: Number,
defaultActiveIndex: Number
})
const emit = defineEmits(['change'])
const activeIndex = ref<number>(defaultActiveIndex || 0)
const uuid = computed(() => attrs?.['data-uiid'])
watch(activeIndex, (n, old) => {
  emit('change', n, old)
})
</script>
  