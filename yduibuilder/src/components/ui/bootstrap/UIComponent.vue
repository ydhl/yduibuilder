<template>
  <div :draggable='draggable' :class="[dragableCss, uiCss]" :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-isContainer="false"
       :data-pageid="pageid">
    <template v-for="(item, index) in uiconfig.items" :key="index">
      <div v-if="item.subPageDeleted" class="p-5 text-danger">{{t('common.uicomponentDeleted', [item.meta.title])}}</div>
      <UIBase v-else :is-lock="myIsLock" :is-readonly="true" :uiconfig="item" :pageid="pageid"></UIBase>
    </template>
  </div>
</template>

<script lang="ts">
import UIBase from '@/components/ui/UIBase.vue'
import UIComponent from '@/components/ui/js/UIComponent'
import { useStore } from 'vuex'

export default {
  name: 'Bootstrap_UIComponent',
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
