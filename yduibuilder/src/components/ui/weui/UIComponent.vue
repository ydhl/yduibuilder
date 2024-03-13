<template>
  <div :draggable='draggable' :class="[dragableCss, uiCss]" :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-isContainer="false"
       :data-pageid="pageid">
    <UIBase v-for="(item, index) in uiconfig.items" :key="index" :is-lock="true" :is-readonly="myIsReadonly" :uiconfig="item" :pageid="pageid"></UIBase>
  </div>
</template>

<script lang="ts">
import UIBase from '@/components/ui/UIBase.vue'
import UIComponent from '@/components/ui/js/UIComponent'
import { useStore } from 'vuex'

export default {
  name: 'Weui_UIComponent',
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
    const component = new UIComponent(props, context, useStore())
    return {
      ...component.setup()
    }
  }
}

</script>
