<template>
  <div class="d-flex flex-column align-items-start position">
    <div class="d-flex justify-content-around align-content-center align-items-center">
      <div :class="{'lt item btn': true, 'btn-primary': currValue=='left-top'}" @click="update('left','top',)"><i class="iconfont icon-lt"></i></div>
      <div :class="{'ct item btn': true, 'btn-primary': currValue=='center-top'}" @click="update('center','top')"><i class="iconfont icon-ct"></i></div>
      <div :class="{'rt item btn': true, 'btn-primary': currValue=='right-top'}" @click="update('right','top')"><i class="iconfont icon-rt"></i></div>
    </div>
    <div class="d-flex justify-content-between align-content-center align-items-center">
      <div :class="{'lc item btn': true, 'btn-primary': currValue=='left-center'}" @click="update('left','center')"><i class="iconfont icon-lc"></i></div>
      <div :class="{'cc item btn': true, 'btn-primary': currValue=='center-center'}" @click="update('center','center')"><i class="iconfont icon-cc"></i></div>
      <div :class="{'rc item btn': true, 'btn-primary': currValue=='right-center'}" @click="update('right','center')"><i class="iconfont icon-rc"></i></div>
    </div>
    <div class="d-flex justify-content-between align-content-center align-items-center">
      <div :class="{'lb item btn': true, 'btn-primary': currValue=='left-bottom'}" @click="update('left','bottom')"><i class="iconfont icon-lb"></i></div>
      <div :class="{'cb item btn': true, 'btn-primary': currValue=='center-bottom'}" @click="update('center','bottom')"><i class="iconfont icon-cb"></i></div>
      <div :class="{'rb item btn': true, 'btn-primary': currValue=='right-bottom'}" @click="update('right','bottom')"><i class="iconfont icon-rb"></i></div>
    </div>
  </div>
</template>

<script lang="ts">
import { computed } from 'vue'

export default {
  name: 'Position',
  emits: ['update:modelValue'],
  props: {
    width: Number,
    height: Number,
    modelValue: Array
  },
  setup (props: any, context: any) {
    const update = (x, y) => {
      context.emit('update:modelValue', [x, y])
    }
    const currValue = computed(() => {
      // console.log(props.modelValue.join('-'))
      return props.modelValue.join('-')
    })
    return {
      update,
      currValue
    }
  }
}
</script>
