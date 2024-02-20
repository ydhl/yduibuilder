export declare type UIState = 'normal' | 'readonly' | 'disabled' | 'hidden'
export declare type UIActionType = 'popup' | 'call'
export declare type PageType = 'popup' | 'subpage' | 'page' | 'master' | 'component'
export declare type UIKind = 'pc' | 'mobile'
export declare type DataType = 'string' | 'integer' | 'number' | 'array' | 'boolean' | 'object' | 'null' | 'any' // 数据结构那类型
export declare type UIType = 'Breadcrumb' | 'Button'
  | 'Card' | 'Carousel' | 'Checkbox' | 'Collapse' | 'Container'
  | 'Dropdown'
  | 'File'
  | 'Hr' | 'Holder'
  | 'Icon' | 'List' | 'Image' | 'Input'
  | 'Modal'
  | 'Nav'
  | 'Page' | 'Pagination' | 'Progress'
  | 'Radio' | 'RangeInput' | 'RichText'
  | 'Select'
  | 'Table' | 'Text' | 'Textarea'
  | 'Unknown' | 'UIComponent' // uicomponent是ui组件示例的容器
export declare type InputType = 'Text' | 'Number' | 'Email' | 'URL' | 'Password' | 'Color'
export declare type ArgType = 'Number' | 'String' | 'Array' | 'Object' | 'Map'
export declare type Placement = 'Center' | 'Left' | 'Right' | 'Top' | 'Bottom' | 'TopLeft' | 'TopRight' | 'BottomLeft' | 'BottomRight'
export declare type OutputASItem = 'HTML' | 'TEXT' | 'VALUE' | 'NAME' | 'STYLE' | 'CSS'
export interface KeyValue{
  name: string,
  value?: string
}
/**
 * api 目录结构
 */
export interface APIFolder{
  name: string;
  method?: string;
  isApi?: boolean;
  status?: string;
  children?: Array<APIFolder>;
}
/**
 * 使用的文件信息
 */
export interface FileInfo{
  /**
   * 后端文件数据的uud
   */
  id: string;
  /**
   * 文件名
   */
  name: string;
}
export interface UIMetaForm{
  /**
   * 数据提交表单名
   */
  inputName?: string;
  /**
   * 提示语
   **/
  placeholder?: string;
  /**
   * 组件的状态 normal readonly disabled hidden
   */
  state?: UIState;
}
/**
 * 元数据设置
 */
export interface UIMetaValue{
  text: string;
  value: string;
  checked: boolean;
  /**
   * 是否禁用
   */
  disabled?: boolean;
}
/**
 * 元数据设置
 */
export interface UIMeta{
  /**
   * ui ID
   */
  id: string;
  /**
   * UI标题
   */
  title: string;
  /**
   * 是否是容器元素
   */
  isContainer: boolean;
  /**
   * 表单类组件的属性
   */
  form?: UIMetaForm;
  /**
   * 多选值列表
   */
  values?: Array<UIMetaValue>;
  /**
   * 默认值
   */
  value?: string;
  /**
   * 组件关联的文件资源
   */
  files?: Array<FileInfo>;
  /**
   * 元素的样式JSON结构体，其内容可以转换成最终的Style字符串
   */
  style?: Record<string, any>;
  /**
   * 元素使用的selector中的样式JSON结构体，其内容可以转换成最终的Style字符串，格式同style
   */
  selector?: Record<string, any>;
  /**
   * 元素的css class 字符串, key是css设置对象,用来区别css主要是影响哪方面的样式，value是其包含的class，比如{padding: p-1}
   */
  css?: Record<string, any>;
  /**
   * UI描述
   */
  desc?: string;
  /**
   * 自定义属性内容
   */
  custom?: Record<string, any>;
  /**
   * 是否被锁住，被锁住的不能删除、编辑器不能选择、不能修改所有属性；isLock可以解锁
   */
  isLock?: boolean;
  /**
   * 是否只读，只读不能删除、编辑器不能选择、不能修改所有属性；isReadonly通常是强制约束，通常用于控制子元素，比如自定义组件，幻灯片内的组件等
   */
  isReadonly?: boolean;
}

/**
 * 数据定义结构体
 */
