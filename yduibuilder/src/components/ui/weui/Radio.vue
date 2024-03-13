<template>
  <FormGroup :uiconfig="uiconfig" :pageid="pageid"
                      :draggable='draggable' :dragableCss="dragableCss">
    <div class="w-100 weui-cells_radio">
    <template v-for="(item, index) in values" :key="index" >
      <div class="weui-cell weui-cell_active weui-check__label" :for="uiconfig.meta.id+item.value">
        <div class="weui-cell__hd pr-3">
          <input type="radio" :checked="item.checked" class="weui-check"
                 :id="uiconfig.meta.id+item.value"
                 :disabled="uiconfig.meta?.form?.state==='disabled'"
                 :value="item.value" :name="uiconfig.meta?.form?.inputName">
          <span class="weui-icon-checked"></span>
        </div>
        <div class="weui-cell__bd">
          <p>{{item.text}}</p>
        </div>
      </div>
    </template>
    </div>
  </FormGroup>
</template>

<script lang="ts">
import Radio from '@/components/ui/js/Radio'
import FormGroup from '@/components/ui/weui/FormGroup.vue'
import { useStore } from 'vuex'

export default {
  name: 'Weui_Radio',
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
    const radio = new Radio(props, context, useStore())

    return {
      ...radio.setup()
    }
  }
}

</script>
