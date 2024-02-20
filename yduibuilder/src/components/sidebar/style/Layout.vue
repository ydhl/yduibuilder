<template>
  <div class="style-header">
    <i class="iconfont icon-tree-close"></i> {{ t('style.layout.layout') }}
    <i class="iconfont icon-point text-danger" v-if="hasSet"></i>
    <i class="iconfont icon-point text-success" v-if="hasInherit"></i>
  </div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.layout.display') }}</label>
      <div class="col-sm-9">
        <div class="input-group input-group-sm">
          <select class="form-select form-select-sm" v-model="currDisplay">
            <option :value="display" v-for="display in displays" :key="display" :selected="display===currDisplay">{{display}}</option>
          </select>
          <span class="input-group-text">{{ t('style.layout.xOverflow') }}</span>
          <select class="form-select form-select-sm" v-model="xOverflow">
            <option :value="name" v-for="(name) in overflows" :key="name">{{name}}</option>
          </select>
          <span class="input-group-text">{{ t('style.layout.yOverflow') }}</span>
          <select class="form-select form-select-sm" v-model="yOverflow">
            <option :value="name" v-for="(name) in overflows" :key="name">{{name}}</option>
          </select>
        </div>
      </div>
    </div>
    <div class="row" v-if="!isParentFlex">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.layout.float') }}</label>
      <div class="col-sm-9 d-flex flex-row align-items-center">
        <div class="input-group input-group-sm">
          <select class="form-select form-select-sm" v-model="currFloat">
            <option :value="float" v-for="float in floats" :key="float" :selected="float===currFloat">{{float}}</option>
          </select>
          <span class="input-group-text">{{ t('style.layout.clearFloat') }}</span>
          <select class="form-select form-select-sm" v-model="currClear">
            <option :value="clear" v-for="clear in clears" :key="clear" :selected="clear===currClear">{{clear}}</option>
          </select>
        </div>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.layout.position') }}</label>
      <div class="col-sm-9">
        <div class="input-group input-group-sm">
          <select class="form-select form-select-sm" v-model="currPosition">
            <option :value="position" v-for="position in positions" :key="position" :selected="position===currPosition">{{position}}</option>
          </select>
          <span class="input-group-text">{{ t('style.layout.zIndex') }}</span>
          <input class="form-control form-control-sm" type="text" v-model="zindex">
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-9 offset-sm-3">
        <div class="input-group input-group-sm">
          <span class="input-group-text">{{ t('style.layout.top') }}</span>
          <input class="form-control form-control-sm" type="text" v-model="positionTop">
          <span class="input-group-text">{{ t('style.layout.right') }}</span>
          <input class="form-control form-control-sm" type="text" v-model="positionRight">
          <span class="input-group-text">{{ t('style.layout.bottom') }}</span>
          <input class="form-control form-control-sm" type="text" v-model="positionBottom">
          <span class="input-group-text">{{ t('style.layout.left') }}</span>
          <input class="form-control form-control-sm" type="text" v-model="positionLeft">
        </div>
      </div>
    </div>

    <template v-if="isParentFlex">
      <div class="row mb-1">
        <div class="col-sm-9 offset-sm-3">
          <div class="input-group input-group-sm">
            <span class="input-group-text">{{ t('style.layout.shrink') }}</span>
            <input class="form-control form-control-sm" v-model="currShrink" type="number" min="0">
            <span class="input-group-text">{{ t('style.layout.grow') }}</span>
            <input class="form-control form-control-sm" v-model="currGrow" type="number" min="0">
            <span class="input-group-text">{{ t('style.layout.basis') }}</span>
            <input class="form-control form-control-sm" v-model="currBasis" type="text" placeholder="0%">
          </div>
        </div>
      </div>
    </template>
    <template v-if="isFlex">
      <div class="row">
        <label class="col-sm-3 col-form-label text-end">{{ t('style.layout.direction') }}</label>
        <div class="col-sm-9">
          <select class="form-select form-select-sm" v-model="currDirection">
            <option :value="direction" :key="direction" :selected="direction === currDirection" v-for="direction in directions">{{ direction }}</option>
          </select>
        </div>
      </div>

      <div class="row">
        <label class="col-sm-3 col-form-label text-end">{{ t('style.layout.justify')}}</label>
        <div class="col-sm-9">
          <select class="form-select form-select-sm" v-model="currJustify">
            <option :value="justify" :key="justify" :selected="justify === currJustify" v-for="justify in justifies">{{ justify }}</option>
          </select>
        </div>
      </div>

      <div class="row">
        <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.layout.alignItem')}}</label>
        <div class="col-sm-9">
          <select class="form-select form-select-sm" v-model="currAlignItem">
            <option :value="alignItem" :key="alignItem" :selected="alignItem === currAlignItem" v-for="alignItem in alignItems">{{ alignItem }}</option>
          </select>
        </div>
      </div>

      <div class="row">
        <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.layout.alignContent')}}</label>
        <div class="col-sm-9">
          <select class="form-select form-select-sm" v-model="currAlignContent">
            <option :value="alignContent" :key="alignContent" :selected="alignContent === currAlignContent" v-for="alignContent in alignContents">{{ alignContent }}</option>
          </select>
        </div>
      </div>

      <div class="row">
        <label class="col-sm-3 col-form-label text-end">{{ t('style.layout.wrap')}}</label>
        <div class="col-sm-9 d-flex flex-row align-items-center">
          <select class="form-select form-select-sm" v-model="currWrap">
            <option :value="wrap" :key="wrap" :selected="wrap === currWrap" v-for="wrap in wraps">{{ wrap }}</option>
          </select>
        </div>
      </div>
    </template>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'

