<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, bodyCss, uiCss,{'overflow-hidden':true, 'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <IconWrapper :uiconfig="uiconfig">
      <input :type="uiconfig.meta?.custom?.inputType || 'Text'" :id="uiconfig.meta.id+uiconfig.type" class="weui-input"
           :name="uiconfig.meta?.form?.inputName" :placeholder="uiconfig.meta?.form?.placeholder"
           :disabled="uiconfig.meta?.form?.state==='disabled'"
           :value="uiconfig.meta.value">
    </IconWrapper>
    <div v-if="uiconfig.meta?.custom?.wordCountVisible" class="ml-3">0{{uiconfig.meta?.custom?.maxLength ? '/' + uiconfig.meta?.custom?.maxLength : ''}}</div>
    <div v-if="uiconfig.meta?.custom?.clearButtonVisible">
      <i class="weui-icon-clear"></i>
    </div>
  </div>
</template>

<script lang="ts">
import Input from '@/components/ui/js/Input'
import IconWrapper from '@/components/ui/weui/IconWrapper.vue'
import { useStore } from 'vuex'
import { computed } from 'vue'

export default {
  name: 'Weui_Input',
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
      const arr: any = ['d-flex justify-content-between align-items-center bg-transparent']
      if (props.uiconfig.meta?.custom?.borderless) {
        arr.push('border-0')
      }

      if (props.uiconfig.meta?.form?.state === 'disabled') {
        arr.push('disabled')
      }
      if (props.uiconfig.meta?.form?.state === 'readonly') {
        arr.push('readonly')
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
