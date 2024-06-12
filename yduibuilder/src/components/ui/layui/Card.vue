<template>
  <div :class="[dragableCss, uiCss, 'layui-card']"
       :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-isContainer="true"
       :data-pageid="pageid">
    <div :class="['subui', cardHeadCss, {'dragenter-subcontainer': isDragIn && dragoverInParent=='head'}]" v-if="!uiconfig.meta.custom?.headless" data-placeInParent="head">
      <template v-if="!myItems.head.length">
        {{t('style.card.drapTip')}}
      </template>
      <UIBase v-for="(item, index) in myItems.head" :key="index" :is-readonly="myIsReadonly" :is-lock="myIsLock" :uiconfig="item" :pageid="pageid"></UIBase>
    </div>
    <div class="layui-card-body">
      <template v-if="!myItems.main.length">
        {{t('style.card.drapTip')}}
      </template>
      <UIBase v-for="(item, index) in myItems.main" :key="index" :is-readonly="myIsReadonly" :is-lock="myIsLock" :uiconfig="item" :pageid="pageid"></UIBase>
    </div>

    <div :class="['layui-card-footer subui',{'dragenter-subcontainer': isDragIn && dragoverInParent=='foot'}]" v-if="!uiconfig.meta.custom?.footless" data-placeInParent="foot">
      <template v-if="!myItems.foot.length">
        {{t('style.card.drapTip')}}
      </template>
      <UIBase v-for="(item, index) in myItems.foot" :key="index" :is-readonly="myIsReadonly" :is-lock="myIsLock" :uiconfig="item" :pageid="pageid"></UIBase>
    </div>
  </div>
</template>

<script lang="ts">
import UIBase from '@/components/ui/UIBase.vue'
import Card from '../js/Card'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Layui_Card',
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
    const card = new Card(props, context, useStore())
    const cardHeadCss = computed(() => {
      const arr: any = []
      let hasTab: boolean = false
      if (props.uiconfig.items) {
        for (const item of props.uiconfig.items) {
          if (item.type.toLowerCase() === 'nav' && item.meta.custom?.type === 'tab') {
            hasTab = true
            break
          }
        }
      }
      if (!hasTab) {
        arr.push('layui-card-header')
      }
      return arr.join(' ')
    })
    return {
      ...card.setup(),
      cardHeadCss
    }
  }
}
</script>
