<template>
  <div :class="[css,'d-flex align-items-center align-content-center p-0']">
    <div class="flex-fill cursor">
      <input type="color" ref="colorInput" class="d-none" v-model="myColor">
      <div class="input-group input-group-sm">
        <span class="input-group-text text-truncate ps-1 pe-1" style="max-width: 60px">{{ t('style.alpha') }}</span>
        <input type="number" v-model="colorOpacity" step="1" max="100" min="0" class="form-control form-control-sm p-1">
        <div class="btn-group" style="border-top: 1px solid #ccc;border-bottom: 1px solid #ccc;border-right: 1px solid #ccc;border-top-right-radius: 0.2rem;border-bottom-right-radius: 0.2rem;">
          <div class="btn btn-light btn-sm p-0 bg-white rounded-0" @click="toggleColor">
            <div class="h-100" :style="`width:30px;${colorStyle}`">&nbsp;</div>
          </div>
          <div class="btn btn-light btn-sm dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false"></div>
          <div class="dropdown-menu dropdown-menu-end">
            <template v-if="colorHistory.length == 0 && customColors.length ==0">
              <div class="p-2">
                {{t('theme.noCustomColor')}}
              </div>
            </template>
            <div class="dropdown-item" :style="`color:${customColor.color};`" @click="myColor=`var(--${customColor.uuid})`"
                 v-for="(customColor, index) in customColors" :key="index">{{customColor.name}}</div>
            <template v-if="colorHistory.length > 0">
            <div><hr class="dropdown-divider"></div>
            <div class="p-2 d-flex gap-2 flex-wrap">
              <div v-for="(color, index) in colorHistory" style="width: 20px;height: 20px" :key="index"
                   @click="myColor=color"
                   :style="`background-color:${color};`"></div>
            </div>
            </template>
          </div>
        </div>
      </div>
    </div>
    <div @click="clearColor" :class="{'cursor flex-grow-0 p-0 flex-shrink-0':true, 'invisible':!modelValue}"><i class="iconfont icon-remove"></i></div>
  </div>
</template>

<script lang="ts">
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import ydhl from '@/lib/ydhl'
import _ from 'lodash'
import { useStore } from 'vuex'

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
    const store = useStore()
    const project = computed(() => store.state.design.project)
    const customColors = computed(() => project.value?.customColors || [])
    const colorHistory = computed(() => project.value?.colorHistory || [])
    const myColor = computed({
      get () {
        return props.modelValue
      },
      set: _.debounce((v) => {
        if (v && colorHistory.value?.findIndex((item) => item === v) === -1) {
          colorHistory.value.unshift(v)
          if (colorHistory.value.length > 10) {
            colorHistory.value.splice(10)
          }
          store.commit('updateProjectState', { name: 'colorHistory', value: colorHistory.value })
        }
        context.emit('update:modelValue', v || undefined)
      }, 800)
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
        return Math.ceil(rgba.a * 100)
      },
      set (v: any) {
        const rgba = ydhl.getRgbaInfo(props.modelValue)
        rgba.a = v / 100
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
      customColors,
      colorHistory,
      toggleColor,
      clearColor
    }
  }
}
</script>
