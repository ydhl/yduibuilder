<template>
  <div :class="[dragableCss, uiCss, 'card']"
       :draggable='!inlineEditItemId' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-isContainer="true"
       :data-pageid="pageid">
    <div :class="['card-header subui',{'dragenter-subcontainer': isDragIn && dragoverInParent=='head'}]" v-if="!uiconfig.meta.custom?.headless" data-placeInParent="head">
      <template v-if="!myItems.head.length">
        {{t('style.card.drapTip')}}
      </template>
      <UIBase v-for="(item, index) in myItems.head" :key="index" :is-readonly="myIsReadonly" :is-lock="myIsLock" :uiconfig="item" :pageid="pageid"></UIBase>
    </div>
    <div class="card-body">
      <template v-if="!hasMainItems">
        {{t('style.card.drapTip')}}
      </template>
      <UIBase v-for="(item, index) in mainItems" :key="index" :is-readonly="myIsReadonly" :is-lock="myIsLock" :uiconfig="item" :pageid="pageid"></UIBase>
    </div>
    <UIBase v-for="(item, index) in otherMainItems" :key="index" :is-readonly="myIsReadonly" :is-lock="myIsLock" :uiconfig="item" :pageid="pageid"></UIBase>
    <div :class="['card-footer subui',{'dragenter-subcontainer': isDragIn && dragoverInParent=='foot'}]" v-if="!uiconfig.meta.custom?.footless" data-placeInParent="foot">
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
import { computed, ref } from 'vue'

export default {
  name: 'Bootstrap_Card',
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
    const card = new Card(props, context)
    const setup = card.setup()
    const mainItems = ref<Array<any>>([])
    const otherMainItems = ref<Array<any>>([])
    const hasMainItems = computed(() => setup.myItems.value.main.length > 0)
    for (const item of setup.myItems.value.main) {
      if (item.type.toLowerCase() === 'list' || item.type.toLowerCase() === 'table') {
        otherMainItems.value.push(item)
      } else {
        mainItems.value.push(item)
      }
    }
    return {
      mainItems,
      otherMainItems,
      hasMainItems,
      ...setup
    }
  }
}
</script>
