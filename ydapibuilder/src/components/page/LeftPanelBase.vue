<template>
  <div>
    <div id="left-panel">
      <div class="left-panel-scroll">
        <div class="item pt-2"></div>
        <slot name="menu"></slot>
      </div>
    </div>
    <div id="left-sidebar" :style="leftSidebarStyle" v-if="currSidebar!==''">
      <div class="sidebar-body">
        <div class="body-scroll">
          <slot name="panel"></slot>
        </div>
        <div class="split"></div>
      </div>
      <div class="close-button" @click="currSidebar=''">
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="28.35px" height="56px" viewBox="0 0 28.35 56" enable-background="new 0 0 28.35 56" xml:space="preserve">
          <g>
            <path fill="none" stroke="#313333" stroke-miterlimit="10" d="M0,1.589l9.771,5.642c0.91,0.526,1.47,1.495,1.47,2.544v36.45 c0,1.049-0.56,2.019-1.47,2.543L0,54.411"/>
            <polyline fill="none" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" points="5.817,31.828 1.989,28 5.817,24.172"/>
          </g>
        </svg>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import split from '@/lib/split'
import { ref, onMounted, computed } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import ydhl from '@/lib/ydhl'
import router from '@/router'
export default {
  name: 'LeftPanelBase',
  emits: ['SideBar'],
  setup (props: any, context: any) {
    const splitStartWidth = ref(0)
    const store = useStore()

    const leftSidebarMinWidth = computed(() => store.state.design.leftSidebarMinWidth)
    const leftSidebarWidth = computed({
      get () {
        return store.state.design.leftSidebarWidth
      },
      set (v: number) {
        store.commit('updateState', { leftSidebarWidth: v })
      }
    })

    const sideBars = computed(() => store.state.design.leftSidebars)
    const leftSidebarStyle = computed((ctx: any) => `width:${leftSidebarWidth.value}px`)
    const currSidebar = computed({
      get () {
        const stacks: any = sideBars.value
        if (!stacks || stacks.length === 0) return ''
        return stacks[stacks.length - 1].sidebar
      },
      set (v: string) {
        store.commit('updateState', { leftSidebars: v !== '' ? [{ sidebar: v }] : [] })
      }
    })

    const onmouted = async () => {
      const splitSelector = '#left-sidebar .split'
      split(splitSelector, () => {
        splitStartWidth.value = leftSidebarWidth.value
        return {
          spliting: (dist: number) => {
            // console.log(dist)
            // if (target !== splitSelector) return
            if (leftSidebarWidth.value < leftSidebarMinWidth.value) {
              leftSidebarWidth.value = leftSidebarMinWidth.value
              return false
            }
            if (leftSidebarWidth.value > leftSidebarMinWidth.value * 2) {
              leftSidebarWidth.value = leftSidebarMinWidth.value * 2
              return false
            }
            leftSidebarWidth.value = splitStartWidth.value + dist // 往左dist是负数，但宽度是减少
            // console.log(dist + ',' + this.splitStartWidth)
            return true
          }
        }
      })
    }
    onMounted(onmouted)
    const goto = (path: string, query = {}) => {
      router.push({ path, query })
    }
    const { t } = useI18n()
    const version = computed(() => ydhl.version)
    return {
      t,
      splitStartWidth,
      leftSidebarStyle,
      currSidebar,
      goto,
      version
    }
  }
}
</script>
