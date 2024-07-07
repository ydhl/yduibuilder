<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, bodyCss, uiCss,{'overflow-hidden':true, 'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <div class="weui-uploader__bd">
      <div class="weui-uploader__input-box">
        <input :id="uiconfig.meta.id+uiconfig.type" :disabled="uiconfig.meta?.form?.state==='disabled'"
               :name="uiconfig.meta?.form?.inputName"
               class="weui-uploader__input" type="file">
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import File from '@/components/ui/js/File'
import { useStore } from 'vuex'
import { computed } from 'vue'

export default {
  name: 'Weui_File',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const file = new File(props, context, useStore())
    const bodyCss = computed(() => {
      const arr: any = ['d-flex justify-content-between align-items-center bg-transparent']
      if (props.uiconfig.meta?.form?.state === 'disabled') {
        arr.push('disabled')
      }
      if (props.uiconfig.meta?.form?.state === 'readonly') {
        arr.push('readonly')
      }
      return arr
    })
    return {
      ...file.setup(),
      bodyCss
    }
  }
}

</script>
