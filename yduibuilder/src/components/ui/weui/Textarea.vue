<template>
  <FormGroup :uiconfig="uiconfig" :pageid="pageid"
                      :draggable='draggable' :dragableCss="dragableCss">
    <textarea class="weui-textarea" :style="uiconfig.meta.custom?.autoRow ? 'resize: none' : ''" :id="uiconfig.meta.id+uiconfig.type" :name="uiconfig.meta?.form?.inputName"
              :disabled="uiconfig.meta?.form?.state==='disabled'"
              :placeholder="uiconfig.meta?.form?.placeholder" :rows="uiconfig.meta.custom?.row" v-model="defaultValue"></textarea>
    <div class="weui-textarea-counter" v-if="uiconfig.meta?.custom?.wordCountVisible">0{{uiconfig.meta?.custom?.maxLength ? '/' + uiconfig.meta?.custom?.maxLength : ''}}</div>
    <button v-if="uiconfig.meta?.custom?.clearButtonVisible" class="weui-btn_reset weui-btn_icon">
      <i class="weui-icon-clear"></i>
    </button>
  </FormGroup>
</template>

<script lang="ts">
import Textarea from '@/components/ui/js/Textarea'
import FormGroup from '@/components/ui/weui/FormGroup.vue'
import { useStore } from 'vuex'

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
  components: { FormGroup },
  setup (props: any, context: any) {
    const textarea = new Textarea(props, context, useStore())
    return {
      ...textarea.setup()
    }
  }
}

</script>
