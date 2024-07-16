<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, bodyCss, uiCss,{'overflow-hidden weui-cell flex-column':true, 'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <div class="weui-cell__hd w-100"><label class="weui-label">{{ uiconfig.meta.title }}</label></div>
    <div class="weui-cell__bd w-100">
      <textarea class="weui-textarea" :style="uiconfig.meta.custom?.autoRow ? 'resize: none' : ''" :id="uiconfig.meta.id+uiconfig.type" :name="uiconfig.meta?.form?.inputName"
                :disabled="uiconfig.meta?.form?.state==='disabled'" readonly
                :placeholder="uiconfig.meta?.form?.placeholder" :rows="uiconfig.meta.custom?.row" v-model="defaultValue"></textarea>
      <div class="weui-flex align-items-center justify-content-end">
        <div class="weui-textarea-counter" v-if="uiconfig.meta?.custom?.wordCountVisible">0{{uiconfig.meta?.custom?.maxLength ? '/' + uiconfig.meta?.custom?.maxLength : ''}}</div>
        <button type="button" class="weui-btn_reset weui-btn_icon ml-2" v-if="uiconfig.meta?.custom?.clearButtonVisible">
          <i class="weui-icon-clear"></i>
        </button>
      </div>
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
