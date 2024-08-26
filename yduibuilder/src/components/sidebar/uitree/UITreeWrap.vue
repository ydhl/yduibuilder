<template>
  <div :class="{'uitree uimouseup': true, 'uitreecontainer': isContainer}"  :id="'uitree-'+uiconfig.meta.id"
       :draggable="uiconfig.type!=='Page' && !isInlineEdit" @mouseup.stop="uiMouseUp"
       :data-type="uiconfig.type" :data-isContainer="uiconfig.meta.isContainer" :data-uiid="uiconfig.meta.id" :data-pageid="pageid">
    <!-- left top drop placement-->
    <div class="uitree-placement" v-if="dragIsUp" :data-type="uiconfig.type" :data-uiid="uiconfig.meta.id" :data-pageid="pageid"></div>
    <UITreeItem @onInlineEdit="onInlineEdit" :index="index" :isOpen="isOpen" :pageid="pageid" :uiconfig="uiconfig" :tab="tab" @click="toggleContainer"></UITreeItem>
    <!-- right bottom drop placement-->
    <div class="uitree-placement" v-if="dragIsDown" :data-type="uiconfig.type" :data-uiid="uiconfig.meta.id" :data-pageid="pageid"></div>
    <div ref="containerBody" :class="{'container-body': true, 'd-none': !isOpen}" v-if="isContainer">
      <UITreeWrap v-for="(item, i) in uiconfig.items" :index="index+i+1" :key="i" :pageid="pageid" :uiconfig="item" :tab="tab+1"></UITreeWrap>
    </div>
    <div ref="containerBody" :class="{'container-body': true, 'd-none': !isOpen}" v-else-if="isTableContainer">
      <template v-if="!uiconfig.meta.custom?.headless">
        <UITreeTRWrap v-for="row in headerRow" :key="row" type="header" :columnItems="columnItems"
                      :index="index + row" :row="row-1" :pageid="pageid" :uiconfig="uiconfig" :tab="tab+1"></UITreeTRWrap>
      </template>
      <UITreeTRWrap v-for="row in bodyRow" :key="row" type="body" :columnItems="columnItems"
                    :index="index + row" :row="row-1+headerRow" :pageid="pageid" :uiconfig="uiconfig" :tab="tab+1"></UITreeTRWrap>
      <template v-if="!uiconfig.meta.custom?.footless">
        <UITreeTRWrap v-for="row in footerRow" :key="row" type="footer" :columnItems="columnItems"
                      :index="index + row" :row="row-1+headerRow+bodyRow" :pageid="pageid" :uiconfig="uiconfig" :tab="tab+1"></UITreeTRWrap>
      </template>
    </div>
  </div>
</template>

<script lang="ts">
import { computed, ref } from 'vue'
import { useStore } from 'vuex'
import InitUITree from '@/components/Common'
import UITreeItem from '@/components/sidebar/uitree/UITreeItem.vue'
import $ from 'jquery'
import _ from 'lodash'
import UITreeTRWrap from '@/components/sidebar/uitree/UITreeTrWrap.vue'

export default {
  name: 'UITreeWrap',
  components: { UITreeTRWrap, UITreeItem },
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
    const isContainer = computed(() => props.uiconfig.meta.isContainer && props.uiconfig.type !== 'Table')
    const isTableContainer = computed(() => props.uiconfig.meta.isContainer && props.uiconfig.type === 'Table')
    const dragoverUIItemId = computed(() => store.state.design.dragoverUIItemId)
    const dragoverPlacement = computed(() => store.state.design.dragoverPlacement)
    const dragIsUp = computed(() => dragoverUIItemId.value === props.uiconfig.meta.id && (dragoverPlacement.value === 'left' || dragoverPlacement.value === 'top'))
    const dragIsDown = computed(() => dragoverUIItemId.value === props.uiconfig.meta.id && (dragoverPlacement.value === 'right' || dragoverPlacement.value === 'bottom'))
    const isInlineEdit = ref(false)

    const footerRow = computed(() => props.uiconfig?.meta?.custom?.footerRow || 1)
    const headerRow = computed(() => props.uiconfig?.meta?.custom?.headerRow || 1)
    const bodyRow = computed(() => props.uiconfig?.meta?.custom?.bodyRow || 1)

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
    const uiMouseUp = _.debounce((event) => {
      store.commit('updateState', { mouseupInFrame: event.clientX + '_' + event.clientY })
    }, 100)

    const columnItems = computed(() => {
      if (props.uiconfig.type !== 'Table' || !props.uiconfig.items) return {}
      const items:any = {}
      for (const item of props.uiconfig.items) {
        const placeInParent = item.placeInParent
        if (!placeInParent) continue
        const [row, column] = placeInParent.split('-')

        if (!items[row]) items[row] = {}
        if (!items[row][column]) items[row][column] = []
        items[row][column].push(item)
      }
      return items
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
      uiMouseUp,
      toggleContainer,
      onInlineEdit,
      isInlineEdit,
      isTableContainer,
      notSubPageItem,
      headerRow,
      columnItems,
      bodyRow,
      footerRow
    }
  }
}
</script>
