import { createPopper, Placement, VirtualElement } from '@popperjs/core'
import { nextTick } from 'vue'
import { layer } from '@layui/layui-vue'
import axios from 'axios'
import { DataStruct } from '@/store/model'
axios.interceptors.response.use(function (response) {
  return response.data
})
export default {
  version: '1.0.0',
  api: 'http://ydecloud-os.local.com/',
  apiBuilder: 'http://localhost:9998',
  modelDesign: 'http://localhost:9997',
  socket: 'ws://localhost:8888',

  /**
   * 保存所有API
   * @param store
   */
  saveAllApi (store: any) {
    if (store.state.design.saving) return

    setTimeout(() => {
      this.saveAPI(store, '', (rst) => {
        const data: any = { saving: false }
        if (rst?.success) {
          data.saved = 1
        }
        store.commit('updateState', data)
      })
    }, 100)
  },
  getLanguage () {
    const urlParams = new URLSearchParams(document.location.search)
    return (urlParams.get('lang') || navigator.languages?.[0].toLowerCase() || 'en').replace(/-/, '')
  },
  saveJwt (jwt: string) {
    window.sessionStorage.setItem('jwt', jwt)
  },
  getJwt () {
    return window.sessionStorage.getItem('jwt')
  },
  /**
   * 保存API信息
   * @param store
   * @param apiId 如果传入空，则保存所有api
   * @param cb
   */
  saveAPI (store, apiId: string, cb: any = null) {
    const projectId = store.state.design.project.id
    const allApis: any = JSON.parse(JSON.stringify(store.state.design.apis))
    let willSaveApis: any = {}
    if (apiId) {
      willSaveApis[apiId] = allApis[apiId]
    } else {
      willSaveApis = allApis
    }

    const needSave = { projectId, apis: willSaveApis }
    this.post('api/save/api.json', { data: JSON.stringify(needSave) }, [], (rst: any) => {
      if (cb) {
        cb(rst)
      }
      if (!rst || !rst.success) {
        layer.msg(rst?.msg || 'save failed')
      }
    })
  },
  post (url: string, data: Record<any, any>, files: Record<string, any> = [], showLoading = false, silence = false, postJson = false) {
    let loadingid = ''
    if (showLoading) {
      loadingid = layer.load(2, { time: 0 })
    }
    let fd
    if (postJson) {
      fd = data
    } else {
      fd = new FormData()
      for (const key in data) {
        fd.append(key, data[key])
      }
      for (const file in files) {
        fd.append(file, files[file])
      }
    }
    return new Promise((resolve, reject) => {
      axios({
        method: 'post',
        url: this.api + url.replace(/^\//, ''),
        data: fd,
        headers: { token: this.getJwt() }
      }).then((res: any) => {
        if (showLoading) layer.close(loadingid)
        if (res?.success) {
          resolve(res.data)
        } else {
          if (!silence) layer.msg(res?.msg || 'post failed')
          reject(res)
        }
      }).catch((reason: any) => {
        if (showLoading) layer.close(loadingid)
        const res = reason.response?.data || { success: false, msg: '[' + reason.response.status + ']' + reason.response.statusText }
        if (!silence) layer.msg(res?.msg || 'post failed')
        reject(res)
      })
    })
  },
  get (url: string, showLoading = false, silence = false) {
    let loadingid = ''
    if (showLoading) {
      loadingid = layer.load(2, { time: 0 })
    }
    return new Promise((resolve, reject) => {
      return axios({
        method: 'get',
        url: this.api + url.replace(/^\//, ''),
        headers: {
          token: this.getJwt()
        },
        responseType: 'json'
      }).then((res: any) => {
        if (showLoading) layer.close(loadingid)
        if (res?.success) {
          resolve(res.data)
        } else {
          if (!silence) layer.msg(res?.msg || 'get failed')
          reject(res)
        }
      }).catch((reason: any) => {
        if (showLoading) layer.close(loadingid)
        const res = reason.response?.data || { success: false, msg: '[' + reason.response.status + ']' + reason.response.statusText }
        if (!silence) layer.msg(res?.msg || 'get failed')
        reject(res)
      })
    })
  },

  deepMerge (...objs: Array<Record<any, any>>) {
    const result = Object.create(null)
    objs.forEach(obj => {
      if (obj) {
        Object.keys(obj).forEach(key => {
          const val = obj[key]
          if (this.isPlainObject(val)) {
            // 递归
            if (this.isPlainObject(result[key])) {
              result[key] = this.deepMerge(result[key], val)
            } else {
              result[key] = this.deepMerge(val)
            }
          } else {
            result[key] = val
          }
        })
      }
    })
    // console.log(result)
    return result
  },
  isPlainObject (val: any) {
    return toString.call(val) === '[object Object]'
  },
  confirm (msg, okLabel, cancelLabel) {
    return new Promise((resolve, reject) => {
      layer.confirm(msg, {
        btn: [
          {
            text: okLabel,
            callback: (id) => {
              resolve(id)
            }
          },
          {
            text: cancelLabel,
            callback: (id) => {
              layer.close(id)
              reject(id)
            }
          }
        ]
      })
    })
  },
  alert (msg, okLabel) {
    return new Promise((resolve, reject) => {
      layer.confirm(msg, {
        btn: [
          {
            text: okLabel,
            callback: (id) => {
              layer.close(id)
              resolve(id)
            }
          }
        ]
      })
    })
  },

  /**
   * 切换显示Popper弹出菜单
   *
   * @param trigger 打开关闭控制值 true打开 false关闭
   * @param openWhere 在那个位置打开
   * @param el 打开的dom
   * @param placement
   * @param offset
   */
  togglePopper: function (trigger: any, openWhere: Element | VirtualElement, el: any, placement: Placement = 'bottom-end', offset = [0, 10]) {
    const oldState = trigger.value
    trigger.value = !oldState
    if (!trigger.value) return
    nextTick(function () {
      createPopper(openWhere, el.value, {
        placement,
        modifiers: [
          {
            name: 'offset',
            options: {
              offset: offset
            }
          }
        ]
      })
    })
  },
  isEmptyString: function (str: string) {
    return str === null || str === ''
  },
  isEmptyObject: function (e: any) {
    // eslint-disable-next-line
    for (const t in e) {
      return !1
    }
    return !0
  },

  formatFloat: function (f: any) {
    const v = (parseFloat(f) || 0).toFixed(2)
    if (v.match(/\.00/)) {
      return parseInt(v)
    } else {
      return parseFloat(v)
    }
  },

  /**
   *
   * @param {type} len 长度
   * @param {type} radix 进制
   * @returns {String}
   */
  uuid: (len = 0, radix = 0, prefix = '') => {
    const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.split('')
    radix = radix || chars.length
    const uuid: any = []
    if (len) {
      // Compact form
      for (let i = 0; i < len; i++) { uuid[i] = chars[0 | Math.random() * radix] }
    } else {
      // rfc4122, version 4 form
      let r
      // rfc4122 requires these characters
      uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-'
      uuid[14] = '4'
      // Fill in random data. At i==19 set the high bits of clock sequence as
      // per rfc4122, sec. 4.1.5
      for (let i = 0; i < 36; i++) {
        if (!uuid[i]) {
          r = 0 | Math.random() * 16
          uuid[i] = chars[(i === 19) ? (r & 0x3) | 0x8 : r]
        }
      }
    }

    return (prefix || '') + uuid.join('')
  },
  /**
   * 提取query string
   * @param string
   */
  parseQueryString: (string: string) => {
    let decodeString = decodeURIComponent(string)
    decodeString = decodeString.replace(/^.*\?/, '')
    const args = {}
    const keyValues = decodeString.split('&')
    for (const keyValue of keyValues) {
      if (!keyValue) continue
      const [key, value] = keyValue.split('=')
      const match = key.match(/(?<name>.+)\[(?<index>.*)\]/)
      if (match?.groups) { // array or map
        const { name, index } = match.groups
        if (!args[name] || args[name].type !== 'map') args[name] = { name, value: [], type: 'map' } // 第一次初始化先作为map
        args[name].value.push({ name: index.trim(), value: value || '' })
      } else {
        let type = 'string'
        if (value && value.match(/^\d*\.\d+$/)) {
          type = 'number'
        } else if (value && value.match(/^\d+$/)) {
          type = 'integer'
        }
        args[key] = { name: key, value: value || '', type }
      }
    }
    for (const key in args) {
      if (args[key].type !== 'map') continue
      let isMap = false
      const values: any = []
      for (const { name, value } of args[key].value) {
        values.push(value)
        if (name !== '') isMap = true
      }
      // 所有的index都是空字符串，则未数组
      if (!isMap) {
        args[key].type = 'array'
        args[key].value = values
      }
    }
    return Object.values(args)
  },
  parseJsonData (name: string, isRoot: boolean, json: any) {
    let struct: any = null
    if (Array.isArray(json)) {
      struct = { name, uuid: this.uuid(), type: 'array', isRoot, item: { uuid: this.uuid(), type: 'string' } }
      if (!json[0]) {
        return struct
      }
      struct.item = this.parseJsonData('', false, json[0])
    } else if (this.isPlainObject(json)) {
      struct = { name, uuid: this.uuid(), type: 'object', isRoot, props: [] }
      for (const item in json) {
        struct.props.push(this.parseJsonData(item, false, json[item]))
      }
    } else {
      struct = { name, uuid: this.uuid(), defaultValue: json, type: (typeof json), isRoot }
    }
    return struct
  },
  mockJson (jsonData: any) {
    let ret = {}
    if (jsonData.type === 'array') {
      if (jsonData.name) { // 一个item, 如name=>[]
        ret[jsonData.name] = [this.mockJson(jsonData.item)]
      } else { // 是最顶级的array，如[]
        ret = [this.mockJson(jsonData.item)]
      }
    } else if (jsonData.type === 'object') {
      let props = {}
      if (jsonData.props) {
        for (const prop of jsonData.props) {
          props = this.deepMerge(props, this.mockJson(prop))
        }
      }
      if (jsonData.name) {
        ret[jsonData.name] = props
      } else {
        ret = props
      }
    } else {
      if (jsonData.name) {
        ret[jsonData.name] = jsonData.type
      } else {
        ret = jsonData.type
      }
    }
    return ret
  }
}
