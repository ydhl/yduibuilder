<template>
  <component :dragableCss="{'ui': !myIsLock && !myIsReadonly, 'uicontainer':isContainer, 'dragenter-container': isDragIn}"
     ref="ui" :isLock="myIsLock" :isReadonly="myIsReadonly" :uiVersion="uiVersion"
     :is="uiComponentWrap" :key="uiconfig.meta.id" :uiconfig="uiconfig" :pageid="pageid">
  </component>
  <div v-if="showRect" :class="{'ui-rect':true, 'ui-hover': hoverUIItemId===uiconfig.meta.id, 'ui-selected': selectedUIItemId===uiconfig.meta.id, 'ui-highlight': highlightUIItemIds ? highlightUIItemIds.indexOf(uiconfig.meta.id)!=-1 : false}" :style="rectStyle">&nbsp;</div>
  <div v-if="showAction" class="ui-action" :style="actionStyle">
    <span><i :class="['iconfont', 'icon-' + uiconfig.type.toLowerCase()]"></i>{{uiconfig.type}}</span>
    <span class="text-muted">{{width}} ✕ {{height}}</span>
    <template v-if="uiconfig.dataIn?.path">
      <span class="text-primary"><i class="iconfont icon-data-input"></i>{{uiconfig.dataIn?.path}}</span>
    </template>
    <template v-if="uiconfig.dataOut">
      <span v-for="(path, outputAs, index) in uiconfig.dataOut" :key="index">
        <i class="iconfont icon-data-output"></i>{{path}}
        <span class="text-success" v-if="outputAs">&nbsp;{{outputAs}}&nbsp;</span>
        <span class="text-primary" v-if="uiconfig.dataBound?.VALUE == path">&nbsp;Value&nbsp;</span>
        <span class="text-primary" v-if="uiconfig.dataBound?.BOUND == path">&nbsp;Bound&nbsp;</span>
      </span>
    </template>
    <span v-if="uiconfig.events && uiconfig.events.length >0"><i class="iconfont icon-event"></i>{{uiconfig.events.length}} {{t('common.event')}}</span>
  </div>
</template>

<script lang="ts">
import { computed, defineAsyncComponent, ref, watch } from 'vue'
import UIBase from '@/components/ui/js/UIBase'
import { useStore } from 'vuex'

export default {
  name: 'UIBase',
  props: {
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String
  },
  setup (props: any, context: any) {
    const store = useStore()
    const uibase = new UIBase(props, context, store)
    const setup = uibase.setup()
    const inlineEditItemId = computed(() => store.state.page.inlineEditItemId)
    const width = computed(() => {
      if (!ui.value) return ''
      if (setup.hoverUIItemId.value !== props.uiconfig.meta.id && setup.selectedUIItemId.value !== props.uiconfig.meta.id) return ''
      const el = document.getElementById(props.uiconfig.meta.id)
      if (!el) return ''
      const { width } = el.getBoundingClientRect()
      return width
    })
    const height = computed(() => {
      if (!ui.value) return ''
      if (setup.hoverUIItemId.value !== props.uiconfig.meta.id && setup.selectedUIItemId.value !== props.uiconfig.meta.id) return ''
      const el = document.getElementById(props.uiconfig.meta.id)
      if (!el) return ''
      const { height } = el.getBoundingClientRect()
      return height
    })
    const ui = ref()
    const uiComponentWrap = computed(() => {
      // 这句判断的目的，只是为了让computed是响应式的，要不然下面defineAsyncComponent 中的promise不是响应式的
      // props改变后，uiComponentWrap不会刷新
      if (!props.uiconfig) return null
      return defineAsyncComponent(
        () => new Promise((resolve) => {
          // console.log(`@/components/ui/${ui.value}_${uiVersion.value}/${uiconfig.type}.vue`)
          require([`@/components/ui/${setup.ui.value}/${props.uiconfig.type}.vue`], resolve)
        }))
    })

    const showRect = computed(() => {
      if (inlineEditItemId.value !== '') return false
      const highlights = setup.highlightUIItemIds.value || []
      if (setup.hoverUIItemId.value === props.uiconfig.meta.id ||
        setup.selectedUIItemId.value === props.uiconfig.meta.id ||
        highlights.indexOf(props.uiconfig.meta.id) !== -1) return true
      return false
    })
    const showAction = computed(() => {
      if (inlineEditItemId.value !== '') return false
      if (setup.hoverUIItemId.value === props.uiconfig.meta.id ||
        setup.selectedUIItemId.value === props.uiconfig.meta.id) return true
      return false
    })
    // 用于观测显示标志和dom都是否准备好
    const watchAction = computed(() => (showAction.value ? '1' : '0') + (ui.value ? '1' : '0'))
    const watchRect = computed(() => (showRect.value ? '1' : '0') + (ui.value ? '1' : '0'))
    const rectStyle = ref('display:none')
    const actionStyle = ref('display:none')
    watch(watchRect, (v) => {
      if (v !== '11') {
        rectStyle.value = 'display:none'
        return
      }
      // nexttick 仍然会导致getBoundingClientRect获取的不是事件的元素数据
      setTimeout(() => {
        const el = document.getElementById(props.uiconfig.meta.id)
        if (!el) {
          rectStyle.value = 'display:none'
          return
        }
        const { width, height, x, y } = el.getBoundingClientRect()
        const marginLeft = parseFloat(window.getComputedStyle(el).getPropertyValue('margin-left')) || 0
        const marginTop = parseFloat(window.getComputedStyle(el).getPropertyValue('margin-top')) || 0
        const marginRight = parseFloat(window.getComputedStyle(el).getPropertyValue('margin-right')) || 0
        const marginBottom = parseFloat(window.getComputedStyle(el).getPropertyValue('margin-bottom')) || 0
        if (props.uiconfig.type === 'Page') {
          rectStyle.value = `top:0px;left:0px;transform:translateX(${x + 2}px) translateY(${y + 2}px);width:${width - 4}px;height:${height - 4}px`
          return
        }
        rectStyle.value = `top:0px;left:0px;transform:translateX(${x - marginLeft - 2}px) translateY(${y - marginTop - 2}px);width:${width + marginLeft + marginRight + 4}px;height:${height + marginTop + marginBottom + 4}px`
      }, 100)
    }, { immediate: true })
    watch(watchAction, (v) => {
      if (v !== '11') {
        rectStyle.value = 'display:none'
        return
      }

      setTimeout(() => {
        const el = document.getElementById(props.uiconfig.meta.id)
        if (!el) {
          actionStyle.value = 'display:none;'
          return
        }
        const { height, x, y } = el.getBoundingClientRect()
        const marginLeft = parseFloat(window.getComputedStyle(el).getPropertyValue('margin-left')) || 0
        const marginTop = parseFloat(window.getComputedStyle(el).getPropertyValue('margin-top')) || 0

        if (props.uiconfig.type === 'Page') {
          actionStyle.value = `transform:translateX(${x + 2}px) translateY(${y + height - 20}px);`
          return
        }
        actionStyle.value = `transform:translateX(${x - marginLeft - 5.2}px) translateY(${y + marginTop + 5 + height}px);`
      }, 100)
    }, { immediate: true })

    return {
      ...setup,
      ui,
      uiComponentWrap,
      rectStyle,
      actionStyle,
      showAction,
      showRect,
      width,
      height
    }
  }
}
</script>
