<template>
  <div :class="[css,'d-flex align-items-center align-content-center p-0']">
    <div class="flex-fill cursor ">
      <input type="color" ref="colorInput" class="d-none" v-model="myColor">
      <div class="input-group input-group-sm">
        <span class="input-group-text text-truncate ps-1 pe-1" style="max-width: 60px">{{ t('style.alpha') }}</span>
        <input type="number" v-model="colorOpacity" step="0.01" max="1" min="0" class="form-control form-control-sm p-1">
        <div class="form-control form-control-sm p-0" style="max-width: 30px">
          <div class="h-100" @click="toggleColor" :style="colorStyle">&nbsp;</div>
        </div>
      </div>
    </div>
    <div @click="clearColor" class="cursor flex-grow-0 p-0 flex-shrink-0"><i class="iconfont icon-remove"></i></div>
  </div>
</template>

<script lang="ts">
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import ydhl from '@/lib/ydhl'

export default {
  props: {
    modelValue: String,
    css: String
  },
  emits: ['update:modelValue', 'clearColor'],
  name: 'ColorPicker',
  setup (props: any, context: any) {
    const colorInput = ref()
    const { t } = useI18n()
    const myColor = computed({
      get () {
        return props.modelValue
      },
      set (v) {
        context.emit('update:modelValue', v || undefined)
      }
    })
    const colorStyle = computed(() => {
      if (myColor.value) {
        return `background-color:${myColor.value}`
      }
      const transparent = require('@/assets/image/transparent.svg')
      // console.log(localColor.value)
      return `background-image:url(${transparent});background-size: contain;`
    })
    const colorOpacity = computed({
      get () {
        const rgba = ydhl.getRgbaInfo(props.modelValue)
        return rgba.a
      },
      set (v) {
        const rgba = ydhl.getRgbaInfo(props.modelValue)
        rgba.a = v
        context.emit('update:modelValue', '#' + ydhl.rgba2hex(`rgba(${rgba.r},${rgba.g},${rgba.b},${rgba.a})`))
      }
    })
    const toggleColor = () => {
      colorInput.value.click()
    }
    const clearColor = () => {
      myColor.value = ''
      context.emit('clearColor', '')
    }
    return {
      t,
      myColor,
      colorStyle,
      colorInput,
      colorOpacity,
      toggleColor,
      clearColor
    }
  }
}
</script>
