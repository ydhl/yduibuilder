/*! ydecloud v0.0.1 */
if (!this.YDECloud) {
    this.YDECloud = {};
}
( function() {
    /**
     *
     * @param args
     * @return {[]}
     */
    function formatArrayData(args){
        const data = []
        for (const key in args) {
            data.push(key + '=' + args[key])
        }
        return data
    }

    if (typeof YDECloud.getPathArgValue !== 'function') {
        /**
         * 在当前请求路径上获取指定路径参数名的值
         * @param name string
         * @return string
         */
        YDECloud.getPathArgValue = function(name, path = null) {
            // TODO
        }
    }
    if (typeof YDECloud.getQueryValue !== 'function') {
        /**
         * 在当前请求参数上获取指定路径参数名的值
         * @param name string
         * @param queryString string 在加载modal弹窗时，query value不是documet上的地址参数，而是加载弹窗时的地址参数，这时需要通过该参数指定，
         * @return string
         */
        YDECloud.getQueryValue = function(name, queryString=null) {
            let myQueryString
            if (queryString) {
                const url = new URL(queryString)
                myQueryString = url.search
            }
            const params = new URLSearchParams(myQueryString || document.location.search);
            const keyValues = Object.fromEntries(params.entries())
            // console.log(keyValues)
            return keyValues[name];
        }
    }
    if (typeof YDECloud.setCookie !== 'function') {
        /**
         * 处理cookie数据
         * @param cookieArgs Object
         * @return {{}}
         */
        YDECloud.setCookie = function(cookieArgs) {
            const cookies = formatArrayData(cookieArgs)
            for(const item of cookies) {
                document.cookie = `${item}; path=/; SameSite=None; Secure`
            }
        }
    }
    if (typeof YDECloud.isEmptyObject !== 'function') {

        /**
         * 判断对象是否是空对象
         * @param args Object
         * @return boolean
         */
        YDECloud.isEmptyObject = function(args) {
            if (!args) return false
            for (const t in args) {
                return !1
            }
            return !0
        }
    }
    if (typeof YDECloud.buildQuery !== 'function'){
        YDECloud.buildQuery = function (obj) {
            const pairs = [];
            for (const key in obj) {
                if (obj.hasOwnProperty(key)) {
                    pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(obj[key]));
                }
            }
            return pairs.join('&');
        }
    }
    if (typeof YDECloud.loadUrl !== 'function'){
        YDECloud.loadUrl = function (url, appendToId) {
            return new Promise((resolve) => {
                $.get(url, function (html) {
                    const scripts = []
                    const other = []
                    for(let i=0; i< $(html).length; i++){
                        const node = $(html).get(i);
                        if (node.nodeName=='SCRIPT'){
                            scripts.push(node)
                        }else{
                            other.push(node)
                        }
                    }
                    $('body').append(scripts)
                    // 把script单独提出来，先执行，延迟后在显示dom，避免其中绑定的x-text等数据提示不存在
                    setTimeout(() => {
                        $(appendToId).append(other)
                        resolve()
                    }, 300)
                })
            })
        }
    }
    if (typeof YDECloud.loadSubpages !== 'function'){
        YDECloud.loadSubpages = function (el2SubpageId) {
            if (!el2SubpageId) return
            const promises = [];
            for (const elId in el2SubpageId) {
                promises.push(YDECloud.loadUrl(el2SubpageId[elId], '#' + elId))
            }
            Promise.all(promises).then(() =>{ })
        }
    }
    if (typeof YDECloud.md5 !== 'function') {
        YDECloud.md5 = function (string) {

            /*
            * Add integers, wrapping at 2^32. This uses 16-bit operations internally
            * to work around bugs in some JS interpreters.
            */
            function safeAdd(x, y) {
                var lsw = (x & 0xffff) + (y & 0xffff)
                var msw = (x >> 16) + (y >> 16) + (lsw >> 16)
                return (msw << 16) | (lsw & 0xffff)
            }

            /*
            * Bitwise rotate a 32-bit number to the left.
            */
            function bitRotateLeft(num, cnt) {
                return (num << cnt) | (num >>> (32 - cnt))
            }

            /*
            * These functions implement the four basic operations the algorithm uses.
            */
            function md5cmn(q, a, b, x, s, t) {
                return safeAdd(bitRotateLeft(safeAdd(safeAdd(a, q), safeAdd(x, t)), s), b)
            }

            function md5ff(a, b, c, d, x, s, t) {
                return md5cmn((b & c) | (~b & d), a, b, x, s, t)
            }

            function md5gg(a, b, c, d, x, s, t) {
                return md5cmn((b & d) | (c & ~d), a, b, x, s, t)
            }

            function md5hh(a, b, c, d, x, s, t) {
                return md5cmn(b ^ c ^ d, a, b, x, s, t)
            }

            function md5ii(a, b, c, d, x, s, t) {
                return md5cmn(c ^ (b | ~d), a, b, x, s, t)
            }

            /*
            * Calculate the MD5 of an array of little-endian words, and a bit length.
            */
            function binlMD5(x, len) {
                /* append padding */
                x[len >> 5] |= 0x80 << (len % 32)
                x[((len + 64) >>> 9 << 4) + 14] = len

                var i
                var olda
                var oldb
                var oldc
                var oldd
                var a = 1732584193
                var b = -271733879
                var c = -1732584194
                var d = 271733878

                for (i = 0; i < x.length; i += 16) {
                    olda = a
                    oldb = b
                    oldc = c
                    oldd = d

                    a = md5ff(a, b, c, d, x[i], 7, -680876936)
                    d = md5ff(d, a, b, c, x[i + 1], 12, -389564586)
                    c = md5ff(c, d, a, b, x[i + 2], 17, 606105819)
                    b = md5ff(b, c, d, a, x[i + 3], 22, -1044525330)
                    a = md5ff(a, b, c, d, x[i + 4], 7, -176418897)
                    d = md5ff(d, a, b, c, x[i + 5], 12, 1200080426)
                    c = md5ff(c, d, a, b, x[i + 6], 17, -1473231341)
                    b = md5ff(b, c, d, a, x[i + 7], 22, -45705983)
                    a = md5ff(a, b, c, d, x[i + 8], 7, 1770035416)
                    d = md5ff(d, a, b, c, x[i + 9], 12, -1958414417)
                    c = md5ff(c, d, a, b, x[i + 10], 17, -42063)
                    b = md5ff(b, c, d, a, x[i + 11], 22, -1990404162)
                    a = md5ff(a, b, c, d, x[i + 12], 7, 1804603682)
                    d = md5ff(d, a, b, c, x[i + 13], 12, -40341101)
                    c = md5ff(c, d, a, b, x[i + 14], 17, -1502002290)
                    b = md5ff(b, c, d, a, x[i + 15], 22, 1236535329)

                    a = md5gg(a, b, c, d, x[i + 1], 5, -165796510)
                    d = md5gg(d, a, b, c, x[i + 6], 9, -1069501632)
                    c = md5gg(c, d, a, b, x[i + 11], 14, 643717713)
                    b = md5gg(b, c, d, a, x[i], 20, -373897302)
                    a = md5gg(a, b, c, d, x[i + 5], 5, -701558691)
                    d = md5gg(d, a, b, c, x[i + 10], 9, 38016083)
                    c = md5gg(c, d, a, b, x[i + 15], 14, -660478335)
                    b = md5gg(b, c, d, a, x[i + 4], 20, -405537848)
                    a = md5gg(a, b, c, d, x[i + 9], 5, 568446438)
                    d = md5gg(d, a, b, c, x[i + 14], 9, -1019803690)
                    c = md5gg(c, d, a, b, x[i + 3], 14, -187363961)
                    b = md5gg(b, c, d, a, x[i + 8], 20, 1163531501)
                    a = md5gg(a, b, c, d, x[i + 13], 5, -1444681467)
                    d = md5gg(d, a, b, c, x[i + 2], 9, -51403784)
                    c = md5gg(c, d, a, b, x[i + 7], 14, 1735328473)
                    b = md5gg(b, c, d, a, x[i + 12], 20, -1926607734)

                    a = md5hh(a, b, c, d, x[i + 5], 4, -378558)
                    d = md5hh(d, a, b, c, x[i + 8], 11, -2022574463)
                    c = md5hh(c, d, a, b, x[i + 11], 16, 1839030562)
                    b = md5hh(b, c, d, a, x[i + 14], 23, -35309556)
                    a = md5hh(a, b, c, d, x[i + 1], 4, -1530992060)
                    d = md5hh(d, a, b, c, x[i + 4], 11, 1272893353)
                    c = md5hh(c, d, a, b, x[i + 7], 16, -155497632)
                    b = md5hh(b, c, d, a, x[i + 10], 23, -1094730640)
                    a = md5hh(a, b, c, d, x[i + 13], 4, 681279174)
                    d = md5hh(d, a, b, c, x[i], 11, -358537222)
                    c = md5hh(c, d, a, b, x[i + 3], 16, -722521979)
                    b = md5hh(b, c, d, a, x[i + 6], 23, 76029189)
                    a = md5hh(a, b, c, d, x[i + 9], 4, -640364487)
                    d = md5hh(d, a, b, c, x[i + 12], 11, -421815835)
                    c = md5hh(c, d, a, b, x[i + 15], 16, 530742520)
                    b = md5hh(b, c, d, a, x[i + 2], 23, -995338651)

                    a = md5ii(a, b, c, d, x[i], 6, -198630844)
                    d = md5ii(d, a, b, c, x[i + 7], 10, 1126891415)
                    c = md5ii(c, d, a, b, x[i + 14], 15, -1416354905)
                    b = md5ii(b, c, d, a, x[i + 5], 21, -57434055)
                    a = md5ii(a, b, c, d, x[i + 12], 6, 1700485571)
                    d = md5ii(d, a, b, c, x[i + 3], 10, -1894986606)
                    c = md5ii(c, d, a, b, x[i + 10], 15, -1051523)
                    b = md5ii(b, c, d, a, x[i + 1], 21, -2054922799)
                    a = md5ii(a, b, c, d, x[i + 8], 6, 1873313359)
                    d = md5ii(d, a, b, c, x[i + 15], 10, -30611744)
                    c = md5ii(c, d, a, b, x[i + 6], 15, -1560198380)
                    b = md5ii(b, c, d, a, x[i + 13], 21, 1309151649)
                    a = md5ii(a, b, c, d, x[i + 4], 6, -145523070)
                    d = md5ii(d, a, b, c, x[i + 11], 10, -1120210379)
                    c = md5ii(c, d, a, b, x[i + 2], 15, 718787259)
                    b = md5ii(b, c, d, a, x[i + 9], 21, -343485551)

                    a = safeAdd(a, olda)
                    b = safeAdd(b, oldb)
                    c = safeAdd(c, oldc)
                    d = safeAdd(d, oldd)
                }
                return [a, b, c, d]
            }

            /*
            * Convert an array of little-endian words to a string
            */
            function binl2rstr(input) {
                var i
                var output = ''
                var length32 = input.length * 32
                for (i = 0; i < length32; i += 8) {
                    output += String.fromCharCode((input[i >> 5] >>> (i % 32)) & 0xff)
                }
                return output
            }

            /*
            * Convert a raw string to an array of little-endian words
            * Characters >255 have their high-byte silently ignored.
            */
            function rstr2binl(input) {
                var i
                var output = []
                output[(input.length >> 2) - 1] = undefined
                for (i = 0; i < output.length; i += 1) {
                    output[i] = 0
                }
                var length8 = input.length * 8
                for (i = 0; i < length8; i += 8) {
                    output[i >> 5] |= (input.charCodeAt(i / 8) & 0xff) << (i % 32)
                }
                return output
            }

            /*
            * Calculate the MD5 of a raw string
            */
            function rstrMD5(s) {
                return binl2rstr(binlMD5(rstr2binl(s), s.length * 8))
            }

            /*
            * Convert a raw string to a hex string
            */
            function rstr2hex(input) {
                var hexTab = '0123456789abcdef'
                var output = ''
                var x
                var i
                for (i = 0; i < input.length; i += 1) {
                    x = input.charCodeAt(i)
                    output += hexTab.charAt((x >>> 4) & 0x0f) + hexTab.charAt(x & 0x0f)
                }
                return output
            }

            /*
            * Encode a string as utf-8
            */
            function str2rstrUTF8(input) {
                return unescape(encodeURIComponent(input))
            }

            /*
            * Take string arguments and return either raw or hex encoded strings
            */
            function rawMD5(s) {
                return rstrMD5(str2rstrUTF8(s))
            }

            function hexMD5(s) {
                return rstr2hex(rawMD5(s))
            }

            return hexMD5(string)
        }
    }
}())
