<template>
  <div :class="[dragableCss, uiCss, 'weui-dialog']"
       :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-isContainer="true"
       :data-pageid="pageid">
    <div :class="['weui-dialog__hd subui',{'dragenter-subcontainer': isDragIn && dragoverInParent=='head'}]" v-if="!uiconfig.meta.custom?.headless" data-placeInParent="head">
      <template v-if="!myItems.head.length">
        {{t('style.modal.drapTip')}}
      </template>
      <UIBase v-for="(item, index) in myItems.head" :key="index" :is-readonly="myIsReadonly" :is-lock="myIsLock" :uiconfig="item" :pageid="pageid"></UIBase>
    </div>
    <div class="weui-dialog__bd">
      <template v-if="!hasMainItems">
        {{t('style.modal.drapTip')}}
      </template>
      <UIBase v-for="(item, index) in mainItems" :key="index" :is-readonly="myIsReadonly" :is-lock="myIsLock" :uiconfig="item" :pageid="pageid"></UIBase>
    </div>
    <div :class="['weui-dialog__ft subui',{'dragenter-subcontainer': isDragIn && dragoverInParent=='foot'}]" v-if="!uiconfig.meta.custom?.footless" data-placeInParent="foot">
      <template v-if="!myItems.foot.length">
        {{t('style.modal.drapTip')}}
      </template>
      <UIBase v-for="(item, index) in myItems.foot" :key="index" :is-readonly="myIsReadonly" :is-lock="myIsLock" :uiconfig="item" :pageid="pageid"></UIBase>
    </div>
  </div>
</template>

<script lang="ts">
import UIBase from '@/components/ui/UIBase.vue'
import Modal from '../js/Modal'
import { computed, ref } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Weui_Model',
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
    const modal = new Modal(props, context, useStore())
    const setup = modal.setup()
    const mainItems = ref<Array<any>>([])
    const hasMainItems = computed(() => setup.myItems.value.main.length > 0)
    for (const item of setup.myItems.value.main) {
      mainItems.value.push(item)
    }
    const uiStyle = computed(() => {
      const style = modal.getUIStyle()
      return modal.appendImportant(style)
    })
    return {
      mainItems,
      hasMainItems,
      ...setup,
      uiStyle
    }
  }
}
</script>
