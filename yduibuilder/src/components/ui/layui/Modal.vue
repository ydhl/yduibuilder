<template>
  <div :class="['layui-card',dragableCss, uiCss]"
       :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-isContainer="true"
       :data-pageid="pageid">
      <div :class="['layui-card-header layui-d-flex layui-align-items-center layui-justify-content-between subui',{'dragenter-subcontainer': isDragIn && dragoverInParent=='head'}]" v-if="!uiconfig.meta.custom?.headless" data-placeInParent="head">
        <template v-if="!myItems.head.length">
          {{t('common.dragtohere')}}
        </template>
        <UIBase v-for="(item, index) in myItems.head" :key="index" :is-readonly="myIsReadonly" :is-lock="myIsLock" :uiconfig="item" :pageid="pageid"></UIBase>
        <button type='button' class='layui-btn layui-btn-primary layui-btn-xs layui-border-0 layui-reset d-none'><i class='layui-icon layui-icon-close'></i></button>
      </div>
      <div class="layui-card-body">
        <template v-if="!hasMainItems">
          {{t('common.dragtohere')}}
        </template>
        <UIBase v-for="(item, index) in mainItems" :key="index" :is-readonly="myIsReadonly" :is-lock="myIsLock" :uiconfig="item" :pageid="pageid"></UIBase>
      </div>
      <div :class="['layui-card-footer subui',{'dragenter-subcontainer': isDragIn && dragoverInParent=='foot'}]" v-if="!uiconfig.meta.custom?.footless" data-placeInParent="foot">
        <template v-if="!myItems.foot.length">
          {{t('common.dragtohere')}}
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
  name: 'Layui_Modal',
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
    return {
      mainItems,
      hasMainItems,
      ...setup
    }
  }
}
</script>
