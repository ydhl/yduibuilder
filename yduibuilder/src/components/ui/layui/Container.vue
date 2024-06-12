<template>
  <div :draggable='draggable' :class="[dragableCss, uiCss]" :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-isContainer="true"
       :data-pageid="pageid">
    <UIBase v-for="(item, index) in uiconfig.items" :key="index" :is-readonly="myIsReadonly" :is-lock="myIsLock" :uiconfig="item" :pageid="pageid"></UIBase>
  </div>
</template>

<script lang="ts">
import UIBase from '@/components/ui/UIBase.vue'
import Container from '@/components/ui/js/Container'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Layui_Container',
  components: { UIBase },
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const container = new Container(props, context, useStore())
    const uiCss = computed(() => {
      const css = container.getUICss()
      if (props.uiconfig.items) {
        let hasForm: any = false
        for (const item of props.uiconfig.items) {
          if (item.meta?.form) {
            hasForm = true
            break
          }
        }
        if (hasForm) {
          css.form = 'layui-form'
        }
      }
      return Object.values(css).join(' ')
    })
    return {
      ...container.setup(),
      uiCss
    }
  }
}

</script>
