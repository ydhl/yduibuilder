<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, uiCss,{'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <div class="van-field__control van-field__control--custom">
      <div class="van-slider" :style="sliderStyle">
        <!--背景条-->
        <div :style="bgStyle" :class="bgTheme">
          <!--滑块按钮-->
          <div :class="trackTheme">
            <div :style="handleStyle" :class="handleTheme">{{ uiconfig.meta.value||50 }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import RangeInput from '@/components/ui/js/RangeInput'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Vant_RangeInput',
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
      const css: any = ['van-slider__button van-text-center']
      if (props.uiconfig.meta?.custom?.theme && props.uiconfig.meta?.custom?.theme !== 'default') {
        css.push(store.getters.translate('backgroundTheme', props.uiconfig.meta?.custom?.theme))
      }

      if (props.uiconfig.meta?.form?.state === 'disabled' || props.uiconfig.meta?.form?.state === 'readonly') {
        css.push('van-disabled')
      }
      return css.join(' ')
    })
    /**
     * 前景色样式，已滑动距离
     */
    const trackTheme = computed(() => {
      const css: any = ['van-slider__button-wrapper van-slider__button-wrapper--right']
      return css.join(' ')
    })

    /**
     * 背景色样式，底色
     */
    const bgTheme = computed(() => {
      const css: any = ['van-slider__bar']

      return css.join(' ')
    })
    const bgStyle = computed(() => {
      const style: any = [`width: ${props.uiconfig.meta.value || 50}% !important;`]
      if (props.uiconfig.meta?.custom?.color) {
        style.push('background-color:' + props.uiconfig.meta?.custom?.color + ' !important;')
      }
      return style.join(';')
    })
    const sliderStyle = computed(() => {
      const style: any = []
      if (props.uiconfig.meta?.custom?.backgroundColor) {
        style.push('background-color:' + props.uiconfig.meta?.custom?.backgroundColor + ' !important;')
      }
      return style.join(';')
    })

    return {
      ...rangeinput.setup(),
      bgTheme,
      bgStyle,
      trackTheme,
      handleStyle,
      sliderStyle,
      handleTheme
    }
  }
}

</script>
