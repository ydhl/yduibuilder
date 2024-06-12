import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import { createI18n } from 'vue-i18n'
import i18nMessage from '@/i18n/index'
import ydhl from '@/lib/ydhl'
import VMdEditor from '@kangc/v-md-editor'
import '@kangc/v-md-editor/lib/style/base-editor.css'
import githubTheme from '@kangc/v-md-editor/lib/theme/github.js'
import '@kangc/v-md-editor/lib/theme/style/github.css'
import hljs from 'highlight.js'
import Layui from '@layui/layui-vue'

if (navigator.userAgent.indexOf('Firefox') > -1) {
  require('@/assets/firefox-index.scss')
} else {
  require('@/assets/index.scss')
}
require('vant/lib/index.css')
require('@layui/layui-vue/lib/index.css')

VMdEditor.use(githubTheme, {
  Hljs: hljs
})
// console.log(language)
const i18n = createI18n({
  legacy: false, // you must set `false`, to use Compostion API
  locale: ydhl.getLanguage(),
  fallbackLocale: 'en',
  messages: i18nMessage
})

const app = createApp(App)
app.use(store).use(router).use(VMdEditor).use(Layui).use(i18n).mount('#app')
