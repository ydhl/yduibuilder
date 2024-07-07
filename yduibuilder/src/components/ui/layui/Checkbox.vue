<template>
  <BootstrapFormGroup
    :uiconfig="uiconfig" :pageid="pageid"
  :draggable='draggable' :dragableCss="dragableCss">
    <template v-for="(item, index) in values" :key="index" >
      <div :class="{'layui-d-inline-block': uiconfig.meta.custom?.inline}">
        <div :class="{'layui-unselect layui-form-checkbox':true, 'layui-form-checked':item.checked, 'layui-checkbox-disabled layui-disabled':uiconfig.meta?.form?.state==='disabled'}" lay-skin="primary">
          <span>{{item.name}}</span>
          <i class="layui-icon layui-icon-ok"></i>
        </div>
      </div>
    </template>
  </BootstrapFormGroup>
</template>

<script lang="ts">
import BootstrapFormGroup from '@/components/ui/layui/FormGroup.vue'
import Checkbox from '@/components/ui/js/Checkbox'
import { useStore } from 'vuex'

export default {
  name: 'Layui_Checkbox',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  components: { BootstrapFormGroup },
  setup (props: any, context: any) {
    const checkbox = new Checkbox(props, context, useStore())
    return {
      ...checkbox.setup()
    }
  }
}

</script>
