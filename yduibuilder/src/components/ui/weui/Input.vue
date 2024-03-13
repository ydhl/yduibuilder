<template>
  <FormGroup :uiconfig="uiconfig" :pageid="pageid"
                      :draggable='draggable' :dragableCss="dragableCss">
    <IconWrapper :uiconfig="uiconfig">
      <input :type="uiconfig.meta?.custom?.inputType || 'Text'" :id="uiconfig.meta.id+uiconfig.type" class="weui-input"
           :name="uiconfig.meta?.form?.inputName" :placeholder="uiconfig.meta?.form?.placeholder"
           :disabled="uiconfig.meta?.form?.state==='disabled'"
           :value="uiconfig.meta.value">
    </IconWrapper>
    <div v-if="uiconfig.meta?.custom?.wordCountVisible" class="ml-3">0{{uiconfig.meta?.custom?.maxLength ? '/' + uiconfig.meta?.custom?.maxLength : ''}}</div>
    <button v-if="uiconfig.meta?.custom?.clearButtonVisible" class="weui-btn_reset weui-btn_icon">
      <i class="weui-icon-clear"></i>
    </button>
  </FormGroup>
</template>

<script lang="ts">
import FormGroup from '@/components/ui/weui/FormGroup.vue'
import Input from '@/components/ui/js/Input'
import IconWrapper from '@/components/ui/weui/IconWrapper.vue'
import { useStore } from 'vuex'

export default {
  name: 'Weui_Input',
  components: { IconWrapper, FormGroup },
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
    return {
      ...input.setup()
    }
  }
}

</script>
