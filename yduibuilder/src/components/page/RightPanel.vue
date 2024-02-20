<template>
  <div id="right-sidebar">
    <div class="header" ref="head" :style="headerStyle">
      <div class="left-tip" v-show="leftHasMore"></div>
      <div class="header-scrollbar" ref="headScrollbar" @scroll="headScroll" @wheel="headWheel">
        <ul class="nav nav-tabs">
          <li class="nav-item"><a class="nav-link disabled" disabled style="width: 10px !important"><i class="iconfont icon-placeholder"></i></a></li>
          <li class="nav-item">
            <a :class="{'nav-link': true, 'active': currSidebar.tab=='UIInfo' && rightSidebarIsOpen}" href="javascript:void(0)" @click="openSidebar('UIInfo')">
              <i class="iconfont icon-info"></i>
              {{ t("ui.info") }}</a>
          </li>
          <li class="nav-item">
            <a :class="{'nav-link': true, 'active': currSidebar.tab=='UIStyle' && rightSidebarIsOpen}" href="javascript:void(0)" @click="openSidebar('UIStyle')">
              <i class="iconfont icon-style"></i>
              {{ t("common.style") }}</a>
          </li>
          <li class="nav-item flex-grow-1"><a class="nav-link disabled" disabled><i class="iconfont icon-placeholder"></i></a></li>
        </ul>
      </div>
      <div class="right-tip" v-show="rightHasMore"></div>
    </div>
    <template v-if="rightSidebarIsOpen">
      <div class="panel" :style="panelStyle">
        <div class="split"></div>
        <div class="right-panel-scroll">
          <keep-alive>
            <component :is="currSidebar.sidebar" @contextMenu="contextMenu"/>
          </keep-alive>
        </div>
      </div>
      <div class="close-button" :style="closeButtonStyle" @click="toggleRightSidebar">
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="28.35px" height="56px" viewBox="0 0 28.35 56" enable-background="new 0 0 28.35 56" xml:space="preserve">
            <g>
              <path fill="#333333" d="M0,1.589l9.771,5.642c0.91,0.526,1.47,1.495,1.47,2.544v36.45 c0,1.049-0.56,2.019-1.47,2.543L0,54.411"></path>
              <polyline fill="none" stroke="#000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" :points="arrowPoint"></polyline>
            </g>
          </svg>
      </div>
    </template>
  </div>
</template>

<script lang="ts">
import UIStyle from '@/components/sidebar/UIStyle.vue'
import UIInfo from '@/components/sidebar/UIInfo.vue'
import UIExport from '@/components/sidebar/UIExport.vue'
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import split from '@/lib/split'
import $ from 'jquery'
import { useStore } from 'vuex'
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'

