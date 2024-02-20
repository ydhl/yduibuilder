<template>
  <div :class="{'uitree': true, 'uitreecontainer': isContainer}"  :id="'uitree-'+uiconfig.meta.id" :draggable="uiconfig.type!=='Page' && !isInlineEdit"
       :data-type="uiconfig.type" :data-isContainer="uiconfig.meta.isContainer" :data-uiid="uiconfig.meta.id" :data-pageid="pageid">
    <!-- left top drop placement-->
    <div class="uitree-placement" v-if="dragIsUp" :data-type="uiconfig.type" :data-uiid="uiconfig.meta.id" :data-pageid="pageid"></div>
    <UITreeLoader @onInlineEdit="onInlineEdit" :index="index" :isOpen="isOpen" :pageid="pageid" :uiconfig="uiconfig" :tab="tab" @click="toggleContainer"></UITreeLoader>
    <!-- right bottom drop placement-->
    <div class="uitree-placement" v-if="dragIsDown" :data-type="uiconfig.type" :data-uiid="uiconfig.meta.id" :data-pageid="pageid"></div>
    <div ref="containerBody" :class="{'container-body': true, 'd-none': !isOpen}" v-if="isContainer">
      <UITreeWrap v-for="(item, i) in uiconfig.items" :index="index+i+1" :key="i" :pageid="pageid" :uiconfig="item" :tab="tab+1"></UITreeWrap>
    </div>
  </div>
</template>

<script lang="ts">
import { computed, ref } from 'vue'
import { useStore } from 'vuex'
import InitUITree from '@/components/Common'
import UITreeLoader from '@/components/sidebar/uitree/Loader.vue'
import $ from 'jquery'

export default {
  name: 'UITreeWrap',
  components: { UITreeLoader },
  props: {
    uiconfig: Object,
    index: Number,
    tab: Number,
    pageid: String
  },
  setup (props: any, context: any) {
    const store = useStore()
    const { focusUIItem, selectedPageId } = InitUITree()
    const containerBody = ref()
    const isOpen = ref(false)
    isOpen.value = selectedPageId.value === props.pageid
    const isContainer = computed(() => props.uiconfig.meta.isContainer)
    const dragoverUIItemId = computed(() => store.state.design.dragoverUIItemId)
    const dragoverPlacement = computed(() => store.state.design.dragoverPlacement)
    const dragIsUp = computed(() => dragoverUIItemId.value === props.uiconfig.meta.id && (dragoverPlacement.value === 'left' || dragoverPlacement.value === 'top'))
    const dragIsDown = computed(() => dragoverUIItemId.value === props.uiconfig.meta.id && (dragoverPlacement.value === 'right' || dragoverPlacement.value === 'bottom'))
    const isInlineEdit = ref(false)

    const toggleContainer = () => {
      if ($(containerBody.value).hasClass('d-none')) {
        isOpen.value = true
        $(containerBody.value).removeClass('d-none')
      } else {
        isOpen.value = false
        $(containerBody.value).addClass('d-none')
      }
      return true
    }

    const onInlineEdit = (isInline) => {
      isInlineEdit.value = isInline
    }

    const notSubPageItem = computed(() => {
      if (props.uiconfig.pageType !== 'subpage') return true
      return props.uiconfig.meta.id === props.pageid
    })
    return {
      focusUIItem,
      selectedPageId,
      dragoverUIItemId,
      dragoverPlacement,
      dragIsUp,
      dragIsDown,
      isContainer,
      containerBody,
      isOpen,
      toggleContainer,
      onInlineEdit,
      isInlineEdit,
      notSubPageItem
    }
  }
}
</script>
