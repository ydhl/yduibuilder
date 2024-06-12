<template>
  <BootstrapFormGroup
    :uiconfig="uiconfig" :pageid="pageid"
    :draggable='draggable' :dragableCss="dragableCss">
    <div :class="bodyCss"
         :id="uiconfig.meta.id+uiconfig.type"
         :disabled="uiconfig.meta?.form?.state==='disabled'">
      <div class="layui-select-title">
        <input type="text" :value="value" :placeholder="uiconfig.meta?.form?.placeholder" :disabled="uiconfig.meta?.form?.state==='disabled'" readonly="readonly" class="layui-input layui-unselect">
        <i class="layui-edge"></i>
      </div>
    </div>
  </BootstrapFormGroup>
</template>

<script lang="ts">
import Select from '@/components/ui/js/Select'
import BootstrapFormGroup from '@/components/ui/layui/FormGroup.vue'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Layui_Select',
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
    const select = new Select(props, context, useStore())
    const bodyCss = computed(() => {
      const arr: any = ['layui-unselect layui-form-select']
      if (props.uiconfig.meta?.css?.formSizing && props.uiconfig.meta?.css?.formSizing !== 'normal') {
        arr.push('layui-form-' + props.uiconfig.meta.css.formSizing)
      }
      return arr
    })

    const value = computed(() => {
      if (!props.uiconfig.meta.values) return ''
      for (const item of props.uiconfig.meta.values) {
        if (item.checked) return item.text
      }
      return ''
    })
    return {
      ...select.setup(),
      bodyCss,
      value
    }
  }
}

</script>
