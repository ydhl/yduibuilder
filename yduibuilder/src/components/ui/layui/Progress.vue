<template>
  <div :class="['layui-progress', dragableCss, uiCss]" :draggable='draggable'
       :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid">
    <div :class="barCss"
         :style="barStyle"
         :lay-percent="defaultValue + '%'">
      <div v-if="uiconfig.meta.custom?.label">{{defaultValue}}%</div>
    </div>
  </div>
</template>

<script lang="ts">
import Progress from '@/components/ui/js/Progress'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Layui_Progress',
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
    const uiStyle = computed(() => {
      const uistyle = progress.getUIStyle()
      delete uistyle.color
      return progress.appendImportant(uistyle)
    })
    const barCss = computed(() => {
      const cssMap = progress.getUICss()
      delete cssMap.foregroundTheme
      const css = ['layui-progress-bar layui-d-flex layui-justify-content-end layui-align-items-center']
      if (props.uiconfig.meta.custom?.striped) {
        css.push('layui-progress-bar-striped')
      }
      if (props.uiconfig.meta.custom?.animatedStrip) {
        css.push('layui-progress-bar-animated')
      }
      return css.join(' ')
    })
    const barStyle = computed(() => {
      const uistyle = progress.getUIStyle()
      const cssMap = progress.getUICss()
      const style:any = []
      style.push('width:' + setup.defaultValue.value + '%')
      let color
      if (uistyle.color) {
        color = uistyle.color
      } else if (cssMap.foregroundTheme) {
        color = store.getters.translate('themeColor', props.uiconfig.meta?.css?.foregroundTheme)
      }
      if (color) {
        style.push('background-color:' + color + ' !important')
      }
      if (uistyle.height) {
        style.push('height:' + uistyle.height)
      }
      return style.length > 0 ? style.join(';') : ''
    })
    return {
      ...setup,
      uiCss,
      barStyle,
      barCss,
      uiStyle
    }
  }
}

</script>
