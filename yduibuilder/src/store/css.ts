/**
 * state 异步网络加载后在replace
 */
const store = {
  state: {
  },
  mutations: {
  },
  actions: {
  },
  getters: {
    translate: (state) => (context, name: string | object) => {
      const cssMap = state.cssTranslate
      if (typeof name === 'string') {
        if (cssMap[context] === undefined || cssMap[context][name] === undefined) return undefined
        return cssMap[context][name]
      }
      const rst: any = []

      for (const key in name) {
        // console.log('css.ts translate', name)
        if (cssMap[context][key][name[key]]) rst.push(cssMap[context][key][name[key]])
      }
      return rst.length > 0 ? rst.join(' ') : ''
    }
  }
}
export default store
