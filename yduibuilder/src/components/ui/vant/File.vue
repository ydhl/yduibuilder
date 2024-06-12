<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid"
       :class="[dragableCss, bodyCss, uiCss,{'overflow-hidden':true, 'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <div class="van-uploader">
      <div class="van-uploader__wrapper">
        <div class="van-uploader__upload">
          <i class="van-badge__wrapper van-icon van-icon-photograph van-uploader__upload-icon"></i>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import File from '@/components/ui/js/File'
import { useStore } from 'vuex'
import { computed } from 'vue'

export default {
  name: 'Vant_File',
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
      const arr: any = ['van-d-flex van-align-items-center']

      if (props.uiconfig.meta?.form?.state === 'disabled') {
        arr.push('van-disabled')
      }
      if (props.uiconfig.meta?.form?.state === 'readonly') {
        arr.push('van-readonly')
      }
      return arr.join(' ')
    })
    return {
      ...file.setup(),
      bodyCss
    }
  }
}

</script>
