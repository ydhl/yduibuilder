<template>
  <LayuiFormGroup :uiconfig="uiconfig" :pageid="pageid"
                      :draggable='draggable' :dragableCss="dragableCss">
    <template v-for="(item, index) in values" :key="index" >
      <div :class="{'layui-d-inline-block': uiconfig.meta.custom?.inline}">
        <div :class="{'layui-unselect layui-form-radio': true, 'layui-form-radioed': item.checked, 'layui-radio-disabled layui-disabled':uiconfig.meta?.form?.state==='disabled'}">
          <i class="layui-anim layui-icon" v-if="!item.checked"></i>
          <i class="layui-anim layui-icon layui-anim-scaleSpring" v-if="item.checked"></i>
          <span>{{item.text}}</span>
        </div>
      </div>
    </template>
  </LayuiFormGroup>
</template>

<script lang="ts">
import Radio from '@/components/ui/js/Radio'
import LayuiFormGroup from '@/components/ui/layui/FormGroup.vue'
import { useStore } from 'vuex'

export default {
  name: 'Layui_Radio',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  components: { LayuiFormGroup },
  setup (props: any, context: any) {
    const radio = new Radio(props, context, useStore())
    return {
      ...radio.setup()
    }
  }
}

</script>
