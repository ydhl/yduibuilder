<template>
  <div class="uitree uimouseup uitreecontainer">
    <UITreeTd :index="index" :isOpen="isOpen" :row="row" :column="column" :pageid="pageid" :has-items="items.length > 0"
              :uiconfig="uiconfig" :tab="tab" @click="toggleContainer"></UITreeTd>
    <div ref="containerBody" :class="{'container-body': true, 'd-none': !isOpen}">
      <UITreeWrap v-for="(item, i) in items"
                  :index="index+i+1" :key="i" :pageid="pageid" :uiconfig="item" :tab="tab+1"></UITreeWrap>
    </div>
  </div>
</template>

<script lang="ts">
import { ref, computed } from 'vue'
import InitUITree from '@/components/Common'
import $ from 'jquery'
import UITreeTd from '@/components/sidebar/uitree/UITreeTd.vue'

export default {
  name: 'UITreeTdWrap',
  components: { UITreeTd },
  props: {
    uiconfig: Object,
    index: Number,
    row: Number,
    columnItems: Object,
    column: Number,
    tab: Number,
    pageid: String,
    type: String
  },
  setup (props: any, context: any) {
    const { focusUIItem, selectedPageId } = InitUITree()
    const containerBody = ref()
    const isOpen = ref(false)
    isOpen.value = selectedPageId.value === props.pageid

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
    const items = computed(() => props.columnItems?.[props.row]?.[props.column] || [])

    return {
      focusUIItem,
      selectedPageId,
      containerBody,
      isOpen,
      toggleContainer,
      items
    }
  }
}
</script>
