import { computed } from 'vue'
import { useStore } from 'vuex'
import baseUIDefines from '@/components/ui/define'

/**
 * 处理UI的悬浮、选择、ui库及其版本等基础ui信息以及提供store的get，set助手方法，
 * 部分逻辑是对通过选中的元素进行处理
 * 部分逻辑是对"自己"（传入的uiconfig）进行处理
 * 该Common.ts只提供非ui元素用
 */
export default function (uiconfig: any = null) {
  const store = useStore()
  const selectedUIItemId = computed({
    get: () => store.state.design?.selectedUIItemId || undefined,
    set: (v) => {
      store.commit('updatePageState', { selectedUIItemId: v })
    }
  })
  const selectedPageId = computed(() => store.state.design.page?.meta?.id)
  const selectedUIItem = computed(() => {
    if (!selectedUIItemId.value) return null
    const { uiConfig } = store.getters.getUIItemInPage(selectedUIItemId.value, selectedPageId.value)
    return uiConfig
  })
  const previewStyleItem = computed(() => {
    return store.state.design.previewStyleItem
  })
  const endKind = computed<string>(() => store.state.design.endKind)
  const hoverUIItemId = computed({
    get: () => store.state.design.hoverUIItemId,
    set: (v) => {
      store.commit('updatePageState', { hoverUIItemId: v })
    }
  })
  const ui = computed(() => store.state.design.project.ui)
  const uiVersion = computed(() => store.state.design.project.ui_version)

  const cssMap = computed(() => store.state.css)
  // console.log(cssMap)
  const selectedUIItemIsInput = computed(() => {
    return selectedUIItem.value ? baseUIDefines[selectedUIItem.value.type]?.isInput : false
  })
  /**
   * 设置一个ui元素的meta指，如果是meta中的普通元素，直接传入name，value，如果meta中的符合元素，需要传入complexTypeName，比如custom，css等
   * @param name
   * @param value
   * @param complexTypeName
   * @param isMerge 默认情况下，都是进行覆盖赋值设置，对于复合元素，如果要合并新旧值，需要设置true
   * @param previewMode true为预览模式，这是设置对是store中对previewStyleItem；false为设置选择对ui item
   */
  const setMeta = (name, value, complexTypeName: string = '', isMerge:boolean = false, previewMode = false) => {
    const props = {}
    props[name] = value
    if (previewMode) {
      store.commit('updatePreviewStyle', {
        type: complexTypeName || null,
        isMerge: isMerge,
        props
      })
      return
    }

    store.commit('updateItemMeta', {
      itemid: selectedUIItemId.value,
      type: complexTypeName || null,
      isMerge: isMerge,
      pageId: selectedPageId.value,
      props
    })
  }
  /**
   * 获取指定的meta值，找不到返回undefined
   * @param name
   * @param complexTypeName
   * @param previewMode true为预览模式，这是设置对是store中对previewStyleItem；false为设置选择对ui item
   */
  const getMeta = (name, complexTypeName: string = '', previewMode = false) => {
    if (previewMode) {
      if (!previewStyleItem.value.meta) return undefined
      if (complexTypeName && !previewStyleItem.value.meta[complexTypeName]) return undefined
      return complexTypeName ? previewStyleItem.value.meta[complexTypeName][name] : previewStyleItem.value.meta[name]
    }

    if (!selectedUIItem.value) return undefined
    let rst
    if (complexTypeName) {
      rst = selectedUIItem.value.meta?.[complexTypeName]?.[name]
    } else {
      rst = selectedUIItem.value.meta[name]
    }
    if (rst !== undefined) return rst

    //  再看selector中是否有值，主要是style的相关内容
    if (complexTypeName) {
      rst = selectedUIItem.value.meta?.selector?.[complexTypeName]?.[name]
    } else {
      rst = selectedUIItem.value.meta?.selector?.[name]
    }
    return rst
  }
  /**
   * 封装computed（包含get set）
   * @param name meta 名称
   * @param complexTypeName meta分组，比如style，custom
   * @param defalutValue get 时默认值
   * @param isMerge set时是否merge
   * @param previewMode true为预览模式，这是设置对是store中对previewStyleItem；false为设置选择对ui item
   */
  const computedWrap = (name, complexTypeName = '', defalutValue: any = undefined, isMerge = false, previewMode = false) => {
    return computed({
      get: () => {
        return getMeta(name, complexTypeName, previewMode) || defalutValue
      },
      set: (v) => {
        setMeta(name, v, complexTypeName, isMerge, previewMode)
      }
    })
  }
  const hasInheritStyle = (complexTypeName: string, names: Array<string>) => {
    if (!selectedUIItem.value) return false
    for (const name of names) {
      let rst
      if (complexTypeName) {
        rst = selectedUIItem.value.meta?.selector?.[complexTypeName]?.[name]
      } else {
        rst = selectedUIItem.value.meta?.selector?.[name]
      }
      if (rst) return true
    }
    return false
  }
  const hasSetStyle = (complexTypeName: string, names: Array<string>) => {
    if (!selectedUIItem.value) return false

    for (const name of names) {
      let rst
      if (complexTypeName) {
        rst = selectedUIItem.value.meta?.[complexTypeName]?.[name]
      } else {
        rst = selectedUIItem.value.meta[name]
      }
      if (rst) return true
    }
    return false
  }
  const focusUIItem = computed(() => {
    if (store.state.design.hoverUIItemId !== '') return store.state.design.hoverUIItemId
    return store.state.design.selectedUIItemId
  })
  // 对自己进行处理

  const hasTitle = computed(() => {
    return uiconfig && uiconfig.meta.title && uiconfig.meta.title.trim().length > 0
  })

  return {
    selectedUIItemId,
    selectedUIItem,
    hoverUIItemId,
    ui,
    uiVersion,
    focusUIItem,
    selectedPageId,
    cssMap,
    selectedUIItemIsInput,
    hasTitle,
    endKind,
    previewStyleItem,
    hasInheritStyle,
    hasSetStyle,
    computedWrap,
    getMeta,
    setMeta
  }
}
