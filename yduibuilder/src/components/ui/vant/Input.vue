<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, bodyCss, uiCss,{'overflow-hidden':true, 'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <IconWrapper :uiconfig="uiconfig">
      <input :type="uiconfig.meta?.custom?.inputType || 'Text'" :id="uiconfig.meta.id+uiconfig.type" class="van-field__control"
           :name="uiconfig.meta?.form?.inputName" :placeholder="uiconfig.meta?.form?.placeholder"
           :disabled="uiconfig.meta?.form?.state==='disabled'"
           :value="uiconfig.meta.value">
    </IconWrapper>
    <div v-if="uiconfig.meta?.custom?.wordCountVisible" class="van-ml-3">0{{uiconfig.meta?.custom?.maxLength ? '/' + uiconfig.meta?.custom?.maxLength : ''}}</div>
    <div v-if="uiconfig.meta?.custom?.clearButtonVisible" class="cursor ml-3">
      <i class="van-badge__wrapper van-icon van-icon-cross"></i>
    </div>
  </div>
</template>

<script lang="ts">
import Input from '@/components/ui/js/Input'
import IconWrapper from '@/components/ui/vant/IconWrapper.vue'
import { useStore } from 'vuex'
import { computed } from 'vue'

export default {
  name: 'Vant_Input',
  components: { IconWrapper },
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const input = new Input(props, context, useStore())
    const bodyCss = computed(() => {
      const arr: any = ['van-d-flex van-justify-content-between van-align-items-center van-bg-transparent']
      if (props.uiconfig.meta?.custom?.borderless) {
        arr.push('van-border-0')
      }

      if (props.uiconfig.meta?.form?.state === 'disabled') {
        arr.push('van-disabled')
      }
      if (props.uiconfig.meta?.form?.state === 'readonly') {
        arr.push('van-readonly')
      }
      return arr
    })
    return {
      ...input.setup(),
      bodyCss
    }
  }
}

</script>
