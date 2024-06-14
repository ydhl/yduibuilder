<template>
  <div :draggable='draggable' :style="myStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, myCss,{'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <div class="van-field__control van-field__control--custom">
      <div class="van-slider">
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
      if (props.uiconfig.meta?.style?.color) {
        style.push('background-color:' + props.uiconfig.meta?.style?.color + ' !important;')
      }
      return style.join(';')
    })
    const handleTheme = computed(() => {
      const css: any = ['van-slider__button van-text-center']
      if (props.uiconfig.meta?.css?.foregroundTheme && props.uiconfig.meta?.css?.foregroundTheme !== 'default') {
        css.push(store.getters.translate('backgroundTheme', props.uiconfig.meta?.css?.foregroundTheme))
        css.push(store.getters.translate('foregroundTheme', 'light'))
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
      if (props.uiconfig.meta?.css?.backgroundTheme && props.uiconfig.meta?.css?.backgroundTheme !== 'default') {
        css.push(store.getters.translate('backgroundTheme', props.uiconfig.meta?.css?.backgroundTheme))
      }
      return css.join(' ')
    })
    const bgStyle = computed(() => {
      const style: any = [`width: ${props.uiconfig.meta.value || 50}% !important;`]
      if (props.uiconfig.meta?.style?.['background-color']) {
        style.push('background-color:' + props.uiconfig.meta?.style?.['background-color'] + ' !important;')
      }
      return style.join(';')
    })

    const myCss = computed(() => {
      const css = rangeinput.getUICss()
      delete css.backgroundTheme
      const arr: any = Object.values(css)
      return arr
    })
    const myStyle = computed(() => {
      const myStyle = rangeinput.getUIStyle()
      delete myStyle?.['background-color']
      delete myStyle?.['background-image']
      return rangeinput.appendImportant(myStyle)
    })
    return {
      ...rangeinput.setup(),
      myCss,
      myStyle,
      bgTheme,
      bgStyle,
      trackTheme,
      handleStyle,
      handleTheme
    }
  }
}

</script>
