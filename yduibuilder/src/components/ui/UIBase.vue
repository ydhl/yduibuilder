<template>
  <component :dragableCss="{'ui': !myIsLock && !myIsReadonly, 'uicontainer':isContainer, 'dragenter-container': isDragIn}"
     ref="ui" :isLock="myIsLock" :isReadonly="myIsReadonly" :uiVersion="uiVersion"
     :is="uiComponentWrap" :key="uiconfig.meta.id" :uiconfig="uiconfig" :pageid="pageid">
  </component>
  <div :class="{'ui-hover': hoverUIItemId===uiconfig.meta.id, 'ui-selected': selectedUIItemId===uiconfig.meta.id, 'ui-highlight': highlightUIItemIds ? highlightUIItemIds.indexOf(uiconfig.meta.id)!=-1 : false}" :style="rectStyle">&nbsp;</div>
</template>

<script lang="ts">
import { computed, defineAsyncComponent } from 'vue'
import UIBase from '@/components/ui/js/UIBase'

export default {
  name: 'UIBase',
  props: {
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String
  },
  setup (props: any, context: any) {
    const uibase = new UIBase(props, context)
    const setup = uibase.setup()

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

    const rectStyle = computed(() => {
      const highlights = setup.highlightUIItemIds.value || []
      if (setup.hoverUIItemId.value !== props.uiconfig.meta.id && setup.selectedUIItemId.value !== props.uiconfig.meta.id && highlights.indexOf(props.uiconfig.meta.id) === -1) return 'display:none'
      const el = document.getElementById(props.uiconfig.meta.id)
      if (!el) {
        // console.log(el, props.uiconfig.meta.id)
        return 'display:none'
      }
      const { width, height, x, y } = el.getBoundingClientRect()
      const marginLeft = parseFloat(window.getComputedStyle(el).getPropertyValue('margin-left')) || 0
      const marginTop = parseFloat(window.getComputedStyle(el).getPropertyValue('margin-top')) || 0
      const marginRight = parseFloat(window.getComputedStyle(el).getPropertyValue('margin-right')) || 0
      const marginBottom = parseFloat(window.getComputedStyle(el).getPropertyValue('margin-bottom')) || 0

      if (props.uiconfig.type === 'Page') {
        return `top:0px;left:0px;transform:translateX(${x + 2}px) translateY(${y + 2}px);width:${width - 4}px;height:${height - 4}px`
      }
      return `top:0px;left:0px;transform:translateX(${x - marginLeft - 2}px) translateY(${y - marginTop - 2}px);width:${width + marginLeft + marginRight + 4}px;height:${height + marginTop + marginBottom + 4}px`
    })

    return {
      ...setup,
      uiComponentWrap,
      rectStyle
    }
  }
}
</script>
