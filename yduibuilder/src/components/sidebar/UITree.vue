<template>
  <div class="pt-1 ps-1 flex-grow-1">
    <template v-if="currPage">
      <div class="text-muted p-1 d-flex justify-content-between align-items-center">
        <div class="fs-6">{{t("common.outline")}}</div>
        <div v-if="!treeIsOpen" @click="expandAll"><i class="iconfont hover-primary icon-expandall"></i></div>
        <div v-if="treeIsOpen" @click="collapseAll"><i class="iconfont hover-primary icon-collapseall"></i></div>
      </div>
      <UITreeWrap :pageid="currPage.meta.id" :uiconfig="currPage" :index="1" :tab="0"></UITreeWrap>
    </template>
  </div>
</template>

<script lang="ts">
/**
 * UI结构树
 */
import { computed, onMounted, ref } from 'vue'
import UITreeWrap from '@/components/sidebar/uitree/Wrap.vue'
import InitUI from '@/components/Common'
import $ from 'jquery'
import uidrag from '@/lib/uidrag'
import { useStore } from 'vuex'
import baseUIDefines from '@/components/ui/define'
import ydhl from '@/lib/ydhl'
import { useI18n } from 'vue-i18n'

export default {
  name: 'UITree',
  components: { UITreeWrap },
  emits: ['contextMenu'],
  setup (props: any, context: any) {
    const store = useStore()
    const { hoverUIItemId, selectedUIItemId } = InitUI()
    const { t } = useI18n()
    const treeIsOpen = ref(true)
    // console.log(pages.value)
    const dragoverUIItemId = computed(() => store.state.design.dragoverUIItemId)
    const dragoverPlacement = computed(() => store.state.design.dragoverPlacement)
    const currPage = computed(() => store.state.design.page)

    const uiMouseEnter = (event: any) => {
      const el = $(event.target)
      hoverUIItemId.value = $(el).parents('.uitree').attr('data-uiid')
    }

    const uiMouseLeave = (event: any) => {
      hoverUIItemId.value = ''
    }

    const uiClick = (event: any) => {
      uiChange($(event.target))
    }
    const uiChange = (el) => {
      const uitree = el.parents('.uitree')
      selectedUIItemId.value = uitree.attr('data-uiid')

      const scrollbar = document.querySelector('.workspace-scrollbar')
      if (scrollbar) {
        const uidom = document.getElementById(selectedUIItemId.value)
        if (uidom) {
          const rect = uidom.getBoundingClientRect()
          // console.log(rect, scrollbar.scrollTop)
          // scrollbar.scrollTo(rect.x, rect.y)
          // 200 是一个估值，确保uidom在主窗体内，不被上边和左边的内容遮挡
          scrollbar.scrollTo(rect.x - 200 + scrollbar.scrollLeft, rect.y - 200 + scrollbar.scrollTop)
        }
      }
    }

    const rightClick = (event: any) => {
      if (event.button === 2) {
        event.returnValue = false
        event.stopPropagation()
        event.preventDefault()
        uiChange($(event.target))
        context.emit('contextMenu', { x: event.pageX + 10, y: event.pageY })
        return false
      }
    }

    onMounted(() => {
      $('body').on('mouseover', '.uitree-item', uiMouseEnter)
      $('body').on('mouseout', '.uitree-item', uiMouseLeave)
      $('body').on('click', '.uitree-item', uiClick)
      $('body').on('contextmenu', '.uitree-item', rightClick)

      uidrag({
        target: '.uitree',
        dragend: () => {
          store.commit('clearDragoverState')
        },
        start: (uiDragFromWhere, event: any) => {
          if (uiDragFromWhere !== 'uiTree') return
          // console.log($(event.target))
          if ($(event.target).attr('data-isContainer') !== 'true') return
          $(event.target).find('.container-body').addClass('d-none')
        },
        over: (uiDragFromWhere, reference: any, dragOverPosi: Record<string, boolean>, isContainer: boolean, placement: any, uidragged: any) => {
          const uIItemId: any = $(reference).attr('id')
          // 从定制区域拖入，这时targetEl是ui tree元素，但sourceEl是设计器中ui元素，这时id的查找有所区别
          if (uiDragFromWhere === 'ui') {
            // 不能拖入自己的子元素中
            const sourceId = $(uidragged).attr('id')
            if ($(`[data-uiid='${uIItemId}']`).parents(`[data-uiid='${sourceId}']`).length > 0) {
              return
            }
          }
          // console.log(placement, uIItemId)
          if (dragoverUIItemId.value === uIItemId && dragoverPlacement.value === placement) return
          store.commit('updatePageState', { dragoverUIItemId: uIItemId, dragoverPlacement: placement })
        },

        /**
         * drop处理
         * @param sourceEl 被拖动的元素
         * @param targetEl 目标元素
         */
        drop: (uiDragFromWhere, sourceEl: Element, targetEl: Element) => {
          // console.log('uitree drop')
          let sourceId = $(sourceEl).attr('data-uiid')
          const sourcePageId = $(sourceEl).attr('data-pageid')
          const targetId = $(targetEl).attr('data-uiid')
          const targetPageId = $(targetEl).attr('data-pageid')
          // 从定制区域拖入，这时targetEl是ui tree元素，但sourceEl是设计器中ui元素，这时id的查找有所区别
          if (uiDragFromWhere === 'ui') {
            sourceId = $(sourceEl).attr('id')
            // 不能拖入自己的子元素中
            if ($(`[data-uiid='${targetId}']`).parents(`[data-uiid='${sourceId}']`).length > 0) {
              store.commit('clearDragoverState')
              return
            }
          }
          // 从组件边栏区域拖入
          if (uiDragFromWhere === 'uiItem') {
            const type: any = $(sourceEl).attr('data-type')
            const isContainer = baseUIDefines[type]?.isContainer || false
            // console.log({ sourceId, sourcePageId, targetId, targetPageId })

            store.commit('addItem', {
              type,
              meta: {
                id: ydhl.uuid(5, 0, targetPageId),
                title: type,
                isContainer: isContainer
              },
              pageId: targetPageId,
              placement: dragoverPlacement.value,
              targetId
            })
            return
          }
          // console.log(sourceId, targetId)

          store.commit('moveItem', { sourceId, sourcePageId, targetId, targetPageId })
          store.commit('clearDragoverState')
        }
      })
    })

    const collapseAll = () => {
      treeIsOpen.value = false
      $('.container-body').addClass('d-none')
    }
    const expandAll = () => {
      treeIsOpen.value = true
      $('.container-body').removeClass('d-none')
    }
    const contextMenu = (data) => {
      context.emit('contextMenu', data)
    }
    return {
      collapseAll,
      expandAll,
      t,
      dragoverUIItemId,
      dragoverPlacement,
      currPage,
      hoverUIItemId,
      selectedUIItemId,
      treeIsOpen,
      uiClick,
      uiMouseLeave,
      contextMenu,
      uiMouseEnter
    }
  }
}
</script>
