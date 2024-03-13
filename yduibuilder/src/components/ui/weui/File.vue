<template>
  <FormGroup :uiconfig="uiconfig" :pageid="pageid"
                      :draggable='draggable' :dragableCss="dragableCss">
    <div class="weui-uploader__bd">
      <div class="weui-uploader__input-box">
        <input :id="uiconfig.meta.id+uiconfig.type" :disabled="uiconfig.meta?.form?.state==='disabled'"
               :name="uiconfig.meta?.form?.inputName"
               class="weui-uploader__input" type="file">
      </div>
    </div>
  </FormGroup>
</template>

<script lang="ts">
import FormGroup from '@/components/ui/weui/FormGroup.vue'
import File from '@/components/ui/js/File'
import { useStore } from 'vuex'

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
  components: { FormGroup },
  setup (props: any, context: any) {
    const file = new File(props, context, useStore())
    return {
      ...file.setup()
    }
  }
}

</script>
