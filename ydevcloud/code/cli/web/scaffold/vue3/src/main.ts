import './assets/index.css'

import { createApp, type Directive } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import * as directives from "./directives";
{{import}}
const app = createApp(App)

Object.keys(directives).forEach(key => {
  app.directive(key, (directives as { [key: string]: Directive })[key]);
});
app.use(createPinia())
app.use(router)

app.mount('#app')
