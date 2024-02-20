/*
 * JavaScript MD5
 * https://github.com/blueimp/JavaScript-MD5
 *
 * Copyright 2011, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 *
 * Based on
 * A JavaScript implementation of the RSA Data Security, Inc. MD5 Message
 * Digest Algorithm, as defined in RFC 1321.
 * Version 2.2 Copyright (C) Paul Johnston 1999 - 2009
 * Other contributors: Greg Holt, Andrew Kepert, Ydnar, Lostinet
 * Distributed under the BSD License
 * See http://pajhome.org.uk/crypt/md5 for more info.
 */

/* global define */
;(function ($) {
	'use strict'

	/*
    * Add integers, wrapping at 2^32. This uses 16-bit operations internally
    * to work around bugs in some JS interpreters.
    */
	function safeAdd (x, y) {
		var lsw = (x & 0xffff) + (y & 0xffff)
		var msw = (x >> 16) + (y >> 16) + (lsw >> 16)
		return (msw << 16) | (lsw & 0xffff)
	}

	/*
    * Bitwise rotate a 32-bit number to the left.
    */
	function bitRotateLeft (num, cnt) {
		return (num << cnt) | (num >>> (32 - cnt))
	}

	/*
    * These functions implement the four basic operations the algorithm uses.
    */
	function md5cmn (q, a, b, x, s, t) {
		return safeAdd(bitRotateLeft(safeAdd(safeAdd(a, q), safeAdd(x, t)), s), b)
	}
	function md5ff (a, b, c, d, x, s, t) {
		return md5cmn((b & c) | (~b & d), a, b, x, s, t)
	}
	function md5gg (a, b, c, d, x, s, t) {
		return md5cmn((b & d) | (c & ~d), a, b, x, s, t)
	}
	function md5hh (a, b, c, d, x, s, t) {
		return md5cmn(b ^ c ^ d, a, b, x, s, t)
	}
	function md5ii (a, b, c, d, x, s, t) {
		return md5cmn(c ^ (b | ~d), a, b, x, s, t)
	}

	/*
    * Calculate the MD5 of an array of little-endian words, and a bit length.
    */
	function binlMD5 (x, len) {
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
	function binl2rstr (input) {
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
	function rstr2binl (input) {
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
	function rstrMD5 (s) {
		return binl2rstr(binlMD5(rstr2binl(s), s.length * 8))
	}

	/*
    * Calculate the HMAC-MD5, of a key and some data (raw strings)
    */
	function rstrHMACMD5 (key, data) {
		var i
		var bkey = rstr2binl(key)
		var ipad = []
		var opad = []
		var hash
		ipad[15] = opad[15] = undefined
		if (bkey.length > 16) {
			bkey = binlMD5(bkey, key.length * 8)
		}
		for (i = 0; i < 16; i += 1) {
			ipad[i] = bkey[i] ^ 0x36363636
			opad[i] = bkey[i] ^ 0x5c5c5c5c
		}
		hash = binlMD5(ipad.concat(rstr2binl(data)), 512 + data.length * 8)
		return binl2rstr(binlMD5(opad.concat(hash), 512 + 128))
	}

	/*
    * Convert a raw string to a hex string
    */
	function rstr2hex (input) {
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
	function str2rstrUTF8 (input) {
		return unescape(encodeURIComponent(input))
	}

	/*
    * Take string arguments and return either raw or hex encoded strings
    */
	function rawMD5 (s) {
		return rstrMD5(str2rstrUTF8(s))
	}
	function hexMD5 (s) {
		return rstr2hex(rawMD5(s))
	}
	function rawHMACMD5 (k, d) {
		return rstrHMACMD5(str2rstrUTF8(k), str2rstrUTF8(d))
	}
	function hexHMACMD5 (k, d) {
		return rstr2hex(rawHMACMD5(k, d))
	}

	function md5 (string, key, raw) {
		if (!key) {
			if (!raw) {
				return hexMD5(string)
			}
			return rawMD5(string)
		}
		if (!raw) {
			return hexHMACMD5(key, string)
		}
		return rawHMACMD5(key, string)
	}

	if (typeof define === 'function' && define.amd) {
		define(function () {
			return md5
		})
	} else if (typeof module === 'object' && module.exports) {
		module.exports = md5
	} else {
		$.md5 = md5
	}
})(this)


/*
    http://www.JSON.org/json2.js
    2009-09-29

    Public Domain.

    NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.

    See http://www.JSON.org/js.html

    This file creates a global JSON object containing two methods: stringify
    and parse.

        JSON.stringify(value, replacer, space)
            value       any JavaScript value, usually an object or array.

            replacer    an optional parameter that determines how object
                        values are stringified for objects. It can be a
                        function or an array of strings.

            space       an optional parameter that specifies the indentation
                        of nested structures. If it is omitted, the text will
                        be packed without extra whitespace. If it is a number,
                        it will specify the number of spaces to indent at each
                        level. If it is a string (such as '\t' or '&nbsp;'),
                        it contains the characters used to indent at each level.

            This method produces a JSON text from a JavaScript value.

            When an object value is found, if the object contains a toJSON
            method, its toJSON method will be called and the result will be
            stringified. A toJSON method does not serialize: it returns the
            value represented by the name/value pair that should be serialized,
            or undefined if nothing should be serialized. The toJSON method
            will be passed the key associated with the value, and this will be
            bound to the value

            For example, this would serialize Dates as ISO strings.

                Date.prototype.toJSON = function (key) {
                    function f(n) {
                        // Format integers to have at least two digits.
                        return n < 10 ? '0' + n : n;
                    }

                    return this.getUTCFullYear()   + '-' +
                         f(this.getUTCMonth() + 1) + '-' +
                         f(this.getUTCDate())      + 'T' +
                         f(this.getUTCHours())     + ':' +
                         f(this.getUTCMinutes())   + ':' +
                         f(this.getUTCSeconds())   + 'Z';
                };

            You can provide an optional replacer method. It will be passed the
            key and value of each member, with this bound to the containing
            object. The value that is returned from your method will be
            serialized. If your method returns undefined, then the member will
            be excluded from the serialization.

            If the replacer parameter is an array of strings, then it will be
            used to select the members to be serialized. It filters the results
            such that only members with keys listed in the replacer array are
            stringified.

            Values that do not have JSON representations, such as undefined or
            functions, will not be serialized. Such values in objects will be
            dropped; in arrays they will be replaced with null. You can use
            a replacer function to replace those with JSON values.
            JSON.stringify(undefined) returns undefined.

            The optional space parameter produces a stringification of the
            value that is filled with line breaks and indentation to make it
            easier to read.

            If the space parameter is a non-empty string, then that string will
            be used for indentation. If the space parameter is a number, then
            the indentation will be that many spaces.

            Example:

            text = JSON.stringify(['e', {pluribus: 'unum'}]);
            // text is '["e",{"pluribus":"unum"}]'


            text = JSON.stringify(['e', {pluribus: 'unum'}], null, '\t');
            // text is '[\n\t"e",\n\t{\n\t\t"pluribus": "unum"\n\t}\n]'

            text = JSON.stringify([new Date()], function (key, value) {
                return this[key] instanceof Date ?
                    'Date(' + this[key] + ')' : value;
            });
            // text is '["Date(---current time---)"]'


        JSON.parse(text, reviver)
            This method parses a JSON text to produce an object or array.
            It can throw a SyntaxError exception.

            The optional reviver parameter is a function that can filter and
            transform the results. It receives each of the keys and values,
            and its return value is used instead of the original value.
            If it returns what it received, then the structure is not modified.
            If it returns undefined then the member is deleted.

            Example:

            // Parse the text. Values that look like ISO date strings will
            // be converted to Date objects.

            myData = JSON.parse(text, function (key, value) {
                var a;
                if (typeof value === 'string') {
                    a =
/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2}(?:\.\d*)?)Z$/.exec(value);
                    if (a) {
                        return new Date(Date.UTC(+a[1], +a[2] - 1, +a[3], +a[4],
                            +a[5], +a[6]));
                    }
                }
                return value;
            });

            myData = JSON.parse('["Date(09/09/2001)"]', function (key, value) {
                var d;
                if (typeof value === 'string' &&
                        value.slice(0, 5) === 'Date(' &&
                        value.slice(-1) === ')') {
                    d = new Date(value.slice(5, -1));
                    if (d) {
                        return d;
                    }
                }
                return value;
            });


    This is a reference implementation. You are free to copy, modify, or
    redistribute.

    This code should be minified before deployment.
    See http://javascript.crockford.com/jsmin.html

    USE YOUR OWN COPY. IT IS EXTREMELY UNWISE TO LOAD CODE FROM SERVERS YOU DO
    NOT CONTROL.
 */

/*jslint evil: true, strict: false */

/*members "", "\b", "\t", "\n", "\f", "\r", "\"", JSON, "\\", apply,
 call, charCodeAt, getUTCDate, getUTCFullYear, getUTCHours,
 getUTCMinutes, getUTCMonth, getUTCSeconds, hasOwnProperty, join,
 lastIndex, length, parse, prototype, push, replace, slice, stringify,
 test, toJSON, toString, valueOf
 */

// Create a JSON object only if one does not already exist. We create the
// methods in a closure to avoid creating global variables.
if (!this.JSON) {
	this.JSON = {};
}

( function() {

	function f(n) {
		// Format integers to have at least two digits.
		return n < 10 ? '0' + n : n;
	}

	if (typeof Date.prototype.toJSON !== 'function') {

		Date.prototype.toJSON = function(key) {

			return isFinite(this.valueOf()) ? this.getUTCFullYear() + '-'
				+ f(this.getUTCMonth() + 1) + '-' + f(this.getUTCDate())
				+ 'T' + f(this.getUTCHours()) + ':'
				+ f(this.getUTCMinutes()) + ':' + f(this.getUTCSeconds())
				+ 'Z' : null;
		};

		String.prototype.toJSON = Number.prototype.toJSON = Boolean.prototype.toJSON = function(
			key) {
			return this.valueOf();
		};
	}

	var cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g, escapable = /[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g, gap, indent, meta = { // table of character substitutions
		'\b' : '\\b',
		'\t' : '\\t',
		'\n' : '\\n',
		'\f' : '\\f',
		'\r' : '\\r',
		'"' : '\\"',
		'\\' : '\\\\'
	}, rep;

	function quote(string) {

		// If the string contains no control characters, no quote characters, and no
		// backslash characters, then we can safely slap some quotes around it.
		// Otherwise we must also replace the offending characters with safe escape
		// sequences.

		escapable.lastIndex = 0;
		return escapable.test(string) ? '"' + string.replace(escapable,
			function(a) {
				var c = meta[a];
				return typeof c === 'string' ? c : '\\u' + ('0000' + a
					.charCodeAt(0).toString(16)).slice(-4);
			}) + '"' : '"' + string + '"';
	}

	function str(key, holder) {

		// Produce a string from holder[key].

		var i, // The loop counter.
			k, // The member key.
			v, // The member value.
			length, mind = gap, partial, value = holder[key];

		// If the value has a toJSON method, call it to obtain a replacement value.

		if (value && typeof value === 'object'
			&& typeof value.toJSON === 'function') {
			value = value.toJSON(key);
		}

		// If we were called with a replacer function, then call the replacer to
		// obtain a replacement value.

		if (typeof rep === 'function') {
			value = rep.call(holder, key, value);
		}

		// What happens next depends on the value's type.

		switch (typeof value) {
			case 'string':
				return quote(value);

			case 'number':

				// JSON numbers must be finite. Encode non-finite numbers as null.

				return isFinite(value) ? String(value) : 'null';

			case 'boolean':
			case 'null':

				// If the value is a boolean or null, convert it to a string. Note:
				// typeof null does not produce 'null'. The case is included here in
				// the remote chance that this gets fixed someday.

				return String(value);

			// If the type is 'object', we might be dealing with an object or an array or
			// null.

			case 'object':

				// Due to a specification blunder in ECMAScript, typeof null is 'object',
				// so watch out for that case.

				if (!value) {
					return 'null';
				}

				// Make an array to hold the partial results of stringifying this object value.

				gap += indent;
				partial = [];

				// Is the value an array?

				if (Object.prototype.toString.apply(value) === '[object Array]') {

					// The value is an array. Stringify every element. Use null as a placeholder
					// for non-JSON values.

					length = value.length;
					for (i = 0; i < length; i += 1) {
						partial[i] = str(i, value) || 'null';
					}

					// Join all of the elements together, separated with commas, and wrap them in
					// brackets.

					v = partial.length === 0 ? '[]' : gap ? '[\n' + gap
						+ partial.join(',\n' + gap) + '\n' + mind + ']'
						: '[' + partial.join(',') + ']';
					gap = mind;
					return v;
				}

				// If the replacer is an array, use it to select the members to be stringified.

				if (rep && typeof rep === 'object') {
					length = rep.length;
					for (i = 0; i < length; i += 1) {
						k = rep[i];
						if (typeof k === 'string') {
							v = str(k, value);
							if (v) {
								partial.push(quote(k) + (gap ? ': ' : ':') + v);
							}
						}
					}
				} else {

					// Otherwise, iterate through all of the keys in the object.

					for (k in value) {
						if (Object.hasOwnProperty.call(value, k)) {
							v = str(k, value);
							if (v) {
								partial.push(quote(k) + (gap ? ': ' : ':') + v);
							}
						}
					}
				}

				// Join all of the member texts together, separated with commas,
				// and wrap them in braces.

				v = partial.length === 0 ? '{}' : gap ? '{\n' + gap
					+ partial.join(',\n' + gap) + '\n' + mind + '}'
					: '{' + partial.join(',') + '}';
				gap = mind;
				return v;
		}
	}

	// If the JSON object does not yet have a stringify method, give it one.

	if (typeof JSON.stringify !== 'function') {
		JSON.stringify = function(value, replacer, space) {

			// The stringify method takes a value and an optional replacer, and an optional
			// space parameter, and returns a JSON text. The replacer can be a function
			// that can replace values, or an array of strings that will select the keys.
			// A default replacer method can be provided. Use of the space parameter can
			// produce text that is more easily readable.

			var i;
			gap = '';
			indent = '';

			// If the space parameter is a number, make an indent string containing that
			// many spaces.

			if (typeof space === 'number') {
				for (i = 0; i < space; i += 1) {
					indent += ' ';
				}

				// If the space parameter is a string, it will be used as the indent string.

			} else if (typeof space === 'string') {
				indent = space;
			}

			// If there is a replacer, it must be a function or an array.
			// Otherwise, throw an error.

			rep = replacer;
			if (replacer
				&& typeof replacer !== 'function'
				&& (typeof replacer !== 'object' || typeof replacer.length !== 'number')) {
				throw new Error('JSON.stringify');
			}

			// Make a fake root object containing our value under the key of ''.
			// Return the result of stringifying the value.

			return str('', {
				'' : value
			});
		};
	}

	// If the JSON object does not yet have a parse method, give it one.

	if (typeof JSON.parse !== 'function') {
		JSON.parse = function(text, reviver) {

			// The parse method takes a text and an optional reviver function, and returns
			// a JavaScript value if the text is a valid JSON text.

			var j;

			function walk(holder, key) {

				// The walk method is used to recursively walk the resulting structure so
				// that modifications can be made.

				var k, v, value = holder[key];
				if (value && typeof value === 'object') {
					for (k in value) {
						if (Object.hasOwnProperty.call(value, k)) {
							v = walk(value, k);
							if (v !== undefined) {
								value[k] = v;
							} else {
								delete value[k];
							}
						}
					}
				}
				return reviver.call(holder, key, value);
			}

			// Parsing happens in four stages. In the first stage, we replace certain
			// Unicode characters with escape sequences. JavaScript handles many characters
			// incorrectly, either silently deleting them, or treating them as line endings.

			cx.lastIndex = 0;
			if (cx.test(text)) {
				text = text.replace(cx, function(a) {
					return '\\u' + ('0000' + a.charCodeAt(0).toString(16))
						.slice(-4);
				});
			}

			// In the second stage, we run the text against regular expressions that look
			// for non-JSON patterns. We are especially concerned with '()' and 'new'
			// because they can cause invocation, and '=' because it can cause mutation.
			// But just to be safe, we want to reject all unexpected forms.

			// We split the second stage into 4 regexp operations in order to work around
			// crippling inefficiencies in IE's and Safari's regexp engines. First we
			// replace the JSON backslash pairs with '@' (a non-JSON character). Second, we
			// replace all simple value tokens with ']' characters. Third, we delete all
			// open brackets that follow a colon or comma or that begin the text. Finally,
			// we look to see that the remaining characters are only whitespace or ']' or
			// ',' or ':' or '{' or '}'. If that is so, then the text is safe for eval.

			if (/^[\],:{}\s]*$/
				.test(text
					.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@')
					.replace(
						/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,
						']').replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {

				// In the third stage we use the eval function to compile the text into a
				// JavaScript structure. The '{' operator is subject to a syntactic ambiguity
				// in JavaScript: it can begin a block or an object literal. We wrap the text
				// in parens to eliminate the ambiguity.

				j = eval('(' + text + ')');

				// In the optional fourth stage, we recursively walk the new structure, passing
				// each name/value pair to a reviver function for possible transformation.

				return typeof reviver === 'function' ? walk( {
					'' : j
				}, '') : j;
			}

			// If the text is not JSON parseable, then a SyntaxError is thrown.

			throw new SyntaxError('JSON.parse');
		};
	}
}());

/**
 * YZE AJAX Lib, 依赖jQuery
 */
function yze_ajax(){
	this.getUrl 				= "";
	this.submittedCallback 		= "";
	this.loadedCallback 		= "";
	this.errorCallback 		= "";

	/**
	 * ajax load html(比如form)到dom中, 并在loadedCallback中决定html如何展示，比如展示位对话框还是
	 * 在页面上的某个位置输出，
	 *
	 * 如果加载的内容是一个form(只支持一个form)，那么yze_ajax会重写form的提交逻辑，表单的submit事件会调用submittedCallback
	 *
	 * url 加载的url
	 * loadedCallback 加载成功后的回调，在该回调中把form放在页面中, 参数是加载下来的html
	 * submittedCallback 提交成功后的回调，参数是提交成功后的数据
	 * errorCallback 出错的回调，参数是错误消息
	 */
	this.get = function(url, loadedCallback, errorCallback, submittedCallback) {
		this.getUrl 		= url;
		this.submittedCallback 	= submittedCallback;
		this.errorCallback 	= errorCallback;
		this.loadedCallback 	= loadedCallback;
		var _self = this;

		$.ajax({
		        url: url,
		        type: "GET",
		        data: {},
		        error: function(jqXHR, textStatus, errorThrown) {
		        	if (errorCallback) {
						errorCallback(errorThrown)
					}else{
		        		console.log(errorThrown);
					}
		        },
		        success: function(data, textStatus, jqXHR) {
		        	modifyForm.call(_self, data, false);
		        },
		        dataType: "html"
		});
	}

	/**
	 *
	 * 构建一个iframe，并在其中加载指定的url内容，如果iframe中包含有表单(只支持一个form)，则yze_ajax会重写表单的submit事件，调用submittedCallback
	 *
	 * url 加载的url
	 * loadedCallback 加载成功后的回调，该回调负责把iframe放在页面中
	 * submittedCallback 表单提交成功后的回调，参数是提交成功后的数据
	 * errorCallback 出错的回调，参数是错误消息
	 */
	this.getIframe = function(url, loadedCallback, errorCallback, submittedCallback) {
		this.getUrl 		= url;
		this.submittedCallback 	= submittedCallback;
		this.loadedCallback 	= loadedCallback;
		this.errorCallback 	= errorCallback;
		var _id = md5(url);
		var _self = this;

		loadedCallback("<iframe id='"+_id+"' marginheight='0' frameborder='0'  width='100%'  height='200px'  src='" + url + "'></iframe>");

		var iframe = $("#"+_id)
		iframe.load(function(){
			var newheight;
			var newwidth;
			newheight = this.contentWindow.document.body.scrollHeight;
			newwidth = this.contentWindow.document.body.scrollWidth;

			this.height = (newheight) + "px";
			this.width = (newwidth) + "px";

			modifyForm.call(_self, this.contentWindow.document.body, true);
		});
	}


	function modifyForm(data, isInIframe){
		var formid = md5(this.getUrl)
		if (!isInIframe) {
			data = data.replace(/<form\s/ig, "<form data-yze-ajax-form-id='"+formid+"' ");
			//alert(data);
			//该回调调用后，表单就已经加在dom中了，现在修改它的submit事件
			this.loadedCallback(data);
		}else{
			$(data).find('form').attr('data-yze-ajax-form-id',formid)
		}

		var getUrl 				= this.getUrl;
		var submittedCallback 	= this.submittedCallback;
		var errorCallback   = this.errorCallback
		var _self = this;
		var form = null;
		if (isInIframe){
			form = $("form[data-yze-ajax-form-id='"+formid+"']", data);
		}else{
			form = $("form[data-yze-ajax-form-id='"+formid+"']");
		}
		form.unbind("submit");
		form.submit(function(){
			var action =  $(this).attr("action") || getUrl; //如果沒有指定action，那麼post仍然提交到getUrl中
			var method = $(this).attr("method") || "POST";

			var formData = new FormData(form.get(0));

			// 处理上传文件
			var uploadFiles = $("form[data-yze-ajax-form-id='"+formid+"']").find("input[type='file']");
			if (uploadFiles.length > 0){
				for (var i = 0; i<uploadFiles.length; i++) {
					formData.append($(uploadFiles[i]).attr('name'), $(uploadFiles[i])[0].files[0])
				}
			}
			$.ajax({
				url: 	action,
				type: 	method,
				processData: false,
				contentType: false,
				data: 	formData,
				error: function(jqXHR, textStatus, errorThrown) {
					if (errorCallback){
						errorCallback(errorThrown)
					}else{
						console.log(errorThrown);
					}
				},
				success: function(data, textStatus, jqXHR) {
					var json = null;
					try{
						json = JSON.parse(data);
					}catch(e){}
					if(json){
						submittedCallback(json);
					}else{
						modifyForm.call(_self, data, isInIframe);
					}
				},
				dataType: "html"
			});
			return false;
		});
	}
}

/**
 *
 * add params in to url.
 * addParamsInUrl("helloworld.php", {foo1:bar1, foo2:bar2}) will return helloworld.php?foo1=bar1&foo2=bar2
 *
 * if params has exist in url，new param will replace old
 * addParamsInUrl("helloworld.php?foo1=bar1", {foo1:bar2, foo2:bar2}) will return helloworld.php?foo1=bar2&foo2=bar2
 *
 * if url is null, ""; will return querystring like:
 * addParamsInUrl("", {foo1:bar2, foo2:bar2}) will return foo1=bar2&foo2=bar2
 *
 * if params is null, "", {}; will return the url:
 * addParamsInUrl("helloworld.php", "") will return helloworld.php
 *
 * if params is not object, will append to url and return:
 * addParamsInUrl("hello", "world") will return hello?world
 *
 * @param url
 * @param params json object like {foo1:bar1, foo2:bar2}
 */
function ydhlib_AddParamsInUrl(url, params){
    var queryString = [];
    if(typeof(params)=="object"){
        for(name in params){
            if(!name)continue;
            queryString.push( name+"="+params[name] );
        }
    }else{
        if(params){
            queryString.push(params);
        }
    }

    if( ! url){
        return queryString.join("&");
    }

    var urlComps = url.split("?");
    if(urlComps.length==1){
        return queryString.length>0 ? url+"?"+queryString.join("&") : url;
    }

    var oldQueryString = urlComps[1].split("&");
    var oldParams = {};
    for(var i=0; i < oldQueryString.length; i++){
        var nameValue = oldQueryString[i].split("=");
        if( ! nameValue[0])continue;
        if( params[nameValue[0]] != undefined) continue;
        queryString.push(nameValue[0] + "=" + (nameValue.length < 1 ? "" : nameValue[1]));
    }

    return queryString.length>0 ? urlComps[0]+"?"+queryString.join("&") : urlComps[0];
}
