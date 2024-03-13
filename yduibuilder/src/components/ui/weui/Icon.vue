<template>
  <template v-if="iconClass">
    <i :draggable='draggable'
          :class="[ dragableCss, uiCss, iconClass]" :style="uiStyle" :id="myId" :data-type="uiconfig.type"
          :data-pageid="pageid"></i>
  </template>
  <template v-else>
    <div :draggable='draggable'
       :class="[ dragableCss, uiCss]" :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid">{{t('style.icon.notChooseIconTip')}}</div>
  </template>
</template>

<script lang="ts">
import Icon from '@/components/ui/js/Icon'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Weui_Icon',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const icon = new Icon(props, context, useStore())
    const iconClass = computed(() => props.uiconfig.meta?.custom?.icon)
    return {
      ...icon.setup(),
      iconClass
    }
  }
}

</script>
