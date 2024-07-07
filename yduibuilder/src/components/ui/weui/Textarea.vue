<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, bodyCss, uiCss,{'overflow-hidden':true, 'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <textarea class="weui-textarea" :style="uiconfig.meta.custom?.autoRow ? 'resize: none' : ''" :id="uiconfig.meta.id+uiconfig.type" :name="uiconfig.meta?.form?.inputName"
              :disabled="uiconfig.meta?.form?.state==='disabled'"
              :placeholder="uiconfig.meta?.form?.placeholder" :rows="uiconfig.meta.custom?.row" v-model="defaultValue"></textarea>
    <div class="weui-textarea-counter" v-if="uiconfig.meta?.custom?.wordCountVisible">0{{uiconfig.meta?.custom?.maxLength ? '/' + uiconfig.meta?.custom?.maxLength : ''}}</div>
    <div v-if="uiconfig.meta?.custom?.clearButtonVisible">
      <i class="weui-icon-clear"></i>
    </div>
  </div>
</template>

<script lang="ts">
import Textarea from '@/components/ui/js/Textarea'
import { useStore } from 'vuex'
import { computed } from 'vue'

export default {
  name: 'Weui_Textarea',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const textarea = new Textarea(props, context, useStore())

    const bodyCss = computed(() => {
      const arr = ['d-flex justify-content-between align-items-end h-auto']

      if (props.uiconfig.meta?.form?.state === 'disabled') {
        arr.push('disabled')
      }
      if (props.uiconfig.meta?.form?.state === 'readonly') {
        arr.push('readonly')
      }
      return arr
    })
    return {
      ...textarea.setup(),
      bodyCss
    }
  }
}

</script>
