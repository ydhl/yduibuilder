export default {
  api: {
    addFolder: '新建目录',
    comment: '备注',
    commitMessage: 'Commit 消息',
    commitMessageTip: 'Commit 消息',
    commitLog: 'Commit 记录',
    dataStruct: {
      addField: '新增字段',
      addFieldTip: '字段名',
      addNextField: '新增相邻字段',
      addSubField: '新增下级字段',
      advance: '高级',
      comment: '备注',
      const: '常量',
      dataStruct: '数据结构',
      defaultValue: '默认',
      deprecatedTip: '已废除',
      enum: '枚举',
      enumValue: '枚举值',
      field: '字段',
      format: '格式化',
      formatCode: '格式化代码',
      import: '导入',
      max: '最大',
      maxCount: '最大数量',
      maxLength: '最大长度',
      maxProp: '最大属性',
      mergeReplace: '替换',
      mergeReserve: '合并新内容',
      mergeType: '合并类型',
      min: '最小',
      minCount: '最小数量',
      minLength: '最小长度',
      minProp: '最小属性',
      mock: 'Mock',
      noSubField: '没有字段',
      unique: '元素唯一',
      nullableTip: '可为Null',
      pathParam: '路径参数',
      pattern: '正则表达式',
      patternTip: '用正则表达式约束字符串',
      queryParam: '查询参数',
      requiredTip: '必填项',
      rootNode: '根节点',
      rw: '读/写',
      sample: '样例',
      setting: '设置',
      title: '标题',
      type: '类型'
    },
    folder: '目录',
    folderName: '目录名',
    folderParent: '上级目录',
    label: '标签',
    labelAddTip: '创建或选择已有标签',
    major: '主版本',
    minor: '次版本',
    name: '名称',
    removeApiConfirm: '确认删除吗?',
    removeFolderConfirm: '确认删除吗?',
    responsible: '责任人',
    revision: '修订版本',
    request: {
      name: '请求',
      param: {
        add: '添加查询参数',
        addQueryStringInParam: '请在参数面部中添加查询参数',
        autoParseQueryTip: '路径中的查询参数已自动提取到参数中',
        desc: '备注',
        fixed: '固定值',
        fixedDesc: '无法被修改',
        import: '导入',
        name: '参数',
        queryName: '参数名',
        required: '必填项',
        sample: '样例',
        sampleName: '样例名',
        sampleValue: '样例值',
        type: '类型'
      },
      body: {
        name: '请求体',
        none: '该请求没有请求体'
      },
      cookie: {
        name: 'Cookie'
      },
      header: {
        name: 'Header'
      },
      auth: {
        name: 'Authorization'
      },
      preOperation: {
        name: '前置操作'
      },
      postOperation: {
        name: '后置操作'
      },
      setting: {
        name: '设置'
      }
    },
    response: {
      contentFormat: '内容格式化',
      contentName: '内容名称',
      httpCode: 'HTTP状态码',
      name: '响应',
      nameIsRequired: '请输入名称',
      success: '成功'
    },
    saveSuccessContinueAdd: '保存成功，继续添加API吗？',
    status: {
      name: '状态',
      develop: '开发中',
      test: '测试中',
      deprecated: '已作废',
      released: '已发布'
    },
    supportMarkdown: '支持 markdown 文档',
    url: 'URL',
    version: '版本'
  },
  common: {
    add: '添加',
    addAPI: '添加 API',
    addFolder: '添加目录',
    addTest: '添加测试',
    apiManage: 'API',
    apiSummary: 'API 概要',
    apiTest: 'API 测试',
    cancel: '取消',
    close: '关闭',
    confirm: '确认',
    continueAdd: '继续添加',
    copy: '复制',
    customAPIMethod: '自定义方法',
    delete: '删除',
    desc: '备注',
    edit: '编辑',
    goBack: '后退',
    graphql: 'GraphQL',
    loading: '加载中',
    none: '无',
    ok: '确定',
    pleaseWait: '请稍等',
    preview: '预览',
    import: '导入',
    refresh: '刷新',
    rename: '重命名',
    save: '保存',
    setting: '设置',
    welcomePage: '欢迎'
  },
  datePicker: {
    year: '',
    month: '月',
    sunday: '日',
    monday: '一',
    tuesday: '二',
    wednesday: '三',
    thursday: '四',
    friday: '五',
    saturday: '六',
    january: '一月',
    february: '二月',
    march: '三月',
    april: '四月',
    may: '五月',
    june: '六月',
    july: '七月',
    august: '八月',
    september: '九月',
    october: '十月',
    november: '十一月',
    december: '十二月',
    selectDate: '选择日期',
    selectTime: '选择时间',
    selectYear: '选择年份',
    selectMonth: '选择月份',
    clear: '清空',
    confirm: '确认',
    cancel: '取消',
    now: '现在',
    startTime: '开始时间',
    endTime: '结束时间'
  },
  empty: {
    description: '没有数据'
  },
  /** 下面是合并layui-vue **/
  input: {
    placeholder: '请输入'
  },
  page: {
    previous: '前一页',
    next: '后一页',
    goTo: '跳转',
    confirm: '确认',
    page: '页',
    item: '项',
    total: '总数'
  },
  setting: {
    environment: '环境'
  },
  upload: {
    text: '上传文件',
    dragText: '点击上传或拖动文件到这里上传',
    defaultErrorMsg: '上传失败',
    urlErrorMsg: '上传地址格式无效',
    numberErrorMsg: '上载的文件数超过指定数',
    cutInitErrorMsg: '剪辑插件初始化失败',
    uploadSuccess: '上传成功',
    cannotSupportCutMsg: '当前版本不支持单个多个文件剪辑。尝试将multiple设置为false，并通过@done获取返回的文件对象',
    occurFileSizeErrorMsg: '文件大小警告，最大文件大小不能超过目标KB',
    startUploadMsg: '开始上传',
    confirmBtn: '确认',
    cancelBtn: '取消',
    title: '标题'
  }
}
