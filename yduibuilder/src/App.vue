<template>
  <router-view />
</template>
<script lang="ts">
import { onMounted } from 'vue'
import ydhl from '@/lib/ydhl'

// bootstrap 在index.html 中通过script的方式引入了（因为ydhl-bootstrap-5.0.1.js不是module的原因），这里只是申明一下，才能编译过去及
// 实际运行时是window环境上是有bootstrap的
declare const bootstrap: any

export default {
  setup (props: any, context: any) {
    onMounted(() => {
      setTimeout(() => {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl, { delay: { show: 800, hide: 300 } })
        })
      }, 1000)
      // 在jwt过期前，刷新下jwt
      setInterval(() => {
        // refresh token
        const token = ydhl.getJwt()
        if (token) {
          ydhl.postJson('api/token.json', {}).then((rst: any) => {
            ydhl.saveJwt(rst.data.token)
          }).catch((reason: any) => {
            // console.log(reason)
          })
        }
      }, 10000000)
    })
    return {
    }
  }
}
</script>
