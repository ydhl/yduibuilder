import { useI18n } from 'vue-i18n'
import { computed, nextTick, onMounted, watch } from 'vue'
import ydhl from '@/lib/ydhl'
declare const ports: any

export default class UIBase {
  protected props: any
  protected context: any
  protected store: any

  constructor (props: any, context: any, store) {
    this.props = props
    this.context = context
    this.store = store
  }

  /**
   * 对style 的json object的每个值后面增加!important
   * @param object
   */
  public appendImportant (object: any) {
    const arr: any = []
    for (const objectKey in object) {
      if (objectKey.match(/^\[.+\]$/)) {
        arr.push(object[objectKey])
      } else {
        arr.push(`${objectKey}:${object[objectKey]} !important`)
      }
    }
    return arr.join(';')
  }

  /**
   * 当前选中的item是否激活了非普通状态
   */
  public hasActiveState () {
    const store = this.store
    if (!store) return false
    return store.state.page?.selectedUIItemActiveState &&
      store.state.page.selectedUIItemActiveState !== 'normal' &&
      store.state.page.selectedUIItemId === this.props.uiconfig?.meta?.id
  }

  /**
   * 从selector中获取style
   *
   * @param selector
   * @return Record<string, string>
   */
  private getStyle (style: Record<string, any>) {
    // 每个角单独设置的优先级最高
    if (style?.['border-radius'] !== undefined) {
      if (!style['border-top-left-radius']) style['border-top-left-radius'] = style?.['border-radius']
      if (!style['border-top-right-radius']) style['border-top-right-radius'] = style?.['border-radius']
      if (!style['border-bottom-left-radius']) style['border-bottom-left-radius'] = style?.['border-radius']
      if (!style['border-bottom-right-radius']) style['border-bottom-right-radius'] = style?.['border-radius']
      delete style?.['border-radius']
    }

    if (style['background-image'] && style['background-image'].length > 0) {
      const images: any = []
      const repeat: any = []
      const clip: any = []
      const origin: any = []
      const attachment: any = []
      const position: any = []
      const size: any = []
      for (let index = 0; index < style['background-image'].length; index++) {
        const background = style['background-image'][index]
        if (!background.url && !background.gradient) {
          continue
        }
        if (background.type === 'image') {
          if (!background.url) continue
          images.push(`url(${background.url})`)
        } else if (background.type === 'gradient') {
          if (!background.gradient) continue
          images.push(ydhl.getGradientStyle(background.gradient))
        }
        if (style?.['background-repeat']?.[index]) repeat.push(style['background-repeat'][index])
        if (style?.['background-clip']?.[index]) clip.push(style['background-clip'][index])
        if (style?.['background-origin']?.[index]) origin.push(style['background-origin'][index])
        if (style?.['background-attachment']?.[index]) attachment.push(style['background-attachment'][index])
        if (style?.['background-position']?.[index]) position.push(style['background-position'][index])
        if (style?.['background-size']?.[index]) size.push(style['background-size'][index])
      }
      style['background-image'] = images.join(',')
      style['background-repeat'] = repeat.join(',')
      style['background-clip'] = clip.join(',')
      style['-webkit-background-clip'] = clip.join(',') // clip 为 text 需要加上-webkit
      style['background-origin'] = origin.join(',')
      style['background-attachment'] = attachment.join(',')
      style['background-position'] = position.join(',')
      style['background-size'] = size.join(',')
    }
    if (style?.['font-family'] !== undefined) {
      style['font-family'] = `"${style['font-family'].uuid}"`
    }
    // console.log(style)
    return style
  }

  /**
   * 公共字体属性
   * @param custom
   * @param style
   * @private
   */
  private getCustomStyle (custom: any, style: any) {
    const decoration: any = []
    if (custom?.underline) {
      decoration.push('underline')
    }
    if (custom?.through) {
      decoration.push('line-through')
    }
    if (decoration.length > 0) {
      style['text-decoration'] = decoration.join(' ')
    }
    if (custom?.align) {
      style['text-align'] = custom.align
    }
    if (custom?.italic) {
      style['font-style'] = 'italic'
    }
    if (custom?.bold) {
      style['font-weight'] = custom.bold.toLowerCase()
    }
    if (style['text-stroke']) {
      style['-webkit-text-stroke'] = style['text-stroke']
    }
    return style
  }

