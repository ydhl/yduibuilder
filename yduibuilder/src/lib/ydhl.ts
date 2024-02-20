import $ from 'jquery'
import { createPopper, Placement, VirtualElement } from '@popperjs/core'
import { nextTick } from 'vue'
import { YDJSStatic } from './ydjs'
import { layer } from '@layui/layer-vue'
declare const YDJS: YDJSStatic
export default {
  version: '1.0.16-220207',
  api: 'http://ydecloud-os.local.com/',
  uploadApi: 'http://ydecloud-os.local.com/upload/',
  socket: 'ws://localhost:8888',
  // api: 'https://ydecloud.yidianhulian.com/',
  // socket: 'wss://ydecloud.yidianhulian.com:8888',

  save (store: any, newVersion = false, message = '') {
    if (store.state.design.saving) return
    // 保存前通知处于内联编辑的UI先更新下value
    store.commit('updatePageState', { updateInlineItemValue: true, saving: true })

    const currFunctionId = store.state.design.function.id
    const currPage = store.state.design.page
    const versionId = store.state.design.pageVersionId[currPage.meta.id]

    setTimeout(() => {
      this.savePage(currFunctionId, currPage, versionId, (rst) => {
        const data: any = { saving: false }
        if (rst?.success) {
          data.saved = 1
          data.pageUuid = currPage.meta.id
          data.versionId = rst.data.versionId
        }
        store.commit('updateSavedState', data)
      }, newVersion, message)
    }, 100)
  },
  saveAsVersion (store: any) {
    if (store.state.design.saving) return
    YDJS.prompt('Commit Message:', '', 'textarea', (dialogid, value) => {
      YDJS.hide_dialog(dialogid)
      this.save(store, true, value)
    })
  },
  getRgbaInfo (str: string) {
    if (!str) return { r: 0, g: 0, b: 0, a: 1 }
    if (str.trim().match(/^#/)) {
      if (str.length === 4) { // #fff 短写格式
        const r = str.slice(1, 2)
        const g = str.slice(2, 3)
        const b = str.slice(3, 4)
        return {
          r: parseInt('0x' + r + r),
          g: parseInt('0x' + g + g),
          b: parseInt('0x' + b + b),
          a: 1
        }
      }
      const a = parseInt('0x' + str.slice(7, 9))
      return {
        r: parseInt('0x' + str.slice(1, 3)),
        g: parseInt('0x' + str.slice(3, 5)),
        b: parseInt('0x' + str.slice(5, 7)),
        a: !isNaN(a) ? a / 255 : 1
      }
    }

    const matches: any = str.match(/(?<r>[\d]+)\s*,\s*(?<g>[\d]+)\s*,\s*(?<b>[\d]+)\s*(,\s*(?<a>.+))?\)/)

    return {
      r: matches?.groups?.r,
      g: matches?.groups?.b,
      b: matches?.groups?.b,
      a: matches?.groups?.a !== undefined ? matches?.groups?.a : 1
    }
  },
  hex2rgba (hex: string) {
    const info = this.getRgbaInfo(hex)
    return 'rgba(' + info.r + ',' + info.g + ',' + info.b + ',' + info.a + ')'
  },
  rgba2hex (rgba: string) {
    const info: any = this.getRgbaInfo(rgba)
    if (!info?.r) return ''
    let hex =
        (info.r | 1 << 8).toString(16).slice(1) +
        (info.g | 1 << 8).toString(16).slice(1) +
        (info.b | 1 << 8).toString(16).slice(1)
    hex = hex + (((info?.a || 1) * 255) | 1 << 8).toString(16).slice(1)

    return hex
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
  deletePage (pageid, cb) {
    this.post('api/deletepage.json', { pageid }, [], (rst: any) => {
      if (cb) {
        cb(rst)
      }
      if (!rst || !rst.success) {
        YDJS.toast(rst?.msg || 'delete failed', YDJS.ICON_ERROR)
      }
    })
  },
  deletePopup (pageid, cb) {
    this.post('api/deletepage.json', { pageid, type: 'popup' }, [], (rst: any) => {
      if (cb) {
        cb(rst)
      }
      if (!rst || !rst.success) {
        YDJS.toast(rst?.msg || 'delete failed', YDJS.ICON_ERROR)
      }
    })
  },
  // eslint-disable-next-line camelcase
  savePage (functionId: any, page: any, versionId, cb: any = null, newVersion = false, message = '', isCopy = false) {
    if (!page) {
      const rst = { success: false, msg: 'Page Not Exist' }
      if (cb) cb(rst)
      return
    }
    const self = this
    const needSave = { page, functionId, versionId }
    this.post('api/save.json', { data: JSON.stringify(needSave), copy: isCopy ? 1 : 0, new_version: newVersion ? '1' : '', message }, [], (rst: any) => {
      if (cb) {
        cb(rst)
      }
      if (!rst || !rst.success) {
        YDJS.toast(rst?.msg || 'save failed', YDJS.ICON_ERROR)
      } else {
        const socket = new WebSocket(self.socket)
        socket.addEventListener('open', function (event) {
          socket.send(JSON.stringify({
            action: 'modifiedPage',
            pageid: page.meta.id,
            token: self.getJwt()
          }))
          socket.close()
        })
      }
    })
  },

  get (url: string, data = {}, cb: any, dataType = 'json') {
    $.ajax({
      headers: {
        token: this.getJwt()
      },
      url: this.api + url,
      data: data,
      crossDomain: true,
      success: (data: any) => cb(data),
      dataType
    })
  },
  /**
   * post请求
   * @param url 不包含域名部分
   * @param data
   * @param files
   * @param cb
   */
  post (url: string, data: Record<any, any> = {}, files: Record<string, any>, cb: any, dataType = 'json') {
    const fd: FormData = new FormData()
    for (const key in data) {
      fd.append(key, data[key])
    }
    for (const file in files) {
      fd.append(file, files[file])
    }

    $.ajax({
      headers: {
        token: this.getJwt()
      },
      method: 'post',
      processData: false,
      contentType: false,
      url: this.api + url,
      data: fd,
      crossDomain: true,
      success: (data: any) => cb(data),
      error: (data: any) => cb(data),
      dataType
    })
  },
  /**
   * post请求，请求数据为json
   * @param url 不包含域名部分
   * @param data
   * @param files
   * @param cb
   */
  postJson (url: string, data: Object) {
    return new Promise((resolve, reject) => {
      $.ajax({
        headers: {
          token: this.getJwt()
        },
        method: 'post',
        processData: false,
        contentType: false,
        url: this.api + url,
        data: JSON.stringify(data),
        crossDomain: true,
        success: (data: any) => resolve(data),
        error: (data: any) => reject(data.responseText),
        dataType: 'json'
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
    this.openPopper(openWhere, el, placement, offset)
  },
  /**
   * 打开Popper弹出菜单
   *
   * @param openWhere 在那个位置打开
   * @param el 打开的dom
   * @param placement
   * @param offset
   */
  openPopper: function (openWhere: Element | VirtualElement, el: any, placement: Placement = 'bottom-end', offset = [0, 10]) {
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
    // eslint-disable-next-line no-unreachable-loop
    for (const t in e) {
      return !1
    }
    return !0
  },
  confirm (msg, okLabel, cancelLabel) {
    return new Promise((resolve, reject) => {
      layer.confirm(msg, {
        title: '',
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
  alert (msg, okLabel = 'OK') {
    return new Promise((resolve, reject) => {
      layer.confirm(msg, {
        title: 'YDECloud',
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
  loading (msg) {
    return new Promise((resolve) => {
      const id = layer.msg(msg, {
        icon: 16,
        time: 0,
        shade: true
      })
      resolve(id)
    })
  },
  closeLoading (id: any) {
    layer.close(id)
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
  uuid: (len = 0, radix: number = 0, prefix = '') => {
    const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.split('')
    radix = radix || chars.length
    if (radix > 61) radix = 61
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
  getGradientStyle: (gradientInfo: any) => {
    const colors: any = []
    if (gradientInfo.stops) {
      for (const index in gradientInfo.stops) {
        colors.push(`${gradientInfo.stops[index]} ${gradientInfo.colorSize?.[index]}`)
      }
    }
    if (gradientInfo.type === 'radial') {
      const _ = [(gradientInfo.repeat ? 'repeating-' : '') + 'radial-gradient(']
      // shape size at position, color stop
      if (gradientInfo?.sizeCustom?.length > 0) { // eg 20% 40% at 50% 50%
        _.push(gradientInfo?.sizeCustom.join(' '))
      } else { // eg circle farthest-corner at 50% 50%
        _.push(gradientInfo.shape || 'ellipse')
        _.push(gradientInfo.size || 'farthest-corner')
      }
      if (gradientInfo.position?.length) {
        _.push('at ' + gradientInfo.position.join(' '))
      }
      _.push(',' + colors.join(','))
      _.push(')')
      return _.join(' ')
    }
    return `${(gradientInfo.repeat ? 'repeating-' : '')}linear-gradient(${gradientInfo.direction || '0'}deg, ${colors.join(',')})`
  },
  copyValue (dest: any, src: any) {
    dest.uuid = src.uuid
    dest.name = src.name
    dest.type = src.type
    if (src.value) {
      dest.value = src.value
    }
    return dest
  },
  filterEmptyValue (jsonData: any) {
    let ret: any = {}
    if (jsonData.type === 'array') {
      const item = this.filterEmptyValue(jsonData.item)
      if (jsonData.value || item) {
        ret = this.copyValue({}, jsonData)
      }
      if (item) {
        ret.item = item
      }
      return this.isEmptyObject(ret) ? null : ret
    }
    if (jsonData.type === 'object') {
      if (jsonData.props) {
        const props: any = {}
        for (const index in jsonData.props) {
          const item = this.filterEmptyValue(jsonData.props[index])
          if (item) {
            props[index] = item
          }
        }
        if (!this.isEmptyObject(props) || jsonData.value) {
          ret = this.copyValue({}, jsonData)
        }
        if (!this.isEmptyObject(props)) {
          ret.props = props
        }
      }
      return this.isEmptyObject(ret) ? null : ret
    }
    if (jsonData.value) {
      ret = this.copyValue({}, jsonData)
    }
    return this.isEmptyObject(ret) ? null : ret
  }
}
