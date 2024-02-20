import { OutputASItem, UIKind, UIType } from '@/store/model'

export interface UIDefine{
  type: UIType,
  isContainer?: boolean,
  kind: Array<UIKind>,
  subItemType?: Array<UIType>,
  /**
   * 是否是表单项
   */
  isInput?: boolean,
  /**
   * i18n的字符串
   */
  name: string,
  /**
   * ui可以绑定输出的内容有那些
   */
  outputAs: Array<OutputASItem>
}
/**
 * 定义支持的UI组件类型，所有前端效果库都需要实现这些类型
 * 这里的类型定义是基于store/model UIMeta进行
 */
const baseUIDefines: Record<UIType, UIDefine> = {
  Breadcrumb: {
    type: 'Breadcrumb',
    kind: ['mobile', 'pc'],
    outputAs: ['NAME', 'VALUE', 'STYLE', 'CSS'],
    name: 'ui.breadcrumb'
  },
  Button: {
    type: 'Button',
    kind: ['mobile', 'pc'],
    name: 'ui.button',
    outputAs: ['TEXT', 'HTML', 'STYLE', 'CSS']
  },
  Card: {
    type: 'Card',
    kind: ['mobile', 'pc'],
    isContainer: true,
    name: 'ui.card',
    outputAs: ['TEXT', 'HTML', 'STYLE', 'CSS']
  },
  Carousel: {
    type: 'Carousel',
    kind: ['mobile', 'pc'],
    isContainer: false,
    name: 'ui.carousel',
    outputAs: ['TEXT', 'HTML', 'STYLE', 'CSS']
  },
  Checkbox: {
    type: 'Checkbox',
    kind: ['mobile', 'pc'],
    isInput: true,
    name: 'ui.checkbox',
    outputAs: ['VALUE', 'NAME', 'STYLE', 'CSS']
  },
  Collapse: {
    type: 'Collapse',
    kind: ['mobile', 'pc'],
    isContainer: true,
    name: 'ui.collapse',
    outputAs: ['TEXT', 'HTML', 'STYLE', 'CSS']
  },
  Container: {
    type: 'Container',
    isContainer: true,
    kind: ['mobile', 'pc'],
    name: 'ui.container',
    outputAs: ['TEXT', 'HTML', 'STYLE', 'CSS']
  },
  Dropdown: {
    type: 'Dropdown',
    kind: ['pc'],
    name: 'ui.dropdown',
    outputAs: ['VALUE', 'NAME', 'STYLE', 'CSS']
  },
  File: {
    type: 'File',
    kind: ['mobile', 'pc'],
    isInput: true,
    name: 'ui.file',
    outputAs: ['NAME', 'STYLE', 'CSS']
  },
  Hr: {
    type: 'Hr',
    kind: ['mobile', 'pc'],
    name: 'ui.hr',
    outputAs: ['TEXT', 'HTML', 'STYLE', 'CSS']
  },
  Holder: {
    type: 'Holder',
    kind: ['mobile', 'pc'],
    name: 'ui.holder',
    outputAs: ['STYLE', 'CSS']
  },
  Icon: {
    type: 'Icon',
    kind: ['mobile', 'pc'],
    name: 'ui.icon',
    outputAs: ['VALUE', 'STYLE', 'CSS']
  },
  Image: {
    type: 'Image',
    kind: ['mobile', 'pc'],
    name: 'ui.image',
    outputAs: ['VALUE', 'STYLE', 'CSS']
  },
  Input: {
    type: 'Input',
    kind: ['mobile', 'pc'],
    isInput: true,
    name: 'ui.input',
    outputAs: ['VALUE', 'STYLE', 'CSS']
  },
  List: {
    type: 'List',
    kind: ['mobile', 'pc'],
    name: 'ui.list',
    outputAs: ['VALUE', 'NAME', 'STYLE', 'CSS']
  },
  Modal: {
    type: 'Modal',
    kind: ['pc'],
    isContainer: true,
    name: 'ui.modal',
    outputAs: ['TEXT', 'HTML', 'STYLE', 'CSS']
  },
  Nav: {
    type: 'Nav',
    isContainer: true,
    kind: ['pc', 'mobile'],
    name: 'ui.nav',
    outputAs: ['VALUE', 'NAME', 'STYLE', 'CSS']
  },
  Page: {
    type: 'Page',
    kind: ['mobile', 'pc'],
    isContainer: true,
    name: 'ui.page',
    outputAs: ['TEXT', 'HTML', 'STYLE', 'CSS']
  },
  Pagination: {
    type: 'Pagination',
    kind: ['pc'],
    name: 'ui.pagination',
    outputAs: ['VALUE', 'NAME', 'STYLE', 'CSS']
  },
  Progress: {
    type: 'Progress',
    kind: ['mobile', 'pc'],
    name: 'ui.progress',
    outputAs: ['VALUE', 'NAME', 'STYLE', 'CSS']
  },
  Radio: {
    type: 'Radio',
    kind: ['mobile', 'pc'],
    isInput: true,
    name: 'ui.radio',
    outputAs: ['VALUE', 'STYLE', 'CSS']
  },
  RangeInput: {
    type: 'RangeInput',
    kind: ['mobile', 'pc'],
    isInput: true,
    name: 'ui.rangeInput',
    outputAs: ['VALUE', 'STYLE', 'CSS']
  },
  RichText: {
    type: 'RichText',
    kind: ['mobile', 'pc'],
    isInput: false,
    name: 'ui.richText',
    outputAs: ['VALUE', 'STYLE', 'CSS']
  },
  Select: {
    type: 'Select',
    kind: ['mobile', 'pc'],
    isInput: true,
    name: 'ui.select',
    outputAs: ['NAME', 'VALUE', 'STYLE', 'CSS']
  },
  Table: {
    type: 'Table',
    kind: ['mobile', 'pc'],
    name: 'ui.table',
    outputAs: ['STYLE', 'CSS']
  },
  Text: {
    type: 'Text',
    kind: ['mobile', 'pc'],
    name: 'ui.text',
    outputAs: ['TEXT', 'HTML', 'STYLE', 'CSS']
  },
  Textarea: {
    type: 'Textarea',
    kind: ['mobile', 'pc'],
    isInput: true,
    name: 'ui.textarea',
    outputAs: ['VALUE', 'STYLE', 'CSS']
  },
  UIComponent: {
    type: 'UIComponent',
    isContainer: false,
    kind: ['mobile', 'pc'],
    name: 'ui.uicomponent',
    outputAs: []
  },
  Unknown: {
    type: 'Container',
    isContainer: true,
    kind: ['mobile', 'pc'],
    name: 'ui.container',
    outputAs: ['TEXT', 'HTML', 'STYLE', 'CSS']
  }
}

