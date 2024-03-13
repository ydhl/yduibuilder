<template>
  <div :class="['weui-progress', dragableCss, uiCss]" :draggable='draggable'
       :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid">
    <div :class="['weui-progress-bar', themeCss,
      {'weui-progress-bar-striped':uiconfig.meta.custom?.striped,'weui-progress-bar-animated':uiconfig.meta.custom?.animatedStrip}]" role="progressbar"
         :style="themeStyle">
      <template v-if="uiconfig.meta.custom?.label">{{defaultValue}}%</template>
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
      return Object.values(cssMap).join(' ')
    })
    const themeCss = computed(() => {
      return store.getters.translate('backgroundTheme', props.uiconfig.meta?.css?.foregroundTheme)
    })
    const uiStyle = computed(() => {
      const style = progress.getUIStyle()
      delete style?.color
      return progress.appendImportant(style)
    })
    const themeStyle = computed(() => {
      const style: any = ['width:' + setup.defaultValue.value + '% !important;']
      const uiStyle = progress.getUIStyle()

      // color 作为前景div的background color
      if (uiStyle?.color) {
        style.push('background-color:' + uiStyle.color + ' !important;')
      }
      // console.log(style)
      return style.join(';')
    })
    return {
      ...setup,
      uiCss,
      uiStyle,
      themeCss,
      themeStyle
    }
  }
}

</script>
