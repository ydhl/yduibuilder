<template>
  <div class="d-flex justify-content-center align-items-center h-100 w-100">
    <div class="text-center">
      <div>登录中...</div>
      <div class="progress">
        <div
          class="progress-bar progress-bar-striped progress-bar-animated"
          role="progressbar"
          aria-valuenow="100"
          aria-valuemin="0"
          aria-valuemax="100"
          style="width: 200px"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import $ from 'jquery'
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ydhl from '@/lib/ydhl'

export default {
  name: 'SSO',
  setup (props: any, context: any) {
    onMounted(() => {
      const route = useRoute()
      const router = useRouter()
      const token = route.query.token || ''
      const uuid = route.query.uuid || ''
      const functionid = route.query.functionid || ''
      if (!token) {
        window.location.href = ydhl.api
        return
      }
      const query: any = {}
      if (uuid) {
        query.uuid = uuid
      } else {
        query.functionId = functionid
      }
      $.post(`${ydhl.api}api/sso.json`, { token }, (rst) => {
        if (rst && rst.success) {
          ydhl.saveJwt(rst.data.token)
          router.replace({ path: '/', query })
        } else {
          window.location.href = ydhl.api
        }
      })
    })
  }
}
</script>
