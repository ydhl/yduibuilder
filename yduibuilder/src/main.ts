import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import { createI18n } from 'vue-i18n'
import i18nMessage from '@/i18n/index'
import ydhl from '@/lib/ydhl'
import layer from '@layui/layer-vue'
import '@layui/layer-vue/lib/index.css'

if (window.top === window) {
  if (navigator.userAgent.indexOf('Firefox') > -1) {
    require('@/assets/firefox-index.scss')
  } else {
    require('@/assets/index.scss')
  }
} else { // iframe
  require('@/assets/iframe.scss')
}

// console.log(language)
const i18n = createI18n({
  legacy: false, // you must set `false`, to use Compostion API
  locale: ydhl.getLanguage(),
  fallbackLocale: 'en',
  messages: i18nMessage
})

const app = createApp(App)
app.use(store).use(router).use(layer).use(i18n).mount('#app')
