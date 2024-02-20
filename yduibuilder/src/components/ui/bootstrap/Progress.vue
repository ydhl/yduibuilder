<template>
  <div :class="['progress', dragableCss, uiCss]" :draggable='!inlineEditItemId'
       :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid">
    <div :class="['progress-bar', barCss,
      {'progress-bar-striped':uiconfig.meta.custom?.striped,'progress-bar-animated':uiconfig.meta.custom?.animatedStrip}]" role="progressbar"
         :style="barStyle"
         :aria-valuenow="defaultValue"
         :aria-valuemin="uiconfig.meta.custom?.min"
         :aria-valuemax="uiconfig.meta.custom?.max">
      <template v-if="uiconfig.meta.custom?.label">{{defaultValue}}%</template>
    </div>
  </div>
</template>

<script lang="ts">
import Progress from '@/components/ui/js/Progress'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Bootstrap_Progress',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const progress = new Progress(props, context)
    const setup = progress.setup()
    const store = useStore()
    const uiCss = computed(() => {
      const cssMap = progress.getUICss()
      delete cssMap.foregroundTheme
      return Object.values(cssMap).join(' ')
    })
    const uiStyle = computed(() => {
      const style = progress.getUIStyle()
      delete style.color
      return progress.appendImportant(style)
    })
    const barCss = computed(() => {
      return store.getters.translate('backgroundTheme', props.uiconfig.meta?.css?.foregroundTheme)
    })
    const barStyle = computed(() => {
      const style: any = ['width:' + setup.defaultValue.value + '%']
      const uiStyle = progress.getUIStyle()

      if (uiStyle?.color) {
        style.push('background-color:' + uiStyle.color + ' !important')
      }
      return style.join(';')
    })
    return {
      ...setup,
      uiCss,
      uiStyle,
      barCss,
      barStyle
    }
  }
}

</script>
