import { createStore } from 'vuex'
import design from './design'
import user from './user'
const debug = false // process.env.NODE_ENV !== 'production'
const plugins: any = []

const store = createStore({
  modules: { design, user },
  strict: debug,
  plugins: plugins
})
export default store
