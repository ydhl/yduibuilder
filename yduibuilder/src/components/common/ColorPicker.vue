<template>
  <div :class="[css,'d-flex align-items-center align-content-center p-0']">
    <div class="flex-fill cursor">
      <input type="color" ref="colorInput" class="d-none" v-model="myColor">
      <div class="input-group flex-nowrap input-group-sm">
        <input type="number" style="width: 50px" :title="t('style.alpha')" v-model="myColorAlpha" step="1" max="100" min="0" class="form-control form-control-sm p-1">
        <div class="btn-group" style="border-top: 1px solid #ccc;border-bottom: 1px solid #ccc;border-right: 1px solid #ccc;border-top-right-radius: 0.2rem;border-bottom-right-radius: 0.2rem;">
          <div class="btn btn-light btn-sm p-0 bg-white rounded-0" @click="pickerColor" :style="`background-image:url(${transparent});background-size: contain;`">
            <div class="h-100" ref="colorDiv" :style="`width:30px;${colorStyle}`">&nbsp;</div>
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
              <div v-for="(color, index) in colorHistory" style="width: 20px;height: 20px;position: relative" :key="index"
                   @click="myColor=color" class="border"
                   :style="`background-image:url(${transparent});background-size: contain;`" @mouseenter.stop="hoverColorIndex=index" @mouseleave.stop="hoverColorIndex=-1">
                <div :style="`background-color:${color};width:100%;height:100%`">
                  <div v-if="hoverColorIndex==index" style="position: absolute;right: -10px;top: -15px">
                    <ConfirmRemove @remove="removeColor(index)"></ConfirmRemove>
                  </div>
                </div>
              </div>
            </div>
            </template>
          </div>
        </div>
      </div>
    </div>
    <div v-if="!hideRemove" @click="clearColor" :class="{'cursor flex-grow-0 p-0 flex-shrink-0':true, 'invisible':!modelValue}"><i class="iconfont icon-remove"></i></div>
  </div>
</template>

<script lang="ts" setup>
import { computed, ref, nextTick, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import ydhl from '@/lib/ydhl'
import _ from 'lodash'
import { useStore } from 'vuex'
import ConfirmRemove from '@/components/common/ConfirmRemove.vue'

const { modelValue, css, hideRemove } = defineProps({
  modelValue: {
    type: [String, Boolean]
  },
  css: String,
  hideRemove: Boolean
})
const emit = defineEmits(['update:modelValue', 'clearColor'])
const colorInput = ref()
const colorDiv = ref()
const { t } = useI18n()
const store = useStore()
const project = computed(() => store.state.design.project)
const customColors = computed(() => project.value?.customColors || [])
const colorHistory = computed(() => project.value?.colorHistory || [])
const transparent = require('@/assets/image/transparent.svg')
// const myColor = ref<any>(modelValue)
const tmpColor = ref(modelValue)// 用来生成dom的颜色值
watch(() => modelValue, (v) => {
  tmpColor.value = v
})
const myColorAlpha = computed<number>({
  get () {
    if (!modelValue) return 100
    const rgba = ydhl.getRgbaInfo(String(modelValue))
    return ~~(rgba.a * 100)
  },
  set: _.debounce((v) => {
    // 由于会使用变量，所以这里通过dom来获取颜色
    const computedStyle = window.getComputedStyle(colorDiv.value)
    const color = computedStyle.getPropertyValue('background-color')
    const rgba = ydhl.getRgbaInfo(color)
    rgba.a = v / 100
    tmpColor.value = '#' + ydhl.rgba2hex(`rgba(${rgba.r},${rgba.g},${rgba.b},${rgba.a})`)
    nextTick(() => {
      updateModelValue()
    })
  }, 800)
})
const myColor = computed<string|undefined, string|undefined>({
  get () {
    return modelValue ? String(modelValue) : undefined
  },
  set: _.debounce((v) => {
    // 由于会使用变量，所以这里通过dom来获取颜色
    tmpColor.value = v
    nextTick(() => {
      updateModelValue()
    })
  }, 800)
})
function updateModelValue () {
  const computedStyle = window.getComputedStyle(colorDiv.value)
  const bgcolor = computedStyle.getPropertyValue('background-color')
  const rgba = ydhl.getRgbaInfo(bgcolor)
  const color = '#' + ydhl.rgba2hex(`rgba(${rgba.r},${rgba.g},${rgba.b},${rgba.a})`)
  // console.log('color picker debounce', color)
  if (color && colorHistory.value?.findIndex((item) => item === color) === -1) {
    colorHistory.value.unshift(color)
    if (colorHistory.value.length > 50) {
      colorHistory.value.splice(50)
    }
    store.commit('updateProjectState', { name: 'colorHistory', value: colorHistory.value })
  }
  emit('update:modelValue', color || undefined)
}

const hoverColorIndex = ref(-1)
const colorStyle = computed(() => {
  if (tmpColor.value) {
    return `background-color:${tmpColor.value};`
  }
  return ''
})
const pickerColor = () => {
  colorInput.value.click()
}
const clearColor = () => {
  myColor.value = undefined
  emit('clearColor', '')
}
function removeColor (index) {
  colorHistory.value.splice(index, 1)
  store.commit('updateProjectState', { name: 'colorHistory', value: colorHistory.value })
}

</script>