  /**
   * 获取ui元素的style集合，其中的key是style name，value是其样式字符串。
   * 先取selector中的内容，在取自己的内容
   * @param uiconfig UIBase 如果没有传入，则用props.uiconfig或previewStyleItem
   * @return Record<string, string>
   */
  public getUIStyle (uiconfig: any = undefined) {
    const store = this.store
    if (!uiconfig) {
      uiconfig = this.props.uiconfig
    }
    const state = this.hasActiveState() ? store.state.page.previewStyleItem : null
    if (!uiconfig?.meta && !state) return {}
    // console.log('getUIStyle')
    const selector = uiconfig?.meta?.selector?.style ? this.getStyle(JSON.parse(JSON.stringify(uiconfig.meta.selector?.style))) : {}
    let style = uiconfig?.meta?.style ? this.getStyle(JSON.parse(JSON.stringify(uiconfig.meta.style))) : {}
    let stateStyle = state?.meta?.style ? this.getStyle(JSON.parse(JSON.stringify(state.meta.style))) : {}

    // 公共字体属性
    const custom = uiconfig.meta.selector?.custom ? ydhl.deepMerge(uiconfig.meta.selector.custom, uiconfig.meta?.custom) : uiconfig.meta?.custom
    style = this.getCustomStyle(custom, style)
    if (state?.meta?.custom) {
      stateStyle = this.getCustomStyle(state.meta.custom, stateStyle)
    }
    return ydhl.deepMerge(selector, style, stateStyle)
  }

  private fetchCss (cssInfo: any, _css: Object) {
    const store = this.store
    if (cssInfo) {
      for (const cssKey in cssInfo) {
        if (!cssInfo[cssKey]) continue
        let css = store.getters.translate(cssKey, cssInfo[cssKey])// 翻译成对应的ui库 css
        if (css === undefined) { // leeboo 如果不是预定义的（没有翻译结果），则直接用他，常见于某些地方自己硬性定义的css，比如modal的move-handler
          css = cssInfo[cssKey]
        }
        if (_css[cssKey]) {
          _css[cssKey] += ` ${css}`
        } else {
          _css[cssKey] = css
        }
      }
    }
  }

  /**
   * 根据uiconfig.meta.css的内容生成class，返回Record<string, string> key是css名称，value是对应ui中的样式名，如backgroudTheme: bg-primary
   *
   * @param uiconfig UIBase 如果没有传入，则用props.uiconfig或previewStyleItem
   * @return Record<string, string>
   */
  public getUICss (uiconfig: any = undefined) {
    const store = this.store
    if (!uiconfig) {
      uiconfig = this.props.uiconfig
    }
    const _css: any = {}
    const state = this.hasActiveState() ? store.state.page.previewStyleItem : null

    if (!uiconfig.meta && !state) return {}

    this.fetchCss(uiconfig?.meta?.selector?.css, _css)
    this.fetchCss(uiconfig?.meta?.css, _css)
    this.fetchCss(state?.meta?.css, _css)
    return _css
  }

  /**
   * UI获取指定的meta值，找不到返回undefined
   * @param name
   * @param complexTypeName
   */
  public getMeta (name, complexTypeName: string = '') {
    const store = this.store
    if (this.hasActiveState()) {
      const state = store.state.page.previewStyleItem
      if (!state) return undefined
      if (complexTypeName && !state.meta[complexTypeName]) return undefined
      return complexTypeName ? state.meta[complexTypeName][name] : state.meta[name]
    }
    if (!this.props.uiconfig) return undefined
    if (complexTypeName && !this.props.uiconfig.meta[complexTypeName]) return undefined
    return complexTypeName ? this.props.uiconfig.meta[complexTypeName][name] : this.props.uiconfig.meta[name]
  }

