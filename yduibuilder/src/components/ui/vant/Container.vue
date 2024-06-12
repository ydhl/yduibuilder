<template>
  <div :draggable='draggable' :class="[dragableCss, uiCss]" :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-isContainer="true"
       :data-pageid="pageid">
    <UIBase v-for="(item, index) in uiconfig.items" :key="index" :is-readonly="myIsReadonly" :is-lock="myIsLock" :uiconfig="item" :pageid="pageid"></UIBase>
  </div>
</template>

<script lang="ts">
import UIBase from '@/components/ui/UIBase.vue'
import Container from '@/components/ui/js/Container'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Vant_Container',
  components: { UIBase },
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const store = useStore()
    const container = new Container(props, context, store)
    const setup = container.setup()
    const uiCss = computed(() => {
      const css = container.getUICss()
      const _ = Object.values(css) || []
      if (setup.isRow.value) {
        _.push('van-row')
        if (props.uiconfig.meta?.custom?.justify) {
          const map = { 'flex-start': 'van-row--justify-start', center: 'van-row--justify-center', 'flex-end': 'van-row--justify-end', 'space-between': 'van-row--justify-space-between', 'space-around': 'van-row--justify-space-around' }
          _.push(map[props.uiconfig.meta?.custom?.justify])
        }
        if (props.uiconfig.meta?.custom?.align) {
          const map = { 'flex-start': 'van-row--align-start', center: 'van-row--align-center', 'flex-end': 'van-row--align-bottom', baseline: 'van-row--align-baseline', stretch: 'van-row--align-stretch' }
          _.push(map[props.uiconfig.meta?.custom?.align])
        }
        if (!props.uiconfig.meta?.custom?.wrap) {
          _.push('van-row--nowrap')
        }
      } else if (setup.isCol.value) {
        _.push('van-col')
      }
      return _.join(' ')
    })
    const uiStyle = computed(() => {
      const style = container.getUIStyle()
      const { index, parentConfig } = store.getters.getUIItemInPage(props.uiconfig.meta.id, props.pageid)
      const gutter = parentConfig.meta?.custom?.gutter

      if (setup.isCol.value && gutter) {
        const total = parentConfig.items.length
        if (total > 1) {
          if (index === 0) { // 第一个
            style['padding-right'] = `${gutter / 2}px`
          } else if (index === total - 1) { // 最后一个
            style['padding-left'] = `${gutter / 2}px`
          } else { // 中间的
            style['padding-left'] = `${gutter / 2}px`
            style['padding-right'] = `${gutter / 2}px`
          }
        }
      }
      return container.appendImportant(style)
    })
    return {
      ...setup,
      uiCss,
      uiStyle
    }
  }
}

</script>
