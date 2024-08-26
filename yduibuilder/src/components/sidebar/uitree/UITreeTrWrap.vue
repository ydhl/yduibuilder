<template>
  <div class="uitree uimouseup uitreecontainer">
    <UITreeTr :index="index" :type="type" :isOpen="isOpen" :row="row" :pageid="pageid" :uiconfig="uiconfig" :tab="tab" @click="toggleContainer"></UITreeTr>
    <div ref="containerBody" :class="{'container-body': true, 'd-none': !isOpen}">
      <template v-for="columnIndex in columnCount" :key="columnIndex">
        <template v-if="!columnConfig?.[row]?.[columnIndex-1] || !columnConfig?.[row]?.[columnIndex-1]?.isMerged">
          <UITreeTdWrap :row="row" :column="columnIndex-1" :columnItems="columnItems"
                      :index="index+row+columnIndex" :type="type"
                      :pageid="pageid" :uiconfig="uiconfig" :tab="tab+1"></UITreeTdWrap>
        </template>
      </template>
    </div>
  </div>
</template>

<script lang="ts">
import { computed, ref } from 'vue'
import InitUITree from '@/components/Common'
import $ from 'jquery'
import UITreeTr from '@/components/sidebar/uitree/UITreeTr.vue'
import UITreeTdWrap from '@/components/sidebar/uitree/UITreeTdWrap.vue'

export default {
  name: 'UITreeTrWrap',
  components: { UITreeTdWrap, UITreeTr },
  props: {
    uiconfig: Object,
    columnItems: Array,
    index: Number,
    row: Number,
    tab: Number,
    type: String,
    pageid: String
  },
  setup (props: any, context: any) {
    const { focusUIItem, selectedPageId } = InitUITree()
    const containerBody = ref()
    const isOpen = ref(false)
    isOpen.value = selectedPageId.value === props.pageid
    const columnCount = computed(() => props.uiconfig?.meta?.custom?.columnCount || 2)

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

    const columnConfig = computed(() => props.uiconfig.meta?.custom?.columnConfig || {})
    return {
      focusUIItem,
      selectedPageId,
      containerBody,
      isOpen,
      toggleContainer,
      columnConfig,
      columnCount
    }
  }
}
</script>
