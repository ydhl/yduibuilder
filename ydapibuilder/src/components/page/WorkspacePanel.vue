<template>
  <div class="workspace">
    <div class="workspace-header" ref="head" :style="headStyle">
      <div class="left-tip" v-show="leftHasMore"></div>
      <div class="header-scrollbar" ref="headScrollbar" @scroll="headScroll" @wheel="headWheel" >
        <div class="p-1"></div>
        <ul class="nav nav-tabs flex-nowrap">
          <li class="nav-item" v-for="(tab, index) in tabs" :key="index">
            <a style="line-height: 18px" :class="{'nav-link text-dark d-flex align-items-center justify-content-center': true, 'active': activeTabIndex == index}" aria-current="page" @click="changeTab(index)" href="javascript:void(0)">
              {{t(tab.name)}}&nbsp;
              <ConfirmRemove v-if="index>0" @remove="removeTab(tab, index)"></ConfirmRemove>
            </a>
          </li>
        </ul>
        <div class="p-1"></div>
      </div>
      <div class="right-tip" v-show="rightHasMore"></div>
    </div>
    <div class="cooperation" v-if="userList && userList.length > 0">
      <div class="cooperation-body">
        <div class="text-muted fs-7 me-2 cursor" @click="openCooperationTip">{{t('common.somebodyComeIn')}} <i class="iconfont icon-tips"></i>:</div>
        <div v-for="(user, index) in userList" :key="index" class="d-flex justify-content-center align-items-center me-3">
          <div class="rounded-circle me-1 cooperation-avatar" :style="`background-image: url(${user.avatar});`"></div>
          <span class="fs-7 text-truncate" style="width: 50px">{{user.name}}</span>
        </div>
      </div>
    </div>
    <div class="workspace-body">
      <div class="workspace-scrollbar">
        <template v-for="(tab, index) in tabs" :key="index">
          <keep-alive v-if="tab.page=='WelcomePage'" >
              <WelcomePage :tabIndex="index" v-if="index==activeTabIndex"></WelcomePage>
          </keep-alive>
          <keep-alive v-if="tab.page=='APIAdd'">
              <APIAdd :tabIndex="index" :query="tab.query" v-if="index==activeTabIndex"></APIAdd>
          </keep-alive>
          <keep-alive v-if="tab.page=='APIDetail'">
              <APIDetail :tabIndex="index" :query="tab.query" v-if="index==activeTabIndex"></APIDetail>
          </keep-alive>
          <keep-alive v-if="tab.page=='Enviroment'">
              <Enviroment :tabIndex="index" :query="tab.query" v-if="index==activeTabIndex"></Enviroment>
          </keep-alive>
        </template>
      </div>
    </div>
  </div>
</template>
<script lang="ts">
import { ref, computed, watch, onMounted, nextTick, defineAsyncComponent } from 'vue'
import $ from 'jquery'
import ydhl from '@/lib/ydhl'
import { useStore } from 'vuex'
import { layer } from '@layui/layui-vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
const ConfirmRemove = defineAsyncComponent(() => import('@/components/common/ConfirmRemove.vue'))
const APIAdd = defineAsyncComponent(() => import('./APIAdd.vue'))
const APIDetail = defineAsyncComponent(() => import('./APIDetail.vue'))
const WelcomePage = defineAsyncComponent(() => import('./WelcomePage.vue'))
const Enviroment = defineAsyncComponent(() => import('@/components/setting/Enviroment.vue'))

export default {
  emits: ['contextMenu'],
  name: 'WorkspacePanel',
  components: {
    ConfirmRemove,
    APIAdd,
    Enviroment,
    APIDetail,
    WelcomePage
  },
  setup (props: any, context: any) {
    const workspaceStyle = ref('')
    const headStyle = ref('')
    const leftHasMore = ref(false)
    const rightHasMore = ref(false)
    const headScrollbar = ref(null)
    const head = ref(null)
    const store = useStore()
    const router = useRouter()
    const { t } = useI18n()

    const leftSidebarWidth = computed(() => store.state.design.leftSidebarWidth)
    const leftSidebars = computed(() => store.state.design.leftSidebars)
    const project = computed(() => store.state.design.project)
    const userList = computed(() => store.state.design.userList)
    const tabs = computed(() => store.state.design.tabs)
    const activeTabIndex = computed(() => store.state.design.activeTab)
    const activeTab = computed(() => store.state.design.tabs[store.state.design.activeTab])
    /**
     * 根据左右边栏的打开尺寸，计算workdspace的宽度和head的宽度，如果
     * workspace的宽度小于了页面的宽度，则更新design.scale让页面进行缩放
     */
    const computeStyle = () => {
      // debugger
      const _leftSidebarWidth = leftSidebars.value.length > 0 ? leftSidebarWidth.value : 0
      const dom = document.getElementById('left-panel')
      const leftPanelWidth = dom ? dom.getBoundingClientRect().width || 0 : 0

      const leftWidth = _leftSidebarWidth + leftPanelWidth

      const _workspaceStyle: any = []
      _workspaceStyle.push(`left:${leftWidth}px`)
      _workspaceStyle.push(`width: ${document.body.clientWidth - leftWidth}px`)

      const _headStyle: any = []
      _headStyle.push(`left:${leftWidth}px`)
      _headStyle.push(`width:${document.body.clientWidth - leftWidth}px`)

      workspaceStyle.value = _workspaceStyle.join(';')
      headStyle.value = _headStyle.join(';')
    }

    const changeHeadMoreState = () => {
      leftHasMore.value = ($(headScrollbar.value as any).scrollLeft() || 0) > 0
      rightHasMore.value = (($(head.value as any).width() || 0) + ($(headScrollbar.value as any).scrollLeft() || 0)) < (headScrollbar.value as any).scrollWidth
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

    const openCooperationTip = () => {
      layer.open({ shade: true, shadeOpacity: '0.4', title: 'Tips', content: t('common.somebodyComeInTip') })
    }

    watch(leftSidebars, () => {
      computeStyle()
    })

    watch(leftSidebarWidth, () => {
      computeStyle()
      changeHeadMoreState()
    })

    onMounted(() => {
      computeStyle()

      nextTick(() => {
        changeHeadMoreState()
      })
    })
    const changeTab = (tabIndex) => {
      store.commit('updateState', { activeTab: tabIndex })
    }
    const removeTab = (tab, tabIndex) => {
      store.commit('removeTab', tabIndex)
    }
    const goto = (path, query = {}) => {
      router.push({ path, query })
    }

    return {
      t,
      tabs,
      changeTab,
      activeTab,
      activeTabIndex,
      leftSidebarWidth,
      workspaceStyle,
      headStyle,
      leftHasMore,
      rightHasMore,
      head,
      headScrollbar,
      project,
      userList,
      api: ydhl.api,
      goto,
      removeTab,
      openCooperationTip,
      headScroll,
      headWheel,
      changeHeadMoreState
    }
  }
}
</script>