export interface DataStruct{
  uuid: string;
  /**
   * 是否是根结点
   */
  isRoot?: boolean;
  /**
   * 结点类型
   */
  type: DataType;
  /**
   * 结点名称，根结点为空
   */
  name?: string;
  /**
   * 标题，面向开发者的，name指的是变量名称
   */
  title?: string;
  /**
   * 说明
   */
  comment?:string;
  /**
   * 是否废弃
   */
  deprecated?: boolean;
  /**
   * 允许为空
   */
  nullable?: boolean;
  /**
   * 是否必须
   */
  required?: boolean;
  action?: string;
}
export interface DataStructString extends DataStruct{
  min?: number;
  max?: number;
  pattern?: string;
  defaultValue?: string;
  constValue?: string;
  /**
   * 枚举值及说明
   */
  enumValue?: Record<string, string>;
}
export interface DataStructInteger extends DataStruct{
  min?: number;
  max?: number;
  format?:string;
  defaultValue?: number;
  constValue?: string;
  enumValue?: Record<string, string>;
}
export interface DataStructNumber extends DataStruct{
  min?: number;
  max?: number;
  numberFormat?:string;
  defaultValue?: number;
  constValue?: string;
  enumValue?: Record<string, string>;
}
export interface DataStructBoolean extends DataStruct{
  defaultValue?: boolean;
}
export interface DataStructArray extends DataStruct{
  min?: number;
  max?: number;
  unique?: boolean;
  /**
   * 只有一个元素，表示array中的item都是指定的dataStruct类型
   */
  item?: DataStruct;
}
export interface DataStructObject extends DataStruct{
  min?: number;
  max?: number;
  /**
   * 有多个，表示object里面的组成内容
   */
  props?: Array<DataStruct>;
}
export interface DataStructParam extends DataStruct{
  /**
   * 样例
   */
  sample?: string | Array<string> | Array<KeyValue>;
}
/**
 * 具体行为设置的基类
 */
export interface UIAction{
  /**
   * 某次事件绑定的唯一id
   */
  uuid: string;
  /**
   * 行为类型，见UIActionType
   */
  type: UIActionType;

  /**
   * 输入参数
   */
  input?: DataStruct;
  /**
   * 输出参数
   */
  output?: DataStruct;
}

/**
 * 打开对话框行为绑定，比如Toast，Alert，Confirm，自定义对话框等等
 */
export interface UIActionPopup extends UIAction{
  /**
   * dialog 也是一个page（可能是一个针对对话框设计的特殊page，也可能是一个已有的page），
   * 这里设置该page等id，以便编译时知道关联哪个page
   */
  popupPageId: string;
  // eslint-disable-next-line camelcase
  popup_type?: string;
  popupPageTitle?: string;
}

/**
 * 重定向行为
 */
export interface UIActionRedirect extends UIAction{
  /**
   * 重定向地址
   */
  redirect: string;
  /**
   * inside， outside
   */
  // eslint-disable-next-line camelcase
  redirect_type?: string;
  /**
   * page，url；如果项目支持重定向的是url，否则是page
   */
  // eslint-disable-next-line camelcase
  url_type?: string;
  /**
   * 重定向时，如果url type为page，那么popupPageId指定重定向的目标页面id
   */
  popupPageId: string;
  popupPageTitle?: string;
}

/**
 * 接口调用行为，比如调用其他组件暴露的接口等
 */
export interface UIActionCall extends UIAction{

}

/**
 * WEB接口调用行为
 */
export interface UIActionWebAPI extends UIAction{
  name?: string;
  method?: string;
  path?: string;
  /**
   * 中间表page_bind_api uuid
   */
  bindApiUuid?: string;
  /**
   * 绑定的api uuid
   */
  apiUuid?: string;
}

export interface UIBase{
  type: UIType;
  /**
   * 页面类型，当ui作为最顶层元素时，用于表示该ui用作什么类型，
   * page 就是常规的页面，popup表示该页面是弹窗， master 母版页，subpage 子页，component 自定义的ui组件
   */
  pageType?: PageType;
  /**
   * 某些元素的子元素可以包含一个子页，子页单独设计，这里通过subPageId进行引用;
   * 这时mate、items，events，pageType，type从指向的page加载, 并以subpage中的为主;
   * 即该uibase的内容会用subPageId指向的页面进行覆盖。
   */
  subPageId?: string;
  /**
   * subPageId所指向的页面是否已经被删除了
   */
  subPageDeleted?: boolean;
  /**
   * 元属性设置
   */
  meta: UIMeta;
   /**
   * 某些组件，可以有多个区域放置子组件，那么通过placeInParent来指定自己放置在父ui的那个位置
   */
  placeInParent?: string;
  /**
   * 主体包含的子组件
   */
  items?: Array<UIBase>;
  /**
   * 事件ID
   */
  events?: Array</* binding唯一id */string>;
}
export type UIPage = UIBase
