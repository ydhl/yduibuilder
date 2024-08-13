import { OutputASItem, UIKind, UIType } from '@/store/model'

export interface UIDefine{
  type: UIType,
  isContainer?: boolean,
  kind: Array<UIKind>,
  subItemType?: Array<UIType>,
  /**
   * 是否是迭代类UI、迭代类ui指内部有需要迭代输出的元素，比如select list
   */
  isIterable?: boolean,
  /**
   * 是否是表单项
   */
  isForm?: boolean,
  /**
   * 是否是有值类ui
   */
  isValuable?: boolean,
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
    isIterable: true,
    isValuable: true,
    outputAs: ['VALUELIST', 'STYLE', 'CSS', 'TITLE', 'KEYVALUE'],
    name: 'ui.breadcrumb'
  },
  Button: {
    type: 'Button',
    kind: ['mobile', 'pc'],
    name: 'ui.button',
    outputAs: ['TEXT', 'HTML', 'STYLE', 'CSS', 'TITLE', 'KEYVALUE', 'NONE']
  },
  Card: {
    type: 'Card',
    kind: ['mobile', 'pc'],
    isContainer: true,
    name: 'ui.card',
    outputAs: ['STYLE', 'CSS', 'KEYVALUE', 'NONE']
  },
  Carousel: {
    type: 'Carousel',
    kind: ['mobile', 'pc'],
    isContainer: false,
    name: 'ui.carousel',
    isIterable: true,
    outputAs: ['VALUELIST', 'STYLE', 'CSS', 'KEYVALUE']
  },
  Checkbox: {
    type: 'Checkbox',
    kind: ['mobile', 'pc'],
    isIterable: true,
    isForm: true,
    isValuable: true,
    name: 'ui.checkbox',
    outputAs: ['VALUELIST', 'STYLE', 'CSS', 'TITLE', 'KEYVALUE']
  },
  Collapse: {
    type: 'Collapse',
    kind: ['pc'],
    isIterable: true,
    isContainer: true,
    name: 'ui.collapse',
    outputAs: ['VALUELIST', 'STYLE', 'CSS', 'KEYVALUE']
  },
  Container: {
    type: 'Container',
    isContainer: true,
    kind: ['mobile', 'pc'],
    name: 'ui.container',
    outputAs: ['VALUE', 'TEXT', 'HTML', 'STYLE', 'CSS', 'KEYVALUE', 'NONE']
  },
  Dropdown: {
    type: 'Dropdown',
    kind: ['pc', 'mobile'],
    name: 'ui.dropdown',
    isIterable: true,
    isValuable: true,
    outputAs: ['VALUELIST', 'STYLE', 'CSS', 'TITLE', 'KEYVALUE']
  },
  File: {
    type: 'File',
    kind: ['mobile', 'pc'],
    isForm: true,
    isValuable: true,
    name: 'ui.file',
    outputAs: ['STYLE', 'CSS', 'TITLE', 'KEYVALUE', 'NONE']
  },
  Hr: {
    type: 'Hr',
    kind: ['mobile', 'pc'],
    name: 'ui.hr',
    outputAs: ['TEXT', 'HTML', 'STYLE', 'CSS', 'TITLE', 'KEYVALUE', 'NONE']
  },
  Holder: {
    type: 'Holder',
    kind: ['mobile', 'pc'],
    name: 'ui.holder',
    outputAs: []
  },
  Icon: {
    type: 'Icon',
    kind: ['mobile', 'pc'],
    name: 'ui.icon',
    outputAs: ['VALUE', 'STYLE', 'CSS', 'TITLE', 'KEYVALUE', 'NONE']
  },
  Image: {
    type: 'Image',
    kind: ['mobile', 'pc'],
    name: 'ui.image',
    outputAs: ['VALUE', 'STYLE', 'CSS', 'ALT', 'TITLE', 'KEYVALUE', 'NONE']
  },
  Input: {
    type: 'Input',
    kind: ['mobile', 'pc'],
    isForm: true,
    isValuable: true,
    name: 'ui.input',
    outputAs: ['VALUE', 'STYLE', 'CSS', 'TITLE', 'KEYVALUE', 'NONE']
  },
  List: {
    type: 'List',
    kind: ['mobile', 'pc'],
    name: 'ui.list',
    isIterable: true,
    isValuable: true,
    outputAs: ['VALUELIST', 'STYLE', 'CSS', 'TITLE', 'KEYVALUE']
  },
  Modal: {
    type: 'Modal',
    kind: ['pc'],
    isContainer: true,
    name: 'ui.modal',
    outputAs: ['STYLE', 'CSS', 'KEYVALUE', 'NONE']
  },
  Nav: {
    type: 'Nav',
    isContainer: true,
    kind: ['pc', 'mobile'],
    name: 'ui.nav',
    isIterable: true,
    isValuable: true,
    outputAs: ['VALUELIST', 'STYLE', 'CSS', 'KEYVALUE']
  },
  Page: {
    type: 'Page',
    kind: ['mobile', 'pc'],
    isContainer: true,
    name: 'ui.page',
    outputAs: ['TEXT', 'HTML', 'STYLE', 'CSS', 'KEYVALUE', 'NONE']
  },
  Pagination: {
    type: 'Pagination',
    kind: ['pc'],
    name: 'ui.pagination',
    isValuable: true,
    outputAs: ['VALUE', 'STYLE', 'CSS', 'KEYVALUE']
  },
  Progress: {
    type: 'Progress',
    kind: ['mobile', 'pc'],
    name: 'ui.progress',
    outputAs: ['VALUE', 'TEXT', 'STYLE', 'CSS', 'TITLE', 'KEYVALUE', 'NONE']
  },
  Radio: {
    type: 'Radio',
    kind: ['mobile', 'pc'],
    isForm: true,
    isIterable: true,
    isValuable: true,
    name: 'ui.radio',
    outputAs: ['VALUELIST', 'STYLE', 'CSS', 'TITLE', 'KEYVALUE']
  },
  RangeInput: {
    type: 'RangeInput',
    kind: ['mobile', 'pc'],
    isForm: true,
    isValuable: true,
    name: 'ui.rangeInput',
    outputAs: ['VALUE', 'STYLE', 'CSS', 'TITLE', 'KEYVALUE', 'NONE']
  },
  RichText: {
    type: 'RichText',
    kind: ['mobile', 'pc'],
    name: 'ui.richText',
    outputAs: ['VALUE', 'STYLE', 'CSS', 'KEYVALUE', 'NONE']
  },
  Select: {
    type: 'Select',
    kind: ['mobile', 'pc'],
    isForm: true,
    isValuable: true,
    name: 'ui.select',
    isIterable: true,
    outputAs: ['VALUELIST', 'STYLE', 'CSS', 'TITLE', 'KEYVALUE']
  },
  Table: {
    type: 'Table',
    kind: ['pc'],
    name: 'ui.table',
    isIterable: true,
    isValuable: true,
    outputAs: ['VALUELIST', 'STYLE', 'CSS', 'KEYVALUE']
  },
  Text: {
    type: 'Text',
    kind: ['mobile', 'pc'],
    name: 'ui.text',
    outputAs: ['TEXT', 'HTML', 'STYLE', 'CSS', 'TITLE', 'KEYVALUE', 'NONE']
  },
  Textarea: {
    type: 'Textarea',
    kind: ['mobile', 'pc'],
    isForm: true,
    isValuable: true,
    name: 'ui.textarea',
    outputAs: ['VALUE', 'STYLE', 'CSS', 'TITLE', 'KEYVALUE', 'NONE']
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
    outputAs: ['TEXT', 'HTML', 'STYLE', 'CSS', 'TITLE', 'KEYVALUE', 'NONE']
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
