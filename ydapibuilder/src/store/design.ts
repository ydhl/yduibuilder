import ydhl from '@/lib/ydhl'
import router from '@/router'
import { WorkspaceTab } from '@/store/model'

function cleanState (state) {
  state.backdropVisible = false
  state.saved = -1
}
export default {
  state: {
    saved: -1, // -1 初始状态（第一次加载） 1已保存 0未保存
    saving: false, // true 保存中
    canEdit: false, // 是否能编辑页面
    userRole: '', // 用户在项目中的角色
    project: {}, // 当前设计的项目信息
    backdropVisible: false,
    currApi: '', // 当前打开的apiid
    api: {}, // 所有打开的的api信息
    tabs: [{ page: 'WelcomePage', name: 'common.welcomePage' }], // 所有打开的tab标签
    activeTab: 0,
    /**
     * 打开当前页面的用户列表
     */
    userList: [],
    /**
     * 左右边栏的状态
     */
    leftSidebars: [], // 当前打开的左边栏模块
    rightSidebars: [], // 当前打开的右边栏模块 { sidebar: 'UITree', tab: 'UITree' }
    rightSidebarIsOpen: false, // 又右边栏是否处于打开状态
    rightSidebarWidth: 350, // 右边栏默认的宽度大小
    rightSidebarMinWidth: 250, // 右边栏最小宽度大小，控制right header
    leftSidebarWidth: 280, // 左边栏默认的宽度
    leftSidebarMinWidth: 280, // 左边栏最小的宽度
    /**
     * WebSocket 对象
     */
    socket: null
  },
  mutations: {
    updateTabTitle (state, { tabIndex, name }) {
      if (!state.tabs[tabIndex]) return
      state.tabs[tabIndex].name = name
    },
    putApi (state: any, api: any) {
      state.api[api.id] = api
    },
    putTab (state: any, tab: WorkspaceTab) {
      state.tabs.push(tab)
      state.activeTab = state.tabs.length - 1
      state.leftSidebars = []
    },
    /**
     * 删除tab标签
     * @param state
     * @param props
     */
    removeTab (state: any, index: number) {
      state.activeTab = index - 1
      state.tabs.splice(index, 1)
    },
    /**
     * 更新某个标签页信息
     * @param state
     * @param index
     */
    updateTab (state: any, { index, tab }: { index: number, tab: WorkspaceTab}) {
      state.activeTab = index
      state.tabs[index] = tab
    },
    cleanState (state: any) {
      cleanState(state)
    },
    /**
     * 对state中的单个属性的统一处理
     * @param state
     * @param props
     */
    updateState (state: any, props: Record<any, any>) {
      // console.log(state)
      for (const name in props) {
        // eslint-disable-next-line no-eval
        // eval(`state.${name}=props[name]`)
        state[name] = props[name]
      }
    }
  },
  actions: {
  },
  getters: {
  }
}
