<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, bodyCss, uiCss,{'overflow-hidden':true, 'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <div class="van-radio-group van-radio-group--horizontal" v-for="(item, index) in values" :key="index">
      <div class="van-radio van-radio--horizontal">
        <div :class="{'van-radio__icon van-radio__icon--round': true, 'van-radio__icon--checked':item.checked}">
          <i class="van-badge__wrapper van-icon van-icon-success" :style="item.checked ? disabledStyle : ''"></i>
        </div>
        <span class="van-radio__label">{{item.text}}</span>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import Radio from '@/components/ui/js/Radio'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Vant_Radio',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const radio = new Radio(props, context, useStore())
    const disabledStyle = computed(() => {
      if (props.uiconfig.meta?.form?.state === 'readonly' || props.uiconfig.meta?.form?.state === 'disabled') {
        return 'border-color:var(--van-gray-6);background-color:var(--van-gray-6)'
      }
      return ''
    })

    const bodyCss = computed(() => {
      const arr: any = []

      arr.push('van-h-auto')
      return arr
    })
    return {
      ...radio.setup(),
      bodyCss,
      disabledStyle
    }
  }
}

</script>