export default {
  name: 'StyleLayout',
  props: {
    previewMode: Boolean
  },
  setup (props: any, context: any) {
    const info = initUI()
    const selectedUIItem = info.selectedUIItem
    const { t } = useI18n()
    const store = useStore()

    const displays = ref(['inherit', 'block', 'inline-block', 'flex', 'inline-flex'])
    const positions = ref(['static', 'absolute', 'relative', 'inherit', 'fixed'])
    const directions = ref(['row', 'row-reverse', 'column', 'column-reverse'])
    const floats = ref(['left', 'right', 'none'])
    const clears = ref(['left', 'right', 'both', 'none'])
    const justifies = ref(['flex-start', 'center', 'flex-end', 'space-between', 'space-around'])
    const alignItems = ref(['flex-start', 'center', 'flex-end', 'baseline', 'stretch'])
    const alignContents = ref(['flex-start', 'center', 'flex-end', 'space-between', 'space-around', 'stretch'])
    const wraps = ref(['nowrap', 'wrap', 'wrap-reverse'])
    const overflows = ref(['visible', 'hidden', 'scroll', 'auto'])

    const isContainer = computed(() => {
      return selectedUIItem.value.meta.isContainer
    })
    const parentUIItem = computed(() => {
      if (!info.selectedUIItemId.value) return null
      const { parentConfig } = store.getters.getUIItemInPage(info.selectedUIItemId.value, info.selectedPageId.value)
      return parentConfig
    })
    const currDisplay = info.computedWrap('display', 'style', 'inherit', false, props.previewMode)
    const currPosition = info.computedWrap('position', 'style', 'static', false, props.previewMode)
    const positionTop = info.computedWrap('top', 'style', '', false, props.previewMode)
    const positionRight = info.computedWrap('right', 'style', '', false, props.previewMode)
    const positionBottom = info.computedWrap('bottom', 'style', '', false, props.previewMode)
    const positionLeft = info.computedWrap('left', 'style', '', false, props.previewMode)
    const zindex = info.computedWrap('z-index', 'style', '', false, props.previewMode)
    const currDirection = info.computedWrap('flex-direction', 'style', '', false, props.previewMode)
    const currFloat = info.computedWrap('float', 'style', 'none', false, props.previewMode)
    const currClear = info.computedWrap('clear', 'style', 'none', false, props.previewMode)
    const currJustify = info.computedWrap('justify-content', 'style', 'flex-start', false, props.previewMode)
    const currShrink = info.computedWrap('flex-shrink', 'style', '', false, props.previewMode)
    const currGrow = info.computedWrap('flex-grow', 'style', '0', false, props.previewMode)
    const currBasis = info.computedWrap('flex-basis', 'style', '', false, props.previewMode)
    const currAlignItem = info.computedWrap('align-items', 'style', 'stretch', false, props.previewMode)
    const currAlignContent = info.computedWrap('align-content', 'style', 'stretch', false, props.previewMode)
    const currWrap = info.computedWrap('flex-wrap', 'style', 'nowrap', false, props.previewMode)
    const xOverflow = info.computedWrap('overflow-x', 'style', 'visible', false, props.previewMode)
    const yOverflow = info.computedWrap('overflow-y', 'style', 'visible', false, props.previewMode)
    const isFlex = computed(() => {
      return currDisplay.value !== undefined ? currDisplay.value.match(/flex|inline-flex/) : false
    })
    const isParentFlex = computed(() => {
      if (parentUIItem.value?.meta?.style?.display !== undefined) {
        return parentUIItem.value.meta.style.display.match(/flex|inline-flex/)
      } else if (parentUIItem.value?.meta?.selector?.style?.display !== undefined) {
        return parentUIItem.value.meta.selector.style.display.match(/flex|inline-flex/)
      }
      return false
    })
    const endKind = computed<string>(() => store.state.design.endKind)
    const hasInherit = computed(() => {
      return info.hasInheritStyle(
        'style',
        ['display', 'position', 'top', 'right', 'bottom', 'left', 'z-index', 'flex-direction', 'float', 'clear', 'justify-content', 'flex-shrink', 'flex-grow', 'flex-basis', 'align-items', 'align-content', 'flex-wrap', 'overflow-x', 'overflow-y']
      )
    })

    const hasSet = computed(() => {
      return info.hasSetStyle(
        'style',
        ['display', 'position', 'top', 'right', 'bottom', 'left', 'z-index', 'flex-direction', 'float', 'clear', 'justify-content', 'flex-shrink', 'flex-grow', 'flex-basis', 'align-items', 'align-content', 'flex-wrap', 'overflow-x', 'overflow-y']
      )
    })
    return {
      ...info,
      hasInherit,
      hasSet,
      displays,
      positions,
      directions,
      floats,
      clears,
      justifies,
      alignItems,
      alignContents,
      wraps,
      currDisplay,
      currPosition,
      positionTop,
      positionRight,
      positionBottom,
      positionLeft,
      zindex,
      currDirection,
      currFloat,
      currClear,
      currJustify,
      currAlignItem,
      currAlignContent,
      currWrap,
      currShrink,
      currGrow,
      currBasis,
      t,
      isFlex,
      isParentFlex,
      isContainer,
      xOverflow,
      yOverflow,
      overflows,
      endKind
    }
  }
}
</script>
