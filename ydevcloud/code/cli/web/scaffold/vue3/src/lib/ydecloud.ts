
export default {
  isPlainObject (val: any) {
    return toString.call(val) === '[object Object]'
  },
  isEmptyObject: function (e: any) {
    for (const t in e) {
      return !1
    }
    return !0
  },
  styleFromJson (json: any) {
    if (!this.isPlainObject(json)) return json
    const styles: any = []
    for (const name in json) {
      styles.push(`${name}: ${json[name]}`)
    }
    return styles.join(';')
  }
}
