export interface ButtonAndCallback{
  label: string;
  cb: CallableFunction;
}
export interface YDJSStatic {
  /**
   * 使用的前端库
   */
  UI_FRAMEWORK_NAME: string,

  /**
   * 成功
   */
  ICON_SUCCESS: string,
  /**
   * 失败
   */
  ICON_ERROR: string,
  /**
   * 一般信息
   */
  ICON_INFO: string,
  /**
   * 警告
   */
  ICON_WARN: string,
  /**
   * 询问
   */
  ICON_QUESTION: string,
  /**
   * 或者不定义，就表示没有图标
   */
  ICON_NONE: string,

  POSITION_TOP: string,
  POSITION_LEFT: string,
  POSITION_BOTTOM: string,
  POSITION_RIGHT: string,
  POSITION_CENTER: string,
  POSITION_LEFT_TOP: string,
  POSITION_LEFT_BOTTOM: string,
  POSITION_RIGHT_TOP: string,
  POSITION_RIGHT_BOTTOM: string,

  SIZE_FULL: string,
  SIZE_LARGE: string,
  SIZE_NORMAL: string,
  SIZE_SMALL: string,

  BACKDROP_NONE: string,
  BACKDROP_NORMAL: string,
  BACKDROP_STATIC: string,

  /**
   * 恢复yd-spin-btn的效果
   *
   * @param {type} selector
   * @returns {undefined}
   */
  // eslint-disable-next-line camelcase
  spin_clear(selector): void;

  /**
   * 隐藏弹出的对话框,参数是toast, alert, dialog等返回的内容
   *
   * @param {type} loadingId
   * @returns {undefined}
   */
  // eslint-disable-next-line camelcase
  hide_dialog (dialogId: string):void;
  // eslint-disable-next-line camelcase
  update_loading (dialogId: string, content: string);

  /**
   *
   * @param {type} msg
   * @param {type} loadingId 可选参数
   * @param {type} overlay dom元素或者selector选择器，如果指定loading则覆盖在该元素上面
   * @returns {undefined} 对话框id
   */
  loading (msg: string, loadingId?: string, overlay?: string|Element|undefined);

  /**
   *
   * @param {type} msg 消息
   * @param {type} icon 图标见YDJS.ICON_XX定义 默认YDJS.ICON_INFO
   * @param {type} cb toast关闭后的回调,默认没有
   * @param {type} backdrop none 没有背景, normal 有背景 默认none
   * @param {type} delay 显示的毫秒 默认3000 -1表示不会消失
   * @param {type} position 位置 见YDJS.POSITION_XX定义 默认POSITION_CENTER
   * @returns {undefined} 对话框id
   */
  toast (msg: string, icon: string, cb?: string|CallableFunction|undefined, backdrop?: string|undefined, delay?: number|undefined, position?: string|undefined):void;

  /**
   *
   * @param {type} url 地址,如果有地址,则忽略content,title
   * @param {type} content 对话框的内容
   * @param {type} title 对话框的标题
   * @param {type} size 见YDJS.SIZE_XXX 默认normal
   * @param {type} backdrop 见YDJS.BACKDROP
   * @param {type} buttonAndCallbacks 按钮及其回调的数组 [{label:"按钮名称","cb":回调函数名或者匿名函数}] 回调参数是dialog_id
   * @param {type} dialogId 对话框id,可不传
   * @param {type} submittedCallback 加载的表单提交回调，传入表单提交后接口返回的值
   * @returns {undefined} 对话框id
   */
  dialog (url: string, content: string, title: string, size?: string|undefined, backdrop?: string|undefined, buttonAndCallbacks?: Array<ButtonAndCallback> | undefined, dialogId?: string|undefined, submittedCallback?:string | CallableFunction): string;

  /**
   * 更新对话框的内容
   *
   * @param dialogId
   * @param content
   */
  // eslint-disable-next-line camelcase
  update_loading (dialogId, content):void;

  /**
   *
   * @param {type} content
   * @param {type} title
   * @param {type} icon 图标见YDJS.ICON_XX定义 默认YDJS.ICON_INFO
   * @param {type} buttonAndCallbacks buttonAndCallbacks 按钮及其回调的数组 [{label:"按钮名称","cb":回调函数名或者匿名函数}] 可选,默认是确定按钮 回调参数是dialog_id
   * @param {type} dialogId 可选
   * @returns {undefined}
   */
  alert (content: string, title: string, icon?: string, buttonAndCallbacks?: Array<ButtonAndCallback> | undefined, dialogId?: string | undefined):void;

  /**
   *
   * @param {type} content 确认对话框的内容
   * @param {type} title 确认对话框的标题
   * @param {type} yesCb 确认按钮的回调 参数是dialog_id
   * @param {type} noCb 取消按钮的回调 参数是dialog_id
   * @param {type} yesLabel 确认按钮名称 默认"确认"
   * @param {type} noLabel 取消按钮的名称 默认"取消"
   * @param {type} dialogId 对话框id
   * @returns {undefined} 对话框id
   */
  confirm (content: string, title: string, yesCb?: string | CallableFunction | undefined, noCb?: string | CallableFunction | undefined, yesLabel?: string | undefined, noLabel?: string | undefined, icon?: string | undefined, dialogId?: string | undefined):void;

  /**
   *
   * @param {type} title 标题
   * @param {type} defaultValue 默认值
   * @param {type} type input 输入框 password 密码框 textarea 文本框
   * @param {type} cb 确定后的回调,参数是dialog_id , value
   * @param {type} dialogId
   * @returns {undefined} 对话框id
   */
  prompt (title: string, defaultValue: string | undefined, type?: string | undefined, cb?: string | CallableFunction, dialogId?: string | undefined): void;
  /**
   * 重新绑定事件
   * @returns {undefined}
   */
  rebind(): void;
  urlencode (clearString: string): string;
  urldecode (encodedString: string): string;
  /**
   *
   * @param {type} len 长度
   * @param {type} radix 进制
   * @returns {String}
   */
  uuid (len: number, radix: number, prefix: string): string;
}
