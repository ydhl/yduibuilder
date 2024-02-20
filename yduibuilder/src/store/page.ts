import { UIBase } from '@/store/model'

/**
 * 在某个页面index中查找item的信息：序号和其ui配置 { index, uiConfig, parentConfig }
 * @param state
 * @param uiid
 * @return UIItemStruct
 */
function findUIItemInfo (state: Record<any, any>, uiid: string) {
  const _find = (uiid: string, parent: UIBase) => {
    const findStruct: any = {
      index: -1,
      uiConfig: null,
      parentConfig: null
    }
    if (parent.meta.id === uiid) {
      findStruct.index = 0
      findStruct.uiConfig = parent
      return findStruct
    }
    if (!parent.items) return findStruct

    for (let i = 0; i < parent.items.length; i++) {
      if (parent.items[i].meta.id === uiid) {
        findStruct.index = i
        findStruct.uiConfig = parent.items[i]
        findStruct.parentConfig = parent
        return findStruct
      }
      const subItems: any = parent.items[i].items || null
      if (subItems && subItems.length > 0) {
        const findInfo: any = _find(uiid, parent.items[i])
        if (findInfo.index !== -1) {
          return findInfo
        }
      }
    }

    return findStruct
  }
  return _find(uiid, state.uiconfig)
}

const store = {
  state: {
    project: {},
    /**
     * 页面下元素的的额外信息，key是id，value是自定义值，比如表格的数据
     * tableId: { header: footer: row }
     */
    extraInfo: {},
    /**
     * page的UIConfig
     */
    uiconfig: null,
    inlineEditItemId: '',
    /**
     * 所有已经加载完成的ui元素 id:boolean
     * 元素在onmouted中更新
     */
    loadedUIIds: {},
    showEventPanel: false, // 是否显示事件提示层
    hoverUIItemId: '',
    highlightUIItemIds: [], // 需要高亮显示的元素id
    selectedUIItemId: '',
    dragoverUIItemId: '',
    dragoverPlacement: '',
    dragoverInParent: '',
    updateInlineItemValue: false,
    endKind: '',
    backdropVisible: false
  },
  mutations: {
    updateExtraInfo (state: any, props: Record<any, any>) {
      for (const name in props) {
        state.extraInfo[name] = props[name]
      }
    },
    /**
     * design.clearDragoverState 调用后通过postMessage同步调用该同名方法进行page store的更新；
     * page store不会主动调用该方法
     * @param state
     */
    clearDragoverState (state: any) {
      state.dragoverUIItemId = ''
      state.dragoverPlacement = ''
      state.dragoverInParent = ''
    },
    /**
     * design.updatePageState 调用后通过postMessage同步调用该同名方法进行page store的更新；
     * page store不会主动调用该方法
     * @param state
     * @param props
     */
    updatePageState (state: any, props: Record<any, any>) {
      // console.log(props)
      for (const name in props) {
        if (!Object.prototype.hasOwnProperty.call(state, name)) continue
        // eslint-disable-next-line no-eval
        state[name] = props[name]
      }
    },
    /**
     * design.switchEventShow 调用后通过postMessage同步调用该同名方法进行page store的更新；
     * page store不会主动调用该方法
     * @param state
     */
    switchEventShow (state: any, showEventPanel) {
      state.showEventPanel = showEventPanel
    },
    /**
     * 和updatePageState的区别：某些组件（如Upload.vue）会同时用在workspace中和iframe的page中, 这两者是不同的store，但props名称用途一直，
     * 这里和design store中保持同名只是便于这些组件调用commit时不用区分是那个store
     * @param state
     * @param props
     */
    updateState (state: any, props: Record<any, any>) {
      for (const name in props) {
        state[name] = props[name]
      }
    },
    loaded (state: any, { id }) {
      if (!id) return
      state.loadedUIIds[id] = true
    }
  },
  actions: {
  },
  getters: {
    /**
     * 获取当前选中页面中指定id的元素
     * @param state
     * @return { index, uiConfig, parentConfig }
     */
    getUIItemInPage: (state) => (uiid: string) => {
      return findUIItemInfo(state, uiid)
    }
  }
}

export default store
export function pickStateFromDesign (pageid, designState) {
  const state:any = {}
  for (const item in store.state) {
    if (item === 'uiconfig') {
      state.uiconfig = designState.page
      continue
    }
    state[item] = designState[item]
  }

  state.loadedUIIds = {}
  return state
}