  /**
   * ui继承的数据获取和处理
   * @return { t, dragoverUIItemId, dragoverPlacement, dragoverInParent, inlineEditItemId, parentUi, hasItems }
   */
  public setup () {
    const props = this.props
    // const context = this.context
    const { t } = useI18n()
    const store = this.store
    const isContainer = computed(() => props.uiconfig.meta.isContainer)
    const selectedUIItemId = computed(() => store.state.page.selectedUIItemId)
    const highlightUIItemIds = computed(() => store.state.page.highlightUIItemIds)
    const hoverUIItemId = computed(() => store.state.page.hoverUIItemId)
    const dragoverUIItemId = computed(() => store.state.page.dragoverUIItemId)
    const dragoverPlacement = computed(() => store.state.page.dragoverPlacement)
    const dragoverInParent = computed(() => store.state.page.dragoverInParent)
    const updateInlineItemValue = computed(() => store.state.page.updateInlineItemValue)
    const ui = computed(() => store.state.page.project.ui)
    const uiVersion = computed(() => store.state.page.project.ui_version)
    const endKind = computed<string>(() => store.state.page.endKind)
    const myIsLock = computed(() => props.isLock || props.uiconfig.meta?.isLock)
    const myIsReadonly = computed(() => props.isReadonly || props.uiconfig.meta?.isReadonly)

    const hasItems = computed(() => props?.uiconfig?.items?.length > 0)
    const hasTitle = computed(() => {
      return props?.uiconfig?.meta?.title?.trim().length > 0
    })

    const inlineEditItemId = computed({
      get () {
        return store.state.page.inlineEditItemId
      },
      set (v) {
        ports.parent({ type: 'update', data: { inlineEditItemId: v } })
      }
    })

    const parentUi = computed(() => {
      const { parentConfig } = store.getters.getUIItemInPage(props.uiconfig.meta.id, props.pageid)
      return parentConfig
    })
    // 非编辑状态的子元素可以拖动
    const draggable = computed<boolean | undefined>(() => {
      if (myIsReadonly.value) return undefined
      if (myIsLock.value) return false
      return !inlineEditItemId.value && parentUi.value !== null
    })

    const isDragIn = computed((ctx: any) => {
      if (dragoverPlacement.value !== 'in') return false
      return dragoverUIItemId.value === props.uiconfig.meta.id
    })
    const myId = computed((ctx: any) => {
      if (myIsReadonly.value) return undefined
      return props.uiconfig.meta.id
    })

    /**
     * 如果改变了选中元素，并且当前有元素处于内部编辑状态，则取消内部编辑
     */
    watch(selectedUIItemId, (newV, oldV) => {
      if (newV !== oldV && newV !== inlineEditItemId.value && inlineEditItemId.value) {
        console.log('watch selectedUIItemId in UIBase.ts')
        ports.parent({ type: 'update', data: { inlineEditItemId: '' } })
      }
    })
    const uiStyle = computed(() => {
      const style = this.getUIStyle()
      // if (this.props.uiconfig.type === 'Container') {
      //   console.log(this.getUIStyle())
      // }

      // console.log(style)
      return this.appendImportant(style)
    })

    const uiCss = computed(() => {
      const css = this.getUICss()
      // console.log(css)
      // if (this.props.uiconfig.type === 'Card') {
      //   console.log(this.props.uiconfig, css)
      // }
      return Object.values(css).join(' ')
    })
    onMounted(() => {
      nextTick(() => {
        store.commit('loaded', { id: this.props?.uiconfig?.meta?.id })
      })
    })
    return {
      t,
      ui,
      myId,
      uiVersion,
      isContainer,
      dragoverUIItemId,
      dragoverPlacement,
      dragoverInParent,
      inlineEditItemId,
      selectedUIItemId,
      highlightUIItemIds,
      hoverUIItemId,
      updateInlineItemValue,
      parentUi,
      hasItems,
      isDragIn,
      hasTitle,
      uiStyle,
      endKind,
      uiCss,
      draggable,
      myIsLock,
      myIsReadonly
    }
  }
}
