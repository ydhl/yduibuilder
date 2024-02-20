;
/**
 * bootstrap 5.0.1的前端效果和行为实现
 * 该文件会对dom元素总按照易点互联的规范进行各种事件和效果的绑定, 对应动态load的html 内容,
 * 也需要加载该文件,否则页面加载后再次加载的html不会有效果
 * 该文件需要在ydhl.js之后加载
 *
 * @author leeboo
 */

/**
 * Bootstrap 5.0.1
 */
(function () {
    ;
    /**
     * 该全局属性定义项目使用的是那个前端库
     * @type String
     */

    if (!this.YDJS) {
        var icon_map = {"success": 1, "error": 2, "question": 3, "info": 6, "warn": 0, "none": -1, "": -1};
        function invoke_cb(cb, args) {
            if (typeof cb === "function") {
                cb.apply(this, args);
            } else if (window[cb]) {
                window[cb].apply(this, args);
            } else {
                YDJS.hide_dialog(args[0]);
            }
        }
        this.YDJS = {
            /**
             * 使用的前端库
             */
            UI_FRAMEWORK_NAME: "bootstrap",

            /**
             * 成功
             */
            ICON_SUCCESS: "success",
            /**
             * 失败
             */
            ICON_ERROR: "error",
            /**
             * 一般信息
             */
            ICON_INFO: "info",
            /**
             * 警告
             */
            ICON_WARN: "warn",
            /**
             * 询问
             */
            ICON_QUESTION: "question",
            /**
             * 或者不定义，就表示没有图标
             */
            ICON_NONE: "none",

            POSITION_TOP: "top",
            POSITION_LEFT: "left",
            POSITION_BOTTOM: "bottom",
            POSITION_RIGHT: "right",
            POSITION_CENTER: "center",
            POSITION_LEFT_TOP: "left&top",
            POSITION_LEFT_BOTTOM: "left&bottom",
            POSITION_RIGHT_TOP: "right&top",
            POSITION_RIGHT_BOTTOM: "right&bottom",

            SIZE_FULL: "full",
            SIZE_LARGE: "large",
            SIZE_NORMAL: "normal",
            SIZE_SMALL: "small",

            BACKDROP_NONE: "none",
            BACKDROP_NORMAL: "normal",
            BACKDROP_STATIC: "static",


            /**
             * 恢复yd-spin-btn的效果
             *
             * @param {type} selector
             * @returns {undefined}
             */
            spin_clear: function (selector) {
                var originText = $(selector).attr("data-btn-text");
                $(selector).prop("disabled", false);
                if (!originText) return
                $(selector).html(YDJS.urldecode(originText).replace(/\+/ig, " "));
            },

            /**
             * 隐藏弹出的对话框,参数是toast, alert, dialog等返回的内容
             *
             * @param {type} loadingId
             * @returns {undefined}
             */
            hide_dialog: function (dialog_id) {
                var el = document.getElementById(dialog_id);
                var modal = bootstrap.Modal.getInstance(el);
                modal.hide();
                $(el).remove();
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
            loading: function (msg, loadingId, overlay) {
                loadingId = loadingId || YDJS.uuid(16, 16, "loading-");

                return loadingId;
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
                backdrop = backdrop != "normal" ? "none" : backdrop;
                delay = delay == undefined || isNaN(delay) ? 3000 : parseInt(delay);
                var theme = 'bg-primary'
                if (icon==YDJS.ICON_ERROR) {
                    theme = 'bg-danger'
                }

                var hide = 'true'
                if (delay == 0) {
                    hide = 'false';
                }

                position = position || "center";

                var backdrop_value = 0.01;
                if (backdrop === 'normal') {
                    backdrop_value = 0.3;
                }
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
                    `<div id="${dialogid}" class="${position_value} popover position-fixed toast align-items-center text-white ${theme} border-0" role="alert" data-bs-autohide="${hide}"  data-bs-delay="${delay}"  aria-live="assertive" aria-atomic="true">
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
             * @returns {undefined} 对话框id
             */
            dialog: function (url, content, title, size, backdrop, buttonAndCallbacks, dialog_id) {
                dialog_id = dialog_id || YDJS.uuid(16, 16, "dialog-"); //加载框的id
                size = size || "normal"; //定义对话框的大小:full 全屏 larget 大 normal 普通 small 小
                var backdrop = false;
                if (backdrop === "static") {
                    backdrop = 'static';
                } else if (backdrop === "normal") {
                    backdrop = true;
                } else {
                    backdrop = true;
                }

                var sizeCss = {
                    "full": 'modal-fullscreen',
                    "large": 'modal-lg',
                    "normal": '',
                    "small": 'modal-xl'
                };
                var args = {
                    keyboard: true,
                    focus: true,
                    backdrop: backdrop,
                    id: dialog_id,
                    btnAlign: 'r',
                    success: function (layero, index) {
                        if (!url)
                            return;
                        var title = layer.getChildFrame('title', index);
                        layer.title(title.text(), index);
                    }
                };

                if (!buttonAndCallbacks) {
                    buttonAndCallbacks = [
                        {
                            label: "确定",
                            cb: function () {
                                YDJS.hide_dialog(dialog_id);
                            }
                        }
                    ];
                }
                var buttons = "";
                if (buttonAndCallbacks.length > 0) {
                    var okBtn = buttonAndCallbacks[0];

                    buttons += `<button type="button" class="btn btn-primary" data-dialog-id="${dialog_id}" id="${dialog_id}_btn0">${okBtn.label}</button>`;
                    $('body').on("click", `#${dialog_id}_btn0`, function () {
                        var dialog_id = $(this).attr('data-dialog-id')
                        invoke_cb(okBtn.cb, [dialog_id]);
                    })

                    for (var i = 1; i < buttonAndCallbacks.length; i++) {
                        var btn = buttonAndCallbacks[i];
                        buttons += `<button type="button" class="btn btn-secondary" data-dialog-id="${dialog_id}" id="${dialog_id}_btn${i}"data-bs-dismiss="modal">${btn.label}</button>`
                        $('body').on("click", `#${dialog_id}_btn${i}`, function () {
                            var dialog_id = $(this).attr('data-dialog-id')
                            invoke_cb(btn.cb, [dialog_id]);
                        })
                    }
                }

                if (buttons) {
                    buttons = `<div class="modal-footer">${buttons}</div>`;
                }
                var title = title ? `<h5 class="modal-title" id="staticBackdropLabel">${title}</h5>` : '';
                if (url){
                    var yze = new yze_ajax();
                    yze.get(url, function (html) {
                        $('body').append(`<div class="modal fade" id="${dialog_id}" data-bs-backdrop="static" data-bs-keyboard="false" 
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

                        var myModalEl = document.getElementById(dialog_id)
                        var myModal = new bootstrap.Modal(myModalEl, args);
                        myModalEl.addEventListener('hidden.bs.modal', function (event) {
                            myModal.dispose();
                            $(event.target).remove();
                        })
                        myModal.show();
                    }, function (msg) {
                        YDJS.toast(msg, YDJS.ICON_ERROR)
                    }, function () {
                    })
                }else{
                    $('body').append(`<div class="modal fade" id="${dialog_id}" data-bs-backdrop="static" data-bs-keyboard="false" 
                            tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                      <div class="modal-dialog ${sizeCss[size]} modal-dialog-scrollable modal-dialog-centered">
                        <div class="modal-content">
                          <div class="modal-header">${title}
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            ${content}
                          </div>
                        ${buttons}
                        </div>
                      </div>
                    </div>`)

                    var myModalEl = document.getElementById(dialog_id)
                    var myModal = new bootstrap.Modal(myModalEl, args);
                    myModalEl.addEventListener('hidden.bs.modal', function (event) {
                        myModal.dispose();
                        $(event.target).remove();
                    })
                    myModal.show();
                }
                return dialog_id;
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
                icon = icon || "warn";
                title = title ? `<div class="card-header">${title}</div>` : '';
                dialog_id = dialog_id || YDJS.uuid(16, 16, "confirm-"); //加载框的id

                if (!buttonAndCallbacks) {
                    buttonAndCallbacks = [
                        {
                            label: "确定",
                            cb: function () {
                                YDJS.hide_dialog(dialog_id);
                            }
                        }
                    ];
                }
                var buttons = '';
                for (var i =0; i < buttonAndCallbacks.length; i++) {
                    var btn = buttonAndCallbacks[i];
                    buttons += `<button type="button" class="btn btn-primary yd-spin-btn" data-dialog-id="${dialog_id}"
                            id="${dialog_id}_btn_${i}">${btn.label || "Ok"}</button>`

                    $('body').on("click", `#${dialog_id}_btn_${i}`, function () {
                        var dialog_id = $(this).attr('data-dialog-id')
                        invoke_cb(btn.cb, [dialog_id]);
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
                </div>`);

                var el = document.getElementById(dialog_id);
                var prompt = new bootstrap.Modal(el, {
                    backdrop: 'static',
                    focus: true,
                    keyboard: true
                });

                el.addEventListener('hidden.bs.modal', function (event) {
                    $(event.target).remove()
                })
                prompt.show();
                return dialog_id;
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
                icon = icon || "question";
                title = title ? `<div class="card-header">${title}</div>` : '';
                dialog_id = dialog_id || YDJS.uuid(16, 16, "confirm-"); //加载框的id

                $('body').append(`<div class="modal" id="${dialog_id}" tabIndex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            ${title}
                            <div class="modal-body">
                                ${content}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dialog-id="${dialog_id}" id="${dialog_id}_confirm_cancel">${no_label || "Cancel"}</button>
                                <button type="button" class="btn btn-primary yd-spin-btn" data-dialog-id="${dialog_id}" id="${dialog_id}_confirm_ok">${yes_label || "Ok"}</button>
                            </div>
                        </div>
                    </div>
                </div>`);

                $('body').on("click", `#${dialog_id}_confirm_ok`, function () {
                    var dialog_id = $(this).attr('data-dialog-id')
                    invoke_cb(yes_cb, [dialog_id]);
                })

                $('body').on("click", `#${dialog_id}_confirm_cancel`, function () {
                    var dialog_id = $(this).attr('data-dialog-id')
                    invoke_cb(no_cb, [dialog_id]);
                })

                var el = document.getElementById(dialog_id);
                var prompt = new bootstrap.Modal(el, {
                    backdrop: 'static',
                    focus: true,
                    keyboard: true
                });

                el.addEventListener('hidden.bs.modal', function (event) {
                    $(event.target).remove()
                })
                prompt.show();
                return dialog_id;
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
                var type_map = {input: 0, password: 1, textarea: 2};
                if (!type_map[type]) {
                    type = "input";
                }
                var input = {
                    input: `<input type="text" class="form-control ydjs-prompt-value" value="${defaultValue||''}">`,
                    textarea: `<textarea class="form-control ydjs-prompt-value">${defaultValue||''}</textarea>`,
                    password: `<input type="password ydjs-prompt-value" class="form-control" value="${defaultValue||''}">`
                }
                dialog_id = dialog_id || YDJS.uuid(16, 16, "prompt-"); //加载框的id

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
                </div>`);


                $('body').on("click", `#${dialog_id}_prompt_ok`, function () {
                    var dialog_id = $(this).attr('data-dialog-id')
                    invoke_cb(cb, [dialog_id, $(`#${dialog_id} .ydjs-prompt-value`).val()]);
                })

                var el = document.getElementById(dialog_id);
                var prompt = new bootstrap.Modal(el, {
                    backdrop: 'static',
                    focus: true,
                    keyboard: true
                });

                el.addEventListener('hidden.bs.modal', function (event) {
                    $(event.target).remove()
                })
                prompt.show();
                return dialog_id;

            },
            /**
             * 重新绑定事件
             * @returns {undefined}
             */
            rebind: function () {

                $(".yd-date-picker").each(function (idx, el) {

                    if ($(el).attr("lay-key"))
                        return;// 避免重复绑定
                    var range = $(el).attr("data-range");
                    var format = $(el).attr("data-format");//yyyy-MM-dd hh:mm
                    var type = $(el).attr("data-type");
                    var min = $(el).attr("data-picker-min");
                    var max = $(el).attr("data-picker-max");
                    var args = {
                        elem: el
                        , trigger: "click"
                        , range: range || false //或 range: '~' 来自定义分割字符
                        , done: function (value, date, endDate) {
                            //                    console.log(value); //得到日期生成的值，如：2017-08-18
                            //                    console.log(date); //得到日期时间对象：{year: 2017, month: 8, date: 18, hours: 0, minutes: 0, seconds: 0}
                            //                    console.log(endDate); //得结束的日期时间对象，开启范围选择（range: true）才会返回。对象成员同上。
                            $(el).change();//触发input的change时间
                        }
                    };
                    if (format) {
                        args["format"] = format;
                    }
                    if (type) {
                        args["type"] = type;
                    }
                    if (min) {
                        args["min"] = min;
                    }
                    if (max) {
                        args["max"] = max;
                    }

                    $(el).attr(":data-date-picker", "date-picker");
                });

                $(".yd-tooltip").hover(function () {
                    var content = $(this).attr("data-tooltip") || $(this).text();
                    var background = $(this).attr("data-tooltip-background") || "#000000";
                    window["subtips"] = layer.tips(content, this, {tips: [1, background], time: 30000});
                }, function () {
                    layer.close(window["subtips"]);
                });

                //富文本编辑器
                $(".yd-editor").each(function (idx, el) {
                    if (!window["ydeditor"])
                        window["ydeditor"] = {};

                    var editorid = $(el).attr("id") || YDJS.uuid(16, 16, "editor-");


                    if (window["ydeditor"][editorid])
                        return;//已经实例化过了


                    $(el).prop("id", editorid);

                    window.onbeforeunload = function () {
                        if (CKEDITOR.instances[editorid] && CKEDITOR.instances[editorid].checkDirty()) {
                            return "页面上包含尚未保存的表单内容。如果离开此页，未保存的内容将被丢弃。";/*You have made changes on this page that you have not yet confirmed. If you navigate away from this page you will lose your unsaved changes*/
                        }
                    };

                    var browseUrl = $(el).attr("data-browser-url");
                    var uploadUrl = $(el).attr("data-upload-url");
                    var config = $(el).attr("data-config-url");
                    var height = $(el).attr("data-height");
                    var isInline = $(el).attr("data-isinline") == 1;


                    var setting = {
                        customConfig: config,
                        height: height || 300
                    };

                    if (browseUrl) {
                        setting["filebrowserBrowseUrl"] = browseUrl;
                    }

                    if (uploadUrl) {
                        setting["filebrowserUploadUrl"] = uploadUrl;
                    }

                    if (isInline) {
                        window["ydeditor"][editorid] = CKEDITOR.inline(editorid, setting);
                    } else {
                        window["ydeditor"][editorid] = CKEDITOR.replace(editorid, setting);
                    }

                });

                //拖动排序
                $(".yd-sortable").each(function (idx, el) {
                    var stopCb = $(el).attr("data-sortable-stop");
                    var helperCb = $(el).attr("data-sortable-helper") || "original";
                    var placeholder = $(el).attr("data-sortable-placeholder");
                    var handle = $(el).attr("data-sortable-handler");
                    $(el).sortable({
                        helper: window[helperCb] || helperCb,
                        stop: window[stopCb] || null,
                        handle: handle || false,
                        placeholder: placeholder || ""
                    });
                });

                //自动弹出的提示
                $(".yd-auto-tip").each(function (index, el) {
                    var content = $(el).attr("data-tip-content");
                });


                yd_tree_select_render();
                yd_tree_render();
                yd_dynamic_select_render();
                yd_upload_render();


            },
            urlencode: function (clearString) {
                var output = '';
                var x = 0;

                clearString = utf16to8(clearString.toString());
                var regex = /(^[a-zA-Z0-9-_.]*)/;

                while (x < clearString.length) {
                    var match = regex.exec(clearString.substr(x));
                    if (match != null && match.length > 1 && match[1] != '') {
                        output += match[1];
                        x += match[1].length;
                    } else {
                        if (clearString[x] == ' ')
                            output += '+';
                        else {
                            var charCode = clearString.charCodeAt(x);
                            var hexVal = charCode.toString(16);
                            output += '%' + (hexVal.length < 2 ? '0' : '') + hexVal.toUpperCase();
                        }
                        x++;
                    }
                }

                function utf16to8(str) {
                    var out, i, len, c;

                    out = "";
                    len = str.length;
                    for (i = 0; i < len; i++) {
                        c = str.charCodeAt(i);
                        if ((c >= 0x0001) && (c <= 0x007F)) {
                            out += str.charAt(i);
                        } else if (c > 0x07FF) {
                            out += String.fromCharCode(0xE0 | ((c >> 12) & 0x0F));
                            out += String.fromCharCode(0x80 | ((c >> 6) & 0x3F));
                            out += String.fromCharCode(0x80 | ((c >> 0) & 0x3F));
                        } else {
                            out += String.fromCharCode(0xC0 | ((c >> 6) & 0x1F));
                            out += String.fromCharCode(0x80 | ((c >> 0) & 0x3F));
                        }
                    }
                    return out;
                }

                return output;
            },
            urldecode: function (encodedString) {
                var output = encodedString;
                var binVal, thisString;
                var myregexp = /(%[^%]{2})/;

                function utf8to16(str) {
                    var out, i, len, c;
                    var char2, char3;

                    out = "";
                    len = str.length;
                    i = 0;
                    while (i < len) {
                        c = str.charCodeAt(i++);
                        switch (c >> 4) {
                            case 0:
                            case 1:
                            case 2:
                            case 3:
                            case 4:
                            case 5:
                            case 6:
                            case 7:
                                out += str.charAt(i - 1);
                                break;
                            case 12:
                            case 13:
                                char2 = str.charCodeAt(i++);
                                out += String.fromCharCode(((c & 0x1F) << 6) | (char2 & 0x3F));
                                break;
                            case 14:
                                char2 = str.charCodeAt(i++);
                                char3 = str.charCodeAt(i++);
                                out += String.fromCharCode(((c & 0x0F) << 12) |
                                    ((char2 & 0x3F) << 6) |
                                    ((char3 & 0x3F) << 0));
                                break;
                        }
                    }
                    return out;
                }

                while ((match = myregexp.exec(output)) != null
                && match.length > 1
                && match[1] != '') {
                    binVal = parseInt(match[1].substr(1), 16);
                    thisString = String.fromCharCode(binVal);
                    output = output.replace(match[1], thisString);
                }

                //output = utf8to16(output);
                output = output.replace(/\\+/g, " ");
                output = utf8to16(output);
                return output;
            },
            /**
             *
             * @param {type} len 长度
             * @param {type} radix 进制
             * @returns {String}
             */
            uuid: function (len, radix, prefix) {
                var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.split('');
                radix = radix || chars.length;
                var uuid = [];
                if (len) {
                    // Compact form
                    for (i = 0; i < len; i++)
                        uuid[i] = chars[0 | Math.random() * radix];
                } else {
                    // rfc4122, version 4 form
                    var r;
                    // rfc4122 requires these characters
                    uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
                    uuid[14] = '4';
                    // Fill in random data. At i==19 set the high bits of clock sequence as
                    // per rfc4122, sec. 4.1.5
                    for (i = 0; i < 36; i++) {
                        if (!uuid[i]) {
                            r = 0 | Math.random() * 16;
                            uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
                        }
                    }
                }

                return (prefix || "") + uuid.join('');
            }
        }

        if (typeof YDJS.event_bind !== 'function') {
            /**
             * jquery on的封装, callback事件只会绑定一次[TODO 并未实现只绑定一次]
             * @param {type} event
             * @param {type} selector
             * @param {type} callback
             * @returns {undefined}
             */
            YDJS.event_bind = function (event, selector, callback) {
                $("html").off(event, selector, callback);
                //callback 作为event.data传入
                $("html").on(event, selector, callback);
            };
        }
    }
}());