export default {
  name: 'RightPanel',
  components: {
    UIInfo,
    UIStyle,
    UIExport
  },
  emits: ['contextMenu'],
  setup (props: any, ctx: any) {
    const info = initUI()
    // 1.988,24.172 5.816,28 1.988,31.828
    const arrowPoint = ref('5.817,31.828 1.989,28 5.817,24.172')
    const splitStartWidth = ref(0)
    const leftHasMore = ref(false)
    const rightHasMore = ref(false)

    const headScrollbar = ref(null)
    const head = ref()

    const store = useStore()

    const sideBars = computed(() => store.state.design.rightSidebars)
    const rightSidebarMinWidth = computed(() => store.state.design.rightSidebarMinWidth)
    const pageScale = computed(() => store.state.design.scale)
    const rightSidebarIsOpen = computed({
      get () {
        return store.state.design.rightSidebarIsOpen
      },
      set (v: boolean) {
        store.commit('updateState', { rightSidebarIsOpen: v })
      }
    })
    const rightSidebarWidth = computed({
      get () {
        return store.state.design.rightSidebarWidth
      },
      set (v: number) {
        store.commit('updateState', { rightSidebarWidth: v })
      }
    })
    const headerStyle = computed((ctx: any) => `width:${rightSidebarWidth.value}px`)
    const closeButtonStyle = computed((ctx: any) => `right: ${rightSidebarIsOpen.value ? rightSidebarWidth.value - 1 : 0}px`)
    const currSidebar = computed({
      get () {
        const stacks: any = sideBars.value
        if (!stacks || stacks.length === 0) return ''
        return stacks[stacks.length - 1]
      },
      set (v: string) {
        store.commit('updateState', { rightSidebars: v !== '' ? [{ sidebar: v }] : [] })
      }
    })
    const panelStyle = computed((ctx: any) => `width:${rightSidebarWidth.value}px`)

    const changeHeadMoreState = () => {
      leftHasMore.value = ($(headScrollbar.value as any).scrollLeft() || 0) > 0
      rightHasMore.value = (($(head.value as any).width() || 0) + ($(headScrollbar.value as any).scrollLeft() || 0)) < (headScrollbar.value as any).scrollWidth
      // console.log($(this.$refs.headScrollbar).scrollLeft(), $(this.$refs.head).width(), this.$refs.headScrollbar.scrollWidth)
    }

    const mounted = () => {
      split('#right-sidebar .split', () => {
        splitStartWidth.value = rightSidebarWidth.value
        return {
          spliting: (dist: number) => {
            // console.log('spliting right')
            if (rightSidebarWidth.value < rightSidebarMinWidth.value) {
              rightSidebarWidth.value = rightSidebarMinWidth.value
              return false
            }
            if (rightSidebarWidth.value > rightSidebarMinWidth.value * 3) {
              rightSidebarWidth.value = rightSidebarMinWidth.value * 3
              return false
            }
            rightSidebarWidth.value = splitStartWidth.value - dist // 往左dist是负数，但宽度是增加
            // console.log(dist + ',' + this.splitStartWidth)
            return true
          }
        }
      })
      nextTick(() => {
        changeHeadMoreState()

        $('body').off('click', '.style-header')
        $('body').on('click', '.style-header', (event) => {
          if (event.target === null) return
          let target: any = event.target
          if (!$(target).hasClass('style-header')) target = $(target).parents('.style-header').get(0)
          const nextSibling: HTMLElement = target.nextSibling
          if (nextSibling === null || !nextSibling.classList) return
          if (nextSibling.classList.contains('d-none')) {
            $(target).find('.icon-tree-close').removeClass('icon-tree-close').addClass('icon-tree-open')
            nextSibling.classList.remove('d-none')
          } else {
            $(target).find('.icon-tree-open').removeClass('icon-tree-open').addClass('icon-tree-close')
            nextSibling.classList.add('d-none')
          }
        })
      })
    }

    const openSidebar = (sidebar: any) => {
      store.commit('updateState', { rightSidebars: [{ sidebar, tab: sidebar }], rightSidebarIsOpen: true })
    }
    const toggleRightSidebar = () => {
      rightSidebarIsOpen.value = !rightSidebarIsOpen.value
    }
    const headScroll = (event: any) => {
      if (leftHasMore.value || rightHasMore.value) {
        event.stopPropagation()
        event.preventDefault()
        event.cancelBubble = true
      }
      changeHeadMoreState()
    }
    const headWheel = (event: any) => {
      if (leftHasMore.value || rightHasMore.value) {
        event.stopPropagation()
        event.preventDefault()
        event.cancelBubble = true
      }
      const _headScrollbar = $(headScrollbar.value as any)
      // console.log(event);
      // event.deltaY 滚动方向与距离
      const scrollLeft = _headScrollbar.scrollLeft() || 0
      _headScrollbar.scrollLeft((scrollLeft + event.deltaY))
    }
    onMounted(mounted)
    watch(rightSidebarWidth, (newValue, oldValue) => {
      changeHeadMoreState()
    })
    const contextMenu = (data) => {
      ctx.emit('contextMenu', data)
    }
    const { t } = useI18n()
    return {
      ...info,
      arrowPoint,
      splitStartWidth,
      leftHasMore,
      rightHasMore,
      sideBars,
      rightSidebarMinWidth,
      rightSidebarIsOpen,
      pageScale,
      panelStyle,
      headerStyle,
      closeButtonStyle,
      currSidebar,
      head,
      headScrollbar,
      t,
      openSidebar,
      changeHeadMoreState,
      toggleRightSidebar,
      headScroll,
      headWheel,
      contextMenu
    }
  }
}
</script>
