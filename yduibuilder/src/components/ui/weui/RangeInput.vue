<template>
  <FormGroup :uiconfig="uiconfig" :pageid="pageid"
                      :draggable='draggable' :dragableCss="dragableCss">
    <div class="weui-slider-box w-100">
      <div class="weui-slider pl-0">
        <div :style="bgStyle" :class="bgTheme">
          <div :style="trackStyle" :class="trackTheme"></div>
          <div :style="handleStyle" :class="handleTheme"></div>
        </div>
      </div>
      <div class="weui-slider-box__value">{{ uiconfig.meta.value||50 }}</div>
    </div>
  </FormGroup>
</template>

<script lang="ts">
import RangeInput from '@/components/ui/js/RangeInput'
import FormGroup from '@/components/ui/weui/FormGroup.vue'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Weui_RangeInput',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  components: { FormGroup },
  setup (props: any, context: any) {
    const store = useStore()
    const rangeinput = new RangeInput(props, context, store)
    /**
     * 滑块主题样式
     */
    const handleStyle = computed(() => {
      const style: any = []
      style.push(`left: ${props.uiconfig.meta.value || 50}%; !important;`)
      if (props.uiconfig.meta?.custom?.color) {
        style.push('background-color:' + props.uiconfig.meta?.custom?.color + ' !important;')
      }
      return style.join(';')
    })
    const handleTheme = computed(() => {
      const css: any = ['weui-slider__handler weui-wa-hotarea']
      if (props.uiconfig.meta?.custom?.theme && props.uiconfig.meta?.custom?.theme !== 'default') {
        css.push(store.getters.translate('backgroundTheme', props.uiconfig.meta?.custom?.theme))
      }
      return css.join(' ')
    })
    /**
     * 前景色样式，已滑动距离
     */
    const trackTheme = computed(() => {
      const css: any = ['weui-slider__track']
      if (props.uiconfig.meta?.custom?.theme && props.uiconfig.meta?.custom?.theme !== 'default') {
        css.push(store.getters.translate('backgroundTheme', props.uiconfig.meta?.custom?.theme))
      }
      return css.join(' ')
    })
    const trackStyle = computed(() => {
      const style: any = [`width: ${props.uiconfig.meta.value || 50}% !important;`]
      if (props.uiconfig.meta?.custom?.color) {
        style.push('background-color:' + props.uiconfig.meta?.custom?.color + ' !important;')
      }
      return style.join(';')
    })
    /**
     * 背景色样式，底色
     */
    const bgTheme = computed(() => {
      const css: any = ['weui-slider__inner']
      return css.join(' ')
    })
    const bgStyle = computed(() => {
      const backgroundColor = props.uiconfig.meta?.custom?.backgroundColor
      if (backgroundColor) return 'background-color: ' + backgroundColor + ' !important;'
      return ''
    })

    return {
      ...rangeinput.setup(),
      bgTheme,
      bgStyle,
      trackTheme,
      trackStyle,
      handleStyle,
      handleTheme
    }
  }
}

</script>
