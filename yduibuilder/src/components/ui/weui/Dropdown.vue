<template>
  <div :class="['weui-cell weui-cell_active weui-cell_access',dragableCss,uiCss]"
       :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid" @dblclick="inlineEditItemId=uiconfig.meta.id" @keyup.enter="inlineEditItemId=''">
    <div class="weui-cell__bd">
      <p>{{uiconfig.meta.title || 'Dropdown'}}</p>
    </div>
    <div class="weui-cell__ft"></div>
  </div>
</template>

<script lang="ts">
import Dropdown from '@/components/ui/js/Dropdown'
import { useStore } from 'vuex'

export default {
  name: 'Weui_Dropdown',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const store = useStore()
    const dropdown = new Dropdown(props, context, store)
    const setup = dropdown.setup()
    return {
      ...setup
    }
  }
}

</script>
