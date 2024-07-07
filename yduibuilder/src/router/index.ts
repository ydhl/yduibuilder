import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router'
import UIBuilder from '../views/UIBuilder.vue'
import SSO from '../views/SSO.vue'
import Page from '../views/Page.vue'
import Error from '../views/Error.vue'
import Test from '../views/Test.vue'

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    name: 'UIBuilder',
    component: UIBuilder
  },
  {
    path: '/sso',
    name: 'SSO',
    component: SSO
  },
  {
    path: '/error',
    name: 'Error',
    component: Error
  },
  {
    path: '/page',
    name: 'Page',
    component: Page
  },
  {
    path: '/test',
    name: 'Test',
    component: Test
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

export default router
