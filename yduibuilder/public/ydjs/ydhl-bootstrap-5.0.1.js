
/**
 * Bootstrap 5.0.1
 */
(function () {
  /**
   * 该全局属性定义项目使用的是那个前端库
   * @type String
   */

  if (!this.YDJS) {
    function invoke_cb (cb, args) {
      if (typeof cb === 'function') {
        cb.apply(this, args)
      } else if (window[cb]) {
        window[cb].apply(this, args)
      } else {
        YDJS.hide_dialog(args[0])
      }
    }

    this.YDJS = {
      /**
       * 使用的前端库
       */
      UI_FRAMEWORK_NAME: 'bootstrap',

      /**
       * 成功
       */
      ICON_SUCCESS: 'success',
      /**
       * 失败
       */
      ICON_ERROR: 'error',
      /**
       * 一般信息
       */
      ICON_INFO: 'info',
      /**
       * 警告
       */
      ICON_WARN: 'warn',
      /**
       * 询问
       */
      ICON_QUESTION: 'question',
      /**
       * 或者不定义，就表示没有图标
       */
      ICON_NONE: 'none',

      POSITION_TOP: 'top',
      POSITION_LEFT: 'left',
      POSITION_BOTTOM: 'bottom',
      POSITION_RIGHT: 'right',
      POSITION_CENTER: 'center',
      POSITION_LEFT_TOP: 'left&top',
      POSITION_LEFT_BOTTOM: 'left&bottom',
      POSITION_RIGHT_TOP: 'right&top',
      POSITION_RIGHT_BOTTOM: 'right&bottom',

      SIZE_FULL: 'full',
      SIZE_LARGE: 'large',
      SIZE_NORMAL: 'normal',
      SIZE_SMALL: 'small',

      BACKDROP_NONE: 'none',
      BACKDROP_NORMAL: 'normal',
      BACKDROP_STATIC: 'static',

      /**
       * 恢复yd-spin-btn的效果
       *
       * @param {type} selector
       * @returns {undefined}
       */
      spin_clear: function (selector) {
        var originText = $(selector).attr('data-btn-text')
        $(selector).prop('disabled', false)
        if (!originText) return
        $(selector).html(YDJS.urldecode(originText).replace(/\+/ig, ' '))
      },

      /**
       * 隐藏弹出的对话框,参数是toast, alert, dialog等返回的内容
       *
       * @param {type} loadingId
       * @returns {undefined}
       */
      hide_dialog: function (dialog_id) {
        var el = document.getElementById(dialog_id)
        if (!el) return
        setTimeout(function () {
          // console.log('hide dialog:' + dialog_id)
          $('.modal-backdrop').remove()
          $(el).remove()
        }, 200)
      },
      update_loading: function (dialog_id, content) {

      },

      /**
       *
       * @param {type} msg
       * @param {type} loadingId 可选参数
       * @param {type} overlay dom元素或者selector选择器，如果指定loading则覆盖在该元素上面
       * @returns {undefined} 对话框id
       */
      loading: function (msg, loadingId = null, overlay = null) {
        loadingId = loadingId || YDJS.uuid(16, 0, 'loading-')

        $('body').append(
          `<div class="modal fade" id="${loadingId}" data-bs-backdrop="static" data-bs-keyboard="false"
                        tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog modal-sm modal-dialog-scrollable modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body d-flex justify-content-center align-items-center">
                                    <div class="spinner-border me-3 text-primary" role="status">
                                        <span class="visually-hidden"></span>
                                    </div>
                                    ${msg}
                                </div>
                            </div>
                        </div>
                    </div>`)
        var el = document.getElementById(loadingId)
        var prompt = new bootstrap.Modal(el, {
          backdrop: 'static',
          focus: true,
          keyboard: false
        })
        prompt.show()
        return loadingId
      },
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
      toast: function (msg, icon, cb, backdrop, delay, position) {
        icon = icon || "info";
        var bgMap = {
          error: 'danger',
          info: 'info',
          success: 'success',
          question: 'primary',
          warn: 'warning'
        }
        delay = (!delay || isNaN(delay)) ? 3000 : parseInt(delay);
        var bg = 'bg-' + bgMap[icon]

        var hide = 'true'
        if (delay == 0) {
          hide = 'false';
        }

        position = position || "center";

        var position_value;
        var map = {
          'top': 'top-0 start-50 translate-middle-x',
          'left': 'top-50 start-0 translate-middle-y',
          'bottom': 'bottom-0 start-50 translate-middle-x',
          'right': 'top-50 end-0 translate-middle-y',
          'center': 'top-50 start-50 translate-middle',
          'left&top': 'top-0 start-0',
          'left&bottom': 'bottom-0 start-0',
          'right&top': 'top-0 end-0',
          'right&bottom': 'bottom-0 end-0'
        };
        position_value = map[position];
        var dialogid = YDJS.uuid(16, 16, "toast-");
        $('body').append(
          `<div id="${dialogid}" class="${position_value} popover position-fixed toast align-items-center text-white ${bg} border-0" role="alert" data-bs-autohide="${hide}"  data-bs-delay="${delay}"  aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                        <div class="toast-body">${msg}</div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>`
        );
        var el = document.getElementById(dialogid)
        var toast = new bootstrap.Toast(el, {})
        el.addEventListener('hidden.bs.toast', function (event) {
          if (cb) {
            cb()
          }
          var el = event.target;
          var modal = bootstrap.Toast.getInstance(el);
          modal.hide();
          $(el).remove();
        })
        toast.show()
      },
      /**
       *
       * @param {type} url 地址,如果有地址,则忽略content,title
       * @param {type} content 对话框的内容
       * @param {type} title 对话框的标题
       * @param {type} size 见YDJS.SIZE_XXX 默认normal
       * @param {type} backdrop 见YDJS.BACKDROP
       * @param {type} buttonAndCallbacks 按钮及其回调的数组 [{label:"按钮名称","cb":回调函数名或者匿名函数}] 回调参数是dialog_id
       * @param {type} dialog_id 对话框id,可不传
       * @param {type} submittedCallback 加载的表单提交回调，传入表单提交后接口返回的值
       * @returns {undefined} 对话框id
       */
      dialog: function (url, content, title, size, backdrop, buttonAndCallbacks, dialog_id, submittedCallback) {
        dialog_id = dialog_id || YDJS.uuid(16, 16, 'dialog-') //加载框的id
        size = size || 'normal' //定义对话框的大小:full 全屏 larget 大 normal 普通 small 小
        var backdrop = false
        if (backdrop === 'static') {
          backdrop = 'static'
        } else if (backdrop === 'normal') {
          backdrop = true
        } else {
          backdrop = true
        }

        var sizeCss = {
          'full': 'modal-fullscreen',
          'large': 'modal-lg',
          'normal': '',
          'small': 'modal-sm'
        }
        var args = {
          keyboard: true,
          focus: true,
          backdrop: backdrop
        }

        if (!buttonAndCallbacks) {
          buttonAndCallbacks = [
            {
              label: '确定',
              cb: function () {
                YDJS.hide_dialog(dialog_id)
              }
            }
          ]
        }
        var buttons = ''
        if (buttonAndCallbacks.length > 0) {
          var okBtn = buttonAndCallbacks[0]

          buttons += `<button type="button" class="btn btn-primary" data-dialog-id="${dialog_id}" id="${dialog_id}_btn0">${okBtn.label}</button>`
          $('body').on('click', `${dialog_id}_btn0`, function () {
            var dialog_id = $(this).attr('data-dialog-id')
            invoke_cb(okBtn.cb, [dialog_id])
          })

          for (var i = 1; i < buttonAndCallbacks.length; i++) {
            var btn = buttonAndCallbacks[i]
            buttons += `<button type="button" class="btn btn-secondary" data-dialog-id="${dialog_id}" id="${dialog_id}_btn${i}"data-bs-dismiss="modal">${btn.label}</button>`
            $('body').on('click', `${dialog_id}_btn${i}`, function () {
              var dialog_id = $(this).attr('data-dialog-id')
              invoke_cb(btn.cb, [dialog_id])
            })
          }
        }

        if (buttons) {
          buttons += `<div class="modal-footer">${buttons}</div>`
        }
        var title = title ? `<h5 class="modal-title" id="staticBackdropLabel">${title}</h5>` : ''
        var yze = new yze_ajax()
        yze.get(url, function (html) {
          $('body').append(`<div class="modal fade" style="z-index: 100020" id="${dialog_id}" data-bs-backdrop="static" data-bs-keyboard="false"
                        tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                  <div class="modal-dialog ${sizeCss[size]} modal-dialog-scrollable modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">${title}
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        ${html}
                      </div>
                    ${buttons}
                    </div>
                  </div>
                </div>`)

          const el = document.getElementById(dialog_id)
          var myModal = new bootstrap.Modal(el, args)
          el.addEventListener('hidden.bs.modal', function (event) {
            myModal.dispose()
            $(event.target).remove()
          })
          myModal.show()
        }, function (msg) {
          YDJS.toast(msg, YDJS.ICON_ERROR)
        }, function (rst) {
          invoke_cb(submittedCallback, [rst])
        })
        return dialog_id
      },

      /**
       * 更新对话框的内容
       *
       * @param dialog_id
       * @param content
       */
      update_loading: function (dialog_id, content) {

      },

      /**
       *
       * @param {type} content
       * @param {type} title
       * @param {type} icon 图标见YDJS.ICON_XX定义 默认YDJS.ICON_INFO
       * @param {type} buttonAndCallbacks buttonAndCallbacks 按钮及其回调的数组 [{label:"按钮名称","cb":回调函数名或者匿名函数}] 可选,默认是确定按钮 回调参数是dialog_id
       * @param {type} dialog_id 可选
       * @returns {undefined}
       */
      alert: function (content, title, icon, buttonAndCallbacks, dialog_id) {
        icon = icon || 'info'
        title = title ? `<div class="card-header">${title}</div>` : ''
        dialog_id = dialog_id || YDJS.uuid(16, 16, 'confirm-') //加载框的id

        if (!buttonAndCallbacks){
          buttonAndCallbacks = [ { label: "知道了", cb: null } ]
        }
        var buttons = '';

        for (var index = 0; index < buttonAndCallbacks.length; index++) {
          buttons += `<button type="button" class="btn btn-primary yd-spin-btn" data-dialog-index="${index}" data-dialog-id="${dialog_id}"
                id="${dialog_id}_btn_${index}">${buttonAndCallbacks[index].label}</button>`;

          $('body').on('click', `#${dialog_id}_btn_${index}`, function () {
            var dialog_id = $(this).attr('data-dialog-id')
            var index = $(this).attr('data-dialog-index')
            invoke_cb(buttonAndCallbacks[index].cb, [dialog_id])
          })
        }
        $('body').append(`<div class="modal" id="${dialog_id}" tabIndex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            ${title}
                            <div class="modal-body">
                                ${content}
                            </div>
                            <div class="modal-footer">
                                ${buttons}
                            </div>
                        </div>
                    </div>
                </div>`)

        var el = document.getElementById(dialog_id)
        var prompt = new bootstrap.Modal(el, {
          backdrop: 'static',
          focus: true,
          keyboard: true
        })

        el.addEventListener('hidden.bs.modal', function (event) {
          $(event.target).remove()
        })
        prompt.show()
        return dialog_id
      },

      /**
       *
       * @param {type} content 确认对话框的内容
       * @param {type} title 确认对话框的标题
       * @param {type} yes_cb 确认按钮的回调 参数是dialog_id
       * @param {type} no_cb 取消按钮的回调 参数是dialog_id
       * @param {type} yes_label 确认按钮名称 默认"确认"
       * @param {type} no_label 取消按钮的名称 默认"取消"
       * @param {type} dialog_id 对话框id
       * @returns {undefined} 对话框id
       */
      confirm: function (content, title, yes_cb, no_cb, yes_label, no_label, icon, dialog_id) {
        icon = icon || 'question'
        title = title ? `<div class="card-header">${title}</div>` : ''
        dialog_id = dialog_id || YDJS.uuid(16, 16, 'confirm-') //加载框的id

        $('body').append(`<div class="modal" id="${dialog_id}" tabIndex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            ${title}
                            <div class="modal-body">
                                ${content}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dialog-id="${dialog_id}" id="${dialog_id}_confirm_cancel">${no_label || 'Cancel'}</button>
                                <button type="button" class="btn btn-primary yd-spin-btn" data-dialog-id="${dialog_id}" id="${dialog_id}_confirm_ok">${yes_label || 'Ok'}</button>
                            </div>
                        </div>
                    </div>
                </div>`)

        $('body').on('click', `#${dialog_id}_confirm_ok`, function () {
          var dialog_id = $(this).attr('data-dialog-id')
          invoke_cb(yes_cb, [dialog_id])
        })

        $('body').on('click', `#${dialog_id}_confirm_cancel`, function () {
          var dialog_id = $(this).attr('data-dialog-id')
          invoke_cb(no_cb, [dialog_id])
        })

        var el = document.getElementById(dialog_id)
        var prompt = new bootstrap.Modal(el, {
          backdrop: 'static',
          focus: true,
          keyboard: true
        })

        el.addEventListener('hidden.bs.modal', function (event) {
          $(event.target).remove()
        })
        prompt.show()
        return dialog_id
      },
      /**
       *
       * @param {type} title 标题
       * @param {type} defaultValue 默认值
       * @param {type} type input 输入框 password 密码框 textarea 文本框
       * @param {type} cb 确定后的回调,参数是dialog_id , value
       * @param {type} dialog_id
       * @returns {undefined} 对话框id
       */
      prompt: function (title, defaultValue, type, cb, dialog_id) {
        var type_map = {
          input: 0,
          password: 1,
          textarea: 2
        }
        if (!type_map[type]) {
          type = 'input'
        }
        var input = {
          input: `<input type="text" class="form-control ydjs-prompt-value" value="${defaultValue || ''}">`,
          textarea: `<textarea class="form-control ydjs-prompt-value">${defaultValue || ''}</textarea>`,
          password: `<input type="password ydjs-prompt-value" class="form-control" value="${defaultValue || ''}">`
        }
        dialog_id = dialog_id || YDJS.uuid(16, 16, 'prompt-') //加载框的id

        $('body').append(`<div class="modal" id="${dialog_id}" tabIndex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body">
                                <p>${title}</p>
                                ${input[type]}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" data-dialog-id="${dialog_id}" id="${dialog_id}_prompt_ok">Ok</button>
                            </div>
                        </div>
                    </div>
                </div>`)

        $('body').on('click', `#${dialog_id}_prompt_ok`, function () {
          var dialog_id = $(this).attr('data-dialog-id')
          invoke_cb(cb, [dialog_id, $(`#${dialog_id} .ydjs-prompt-value`).val()])
        })

        var el = document.getElementById(dialog_id)
        var prompt = new bootstrap.Modal(el, {
          backdrop: 'static',
          focus: true,
          keyboard: true
        })

        el.addEventListener('hidden.bs.modal', function (event) {
          $(event.target).remove()
        })
        prompt.show()
        return dialog_id

      },
      /**
       * 重新绑定事件
       * @returns {undefined}
       */
      rebind: function () {
      },
      urlencode: function (clearString) {
        var output = ''
        var x = 0

        clearString = utf16to8(clearString.toString())
        var regex = /(^[a-zA-Z0-9-_.]*)/

        while (x < clearString.length) {
          var match = regex.exec(clearString.substr(x))
          if (match != null && match.length > 1 && match[1] != '') {
            output += match[1]
            x += match[1].length
          } else {
            if (clearString[x] == ' ') {
              output += '+'
            } else {
              var charCode = clearString.charCodeAt(x)
              var hexVal = charCode.toString(16)
              output += '%' + (hexVal.length < 2 ? '0' : '') + hexVal.toUpperCase()
            }
            x++
          }
        }

        function utf16to8 (str) {
          var out, i, len, c

          out = ''
          len = str.length
          for (i = 0; i < len; i++) {
            c = str.charCodeAt(i)
            if ((c >= 0x0001) && (c <= 0x007F)) {
              out += str.charAt(i)
            } else if (c > 0x07FF) {
              out += String.fromCharCode(0xE0 | ((c >> 12) & 0x0F))
              out += String.fromCharCode(0x80 | ((c >> 6) & 0x3F))
              out += String.fromCharCode(0x80 | ((c >> 0) & 0x3F))
            } else {
              out += String.fromCharCode(0xC0 | ((c >> 6) & 0x1F))
              out += String.fromCharCode(0x80 | ((c >> 0) & 0x3F))
            }
          }
          return out
        }

        return output
      },
      urldecode: function (encodedString) {
        var output = encodedString
        var binVal, thisString
        var myregexp = /(%[^%]{2})/

        function utf8to16 (str) {
          var out, i, len, c
          var char2, char3

          out = ''
          len = str.length
          i = 0
          while (i < len) {
            c = str.charCodeAt(i++)
            switch (c >> 4) {
              case 0:
              case 1:
              case 2:
              case 3:
              case 4:
              case 5:
              case 6:
              case 7:
                out += str.charAt(i - 1)
                break
              case 12:
              case 13:
                char2 = str.charCodeAt(i++)
                out += String.fromCharCode(((c & 0x1F) << 6) | (char2 & 0x3F))
                break
              case 14:
                char2 = str.charCodeAt(i++)
                char3 = str.charCodeAt(i++)
                out += String.fromCharCode(((c & 0x0F) << 12) |
                  ((char2 & 0x3F) << 6) |
                  ((char3 & 0x3F) << 0))
                break
            }
          }
          return out
        }

        while ((match = myregexp.exec(output)) != null
        && match.length > 1
        && match[1] != '') {
          binVal = parseInt(match[1].substr(1), 16)
          thisString = String.fromCharCode(binVal)
          output = output.replace(match[1], thisString)
        }

        //output = utf8to16(output);
        output = output.replace(/\\+/g, ' ')
        output = utf8to16(output)
        return output
      },
      /**
       *
       * @param {type} len 长度
       * @param {type} radix 进制
       * @returns {String}
       */
      uuid: function (len, radix, prefix) {
        var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.split('')
        radix = radix || chars.length
        var uuid = []
        if (len) {
          // Compact form
          for (i = 0; i < len; i++) {
            uuid[i] = chars[0 | Math.random() * radix]
          }
        } else {
          // rfc4122, version 4 form
          var r
          // rfc4122 requires these characters
          uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-'
          uuid[14] = '4'
          // Fill in random data. At i==19 set the high bits of clock sequence as
          // per rfc4122, sec. 4.1.5
          for (i = 0; i < 36; i++) {
            if (!uuid[i]) {
              r = 0 | Math.random() * 16
              uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r]
            }
          }
        }

        return (prefix || '') + uuid.join('')
      }
    }
  }
}())
