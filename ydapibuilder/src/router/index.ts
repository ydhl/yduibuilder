import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router'
import APIBuilder from '../views/APIBuilder.vue'
import SSO from '../views/SSO.vue'
import Test from '../views/Test.vue'
import Error from '../views/Error.vue'

const routes: Array<RouteRecordRaw> = [
  {
    path: '/:projectId([^/]+)',
    name: 'APIBuilder',
    component: APIBuilder
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
    path: '/',
    name: 'Blank',
    component: SSO
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
