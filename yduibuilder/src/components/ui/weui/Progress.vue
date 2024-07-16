<template>
  <div :class="['weui-progress', dragableCss, uiCss]" :draggable='draggable'
       :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid">
    <div :class="['weui-progress__bar', bgCss]" :style="bgStyle">
      <div :class="['weui-progress__inner-bar', frontCss,
        {'weui-progress-bar-striped':uiconfig.meta.custom?.striped,'weui-progress-bar-animated':uiconfig.meta.custom?.animatedStrip}]"
           :style="frontStyle">
        <template v-if="uiconfig.meta.custom?.label">{{defaultValue}}%</template>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import Progress from '@/components/ui/js/Progress'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Weui_Progress',
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
    const progress = new Progress(props, context, store)
    const setup = progress.setup()
    const uiCss = computed(() => {
      const cssMap = progress.getUICss()
      delete cssMap.foregroundTheme
      delete cssMap.backgroundTheme
      return Object.values(cssMap).join(' ')
    })
    const uiStyle = computed(() => {
      const style = progress.getUIStyle()
      delete style?.color
      delete style?.height
      delete style?.['background-color']
      return progress.appendImportant(style)
    })
    const bgCss = computed(() => {
      const css: any = []
      if (props.uiconfig.meta?.css?.backgroundTheme) {
        css.push(store.getters.translate('backgroundTheme', props.uiconfig.meta?.css?.backgroundTheme))
      }
      return css.join(' ')
    })
    const bgStyle = computed(() => {
      const style: any = []
      const uiStyle = progress.getUIStyle()

      if (uiStyle?.height) {
        style.push('height:' + uiStyle.height + ' !important;')
      }
      if (uiStyle?.['background-color']) {
        style.push('background-color:' + uiStyle['background-color'] + ' !important;')
      }
      return style.join(';')
    })
    const frontCss = computed(() => {
      const css: any = []
      if (props.uiconfig.meta?.css?.foregroundTheme) {
        css.push(store.getters.translate('backgroundTheme', props.uiconfig.meta?.css?.foregroundTheme))
      }
      if (props.uiconfig.meta?.custom?.striped) {
        css.push('weui-progress-bar-striped')
      }
      if (props.uiconfig.meta?.custom?.animatedStrip) {
        css.push('weui-progress-bar-animated')
      }
      return css.join(' ')
    })
    const frontStyle = computed(() => {
      const style: any = []
      const uiStyle = progress.getUIStyle()
      style.push('width:' + setup.defaultValue.value + '% !important;')

      if (uiStyle?.color) {
        style.push('background-color:' + uiStyle?.color + ' !important;')
      }
      return style.join(';')
    })
    return {
      ...setup,
      uiCss,
      uiStyle,
      bgCss,
      bgStyle,
      frontCss,
      frontStyle
    }
  }
}

</script>
