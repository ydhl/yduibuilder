<template>
  <div class="d-flex justify-content-center align-items-center vh-100 w-100">
    <div class="card light shadow">
      <div class="card-header">Oops</div>
      <div class="card-body p-5">
        <i class="iconfont icon-error"></i>
        {{error}}
      </div>
      <div class="card-footer">
        <a class="btn btn-primary" :href="api+'dashboard'">{{t('common.goBack')}}</a>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import ydhl from '@/lib/ydhl'

export default {
  name: 'SSO',
  setup (props: any, context: any) {
    const error = ref()
    const { t } = useI18n()
    const api = ydhl.api
    onMounted(() => {
      const route = useRoute()
      // const router = useRouter()
      error.value = route.query.error
    })
    return {
      error,
      t,
      api
    }
  }
}
</script>