export default baseUIDefines

/**
 * UI变量输出的组件格式定义
 */
const uiDefines: Record<string, Array<UIType>> = {
  common: [
    'Container', 'Text', 'Button', 'Image'
  ],
  base: [
    'Breadcrumb',
    'Progress', 'Dropdown', 'List', 'Nav', 'Hr', 'Icon'
  ],
  form: [
    'Input', 'Textarea', 'Radio', 'Checkbox', 'Select', 'File', 'RangeInput'
  ],
  advance: [
    'Card', 'Carousel', 'Collapse', 'Pagination', 'Table', 'RichText', 'Holder'
  ]
}

/**
 * 获取能使用的ui组件
 *
 * @param kind 加载的ui支持的类型，pc | mobile
 * @param excludeUI 加载除了这些外的其他ui
 * @param includeUI 只加载这些组件，指定了includeUI忽略excludeUI
 */
export function getUIDefines (kind, excludeUI: Array<UIType> = [], includeUI: Array<UIType> = []) {
  // console.log(kind)
  const _: any = {}
  for (const type in uiDefines) {
    _[type] = []
    for (const item of uiDefines[type]) {
      if (baseUIDefines[item].kind.indexOf(kind) === -1) continue
      if (includeUI.length && includeUI.indexOf(item) === -1) continue
      if (!includeUI.length && excludeUI.length && excludeUI.indexOf(item) !== -1) continue
      _[type].push(baseUIDefines[item])
    }
  }
  return _
}