// CSS风格动态绑定
$(function () {
    function invoke_cb(cb, args) {
        if (typeof cb === "function") {
            cb.apply(this, args);
        } else if (window[cb]) {
            window[cb].apply(this, args);
        }
    }

    YDJS.event_bind("click", "[yd-link-ignore]", function (e) {
        if (e && e.stopPropagation)
            e.stopPropagation();
        else
            window.event.cancelBubble = true;

    });

    //yd-auto-tip
    YDJS.event_bind("click", ".yd-remove-self", function () {
        var data_remove_from = $(this).attr("data-remove-from");
        if (data_remove_from) {
            $(this).parents(data_remove_from).remove();
        } else {
            $(this).remove();
        }
    });


    //popper 弹出框处理 leeboo 20190917
    YDJS.event_bind("mouseover", ".yd-popper-trigger", function () {
        if (window["ydpoper"]) {
            $(window["ydpoper"].popper).hide();
            window["ydpoper"].destroy();
        }
        if (!$(this).attr("data-popper-target"))
            return;
        var reference = this;
        var popper = document.querySelector('#' + $(this).attr("data-popper-target"));
        $(popper).show();
        var popperInstance = new Popper(reference, popper, {
            placement: $(this).attr("data-popper-position"),
            positionFixed: true,
            modifiers: {
                offset: {offset: "0px,-5px"},
                preventOverflow: {enabled: false}
            }
        });
        window["ydpoper"] = popperInstance;
        $(window["ydpoper"].popper).on("mouseout", function (event) {
            if ($(event.toElement).parents(".yd-popper-content").length > 0)
                return;
            $(window["ydpoper"].popper).hide();
            window["ydpoper"].destroy();
        });
    });

    //点击切换目标元素的指定样式
    YDJS.event_bind("click", ".yd-toggle-class", function () {
        var target = $(this).attr("data-toggle-target");
        var cb = $(this).attr("data-toggle-callback");
        if (!target)
            return;
        $(target).toggleClass($(this).attr("data-toggle-class"));
        invoke_cb(cb, [$(this), $(target).hasClass($(this).attr("data-toggle-class"))]);
    });

    //鼠标悬浮时切换目标元素的指定样式
    YDJS.event_bind("mouseover", ".yd-mousetoggle-class", function () {
        var target = $(this).attr("data-mousetoggle-target");
        var cb = $(this).attr("data-mousetoggle-callback");
        if (!target)
            return;
        $(target).addClass($(this).attr("data-mousetoggle-class"));
        invoke_cb(cb, [$(this), $(target).hasClass($(this).attr("data-mousetoggle-class"))]);
    });

    YDJS.event_bind("mouseout", ".yd-mousetoggle-class", function () {
        var target = $(this).attr("data-mousetoggle-target");
        var cb = $(this).attr("data-mousetoggle-callback");
        if (!target)
            return;
        $(target).removeClass($(this).attr("data-mousetoggle-class"));
        invoke_cb(cb, [$(this), $(target).hasClass($(this).attr("data-mousetoggle-class"))]);
    });

    YDJS.event_bind("click", ".yd-link", function () {
        var url = $(this).attr("data-url");
        var intop = $(this).attr("data-in-top");
        var _window = intop ? top.window : window;
        if ($(this).attr("data-target") == "_blank") {
            _window.open(url);
        } else {
            _window.location.href = url;
        }
    });

    //点击后显示提示框
    YDJS.event_bind("click", ".yd-toast", function () {
        var msg = $(this).attr("data-msg");
        var icon = $(this).attr("data-icon");
        var backdrop = $(this).attr("data-backdrop");
        var delay = parseInt($(this).attr("data-delay")) || 0;
        var position = $(this).attr("data-position");
        YDJS.toast(msg, icon, null, backdrop, delay, position);
    });

    //yd-loading加载框
    YDJS.event_bind("click", ".yd-loading", function () {
        var msg = $(this).attr("data-msg");
        var loadingId = $(this).attr("data-loading-id");

        YDJS.loading(msg, loadingId);
    });

    //打开对话框
    YDJS.event_bind("click", ".yd-dialog", function () {
        var url = $(this).attr("data-url");   //要打开的网址
        var content = $(this).attr("data-content");     //内容
        var content_ref = $(this).attr("data-content-ref");     //内容dom的选择器
        var title = $(this).attr("data-title");     //标题
        var primary_button_label = $(this).attr("data-primary-button-label");   //主要按钮
        var secondary_button_label = $(this).attr("data-secondary-button-label");    //次要按钮
        var primary_button_click = $(this).attr("data-primary-button-click");//主要按钮的文字点击的事件名，只传入dialogId
        var secondary_button_click = $(this).attr("data-secondary-button-click");//次要按钮的文字点击的时间名，只传入dialogId
        var dialog_id = $(this).attr("data-dialog-id")
        var size = $(this).attr("data-size");
        var backdrop = $(this).attr("data-backdrop");
        var intop = $(this).attr("data-in-top");
        var buttonAndCallbacks = [];

        if (primary_button_label) {
            buttonAndCallbacks.push({"label": primary_button_label, "cb": primary_button_click});
        }
        if (secondary_button_label) {
            buttonAndCallbacks.push({"label": secondary_button_label, "cb": secondary_button_click});
        }

        if(content_ref){
            content = $(content_ref).html();
        }

        var _ydjs = intop ? top.YDJS : YDJS;
        _ydjs.dialog(url, content, title, size, backdrop, buttonAndCallbacks, dialog_id);

    });


    //确认对话框
    YDJS.event_bind("click", ".yd-confirm", function () {
        var content = $(this).attr("data-content");     //内容
        var title = $(this).attr("data-title") || "提示";     //标题
        var icon = $(this).attr("data-icon")
        var primary_button_label = $(this).attr("data-primary-button-label");   //主要按钮
        var secondary_button_label = $(this).attr("data-secondary-button-label");    //次要按钮
        var primary_button_click = $(this).attr("data-primary-button-click");//主要按钮的文字点击的事件名，只传入dialogId
        var secondary_button_click = $(this).attr("data-secondary-button-click");//次要按钮的文字点击的时间名，只传入dialogId
        var dialog_id = $(this).attr("data-dialog-id");

        YDJS.confirm(content, title, primary_button_click, secondary_button_click, primary_button_label, secondary_button_label,icon, dialog_id);

    });

    //确认对话框, 确认后ajax请求某网址，
    YDJS.event_bind("click", ".yd-confirm-post", function () {
        var btn = this;
        var content = $(this).attr("data-content");     //内容
        var title = $(this).attr("data-title") || "";     //标题
        var icon = $(this).attr("data-icon");
        var post_url = $(this).attr("data-url");   //请求api
        var redirect = $(this).attr("data-redirect");   //接口成功后重定向地址,可以是一个具体的网址，也可以reload，表示重载页面, 也可以是一个村子的函数
        var intop = $(this).attr("data-in-top");   //接口成功后重定向地址
        var args = $(this).attr("data-args");   //请求参数
        var primary_button_label = $(this).attr("data-primary-button-label");   //主要按钮
        var secondary_button_label = $(this).attr("data-secondary-button-label");    //次要按钮
        var dialog_id = $(this).attr("data-dialog-id") || YDJS.uuid(16, 16, "confirm-post-");
        args = args ? JSON.parse(args) : {};

        var _ydjs = intop ? top.YDJS : YDJS;
        _ydjs.confirm(content, title, function(){
            _ydjs.spin_clear($(btn));
            $.post(post_url,  args , function (rst) {
                _ydjs.spin_clear($(`#${dialog_id}_confirm_ok`));
                if(rst.success){
                    if(redirect){
                        var _window = intop ? top.window : window;
                        if(redirect=="reload"){
                            _window.location.reload();
                        }else if(_window[redirect]){
                            invoke_cb(redirect, [dialog_id]);
                        }else{
                            _window.location.href = redirect;
                        }
                    }else{
                        top.YDJS.toast("处理成功", YDJS.ICON_SUCCESS);
                    }
                }else {
                    top.YDJS.toast(rst.msg || "接口请求识别", YDJS.ICON_ERROR);
                }
            })
        },null, primary_button_label,secondary_button_label, icon, dialog_id);

    });

    //输入提示对话框
    YDJS.event_bind("click", ".yd-prompt", function () {
        var title = $(this).attr("data-title") || "提示";     //标题
        var defaultValue = $(this).attr("data-default-value");   //默认内容
        var type = $(this).attr("data-type");    //input password textarea
        var cb = $(this).attr("data-prompt-cb");//确认回调
        var dialog_id = $(this).attr("data-dialog-id");

        YDJS.prompt(title, defaultValue, type, cb, dialog_id);
    });

    //yd-remove-self
    YDJS.event_bind("click", ".yd-remove-self", function () {
        var data_remove_from = $(this).attr("data-remove-from");
        if (data_remove_from) {
            $(this).parents(data_remove_from).remove();
        } else {
            $(this).remove();
        }
    });


    //表单提交
    YDJS.event_bind("click", ".yd-form-submit", function () {
        var url = $(this).attr("data-url") || $(this).parents("form").attr("action");
        var redirect = $(this).attr("data-redirect");
        var cb = $(this).attr("data-submit-cb");
        var before = $(this).attr("data-before-submit");
        var msg = $(this).attr("data-confirm-msg");
        var intop = $(this).attr("data-in-top");   //接口成功后重定向地址
        var _ydjs = intop ? top.YDJS : YDJS;
        var _window = intop ? top.window : window;
        var self = this;

        var form = $(this).parents("form");
        // 禁用form的submit事件
        form.unbind("submit");
        form.submit(function(){
            return false;
        });
        if(before){
            invoke_cb(before,[]);
        }

        var formData = new FormData(form.get(0));
        // 处理上传文件
        var uploadFiles = form.find("input[type='file']");
        if (uploadFiles.length > 0){
            for (var i = 0; i<uploadFiles.length; i++) {
                formData.append($(uploadFiles[i]).attr('name'), $(uploadFiles[i])[0].files[0])
            }
        }

        var post = function(){
            $.post(url, $(self).parents("form").serialize(), function (rst) {
                _ydjs.spin_clear(self);

                if (rst.success) {
                    _ydjs.toast("操作成功", YDJS.ICON_SUCCESS, function () {
                        if (redirect) {
                            if(redirect=="reload"){
                                _window.location.reload();
                            }else{
                                _window.location.href = redirect;
                            }
                        }else if(cb){
                            invoke_cb(cb, [rst]);
                        }
                    });
                } else {
                    _ydjs.toast(rst.msg || "保存失败", YDJS.ICON_ERROR);
                }
            }, "json");
        };

        if (msg){
            top.YDJS.confirm(msg, $(self).text(),function (dialogid) {
                _ydjs.hide_dialog(dialogid);
                post();
            },function (dialogid) {
                _ydjs.hide_dialog(dialogid);
                _ydjs.spin_clear(self);
            });
        }else{
            post();
        }

    });
    //yd-spin-button提交按钮
    YDJS.event_bind("click", ".yd-spin-btn", function () {
        var text = YDJS.urlencode( $(this).html() );
        var icon = '&nbsp;<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="visually-hidden">Loading...</span>&nbsp;';
        $(this).prop("disabled", true);
        $(this).attr("data-btn-text", text);
        $(this).html(icon);
        return true;
    });

    YDJS.event_bind("click", ".yd-image-preview,.yd-image-preview img", function () {
        var url = $(this).attr("src");
        if ( ! url)return;
        var intop = $(this).attr("data-in-top");
        console.log('not implement');
    });

    YDJS.rebind()
});
