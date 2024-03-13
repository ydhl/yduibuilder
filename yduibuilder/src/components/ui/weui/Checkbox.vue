<template>
  <FormGroup
    :uiconfig="uiconfig" :pageid="pageid"
  :draggable='draggable' :dragableCss="dragableCss">
    <div class="w-100 weui-cells_checkbox pointer-event-none">
    <template v-for="(item, index) in values" :key="index" >
      <label class="weui-cell weui-cell_active weui-check__label">
        <div class="weui-cell__hd">
          <input type="checkbox" :checked="item.checked" class="weui-check"
                 :id="uiconfig.meta.id+item.value"
                 :disabled="uiconfig.meta?.form?.state==='disabled'"
                 :value="item.value" :name="uiconfig.meta?.form?.inputName">
          <i class="weui-icon-checked"></i>
        </div>
        <div class="weui-cell__bd">
          <p>{{item.text}}</p>
        </div>
      </label>
    </template>
    </div>
  </FormGroup>
</template>

<script lang="ts">
import FormGroup from '@/components/ui/weui/FormGroup.vue'
import Checkbox from '@/components/ui/js/Checkbox'
import { useStore } from 'vuex'

export default {
  name: 'Weui_Checkbox',
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
    const checkbox = new Checkbox(props, context, useStore())
    return {
      ...checkbox.setup()
    }
  }
}

</script>
