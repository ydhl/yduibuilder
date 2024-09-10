<template>
  <div class="d-flex justify-content-center align-items-center h-100">
    <button type="button" @click="addTab('APIAdd')" style="width: 200px" class="btn btn-outline-secondary pb-3"><div style="font-size: 2rem" class="iconfont icon-api m-3"></div>{{t('common.addAPI')}}</button>
    <button type="button" @click="addTab('TestAdd')" style="width: 200px" class="btn btn-outline-secondary ms-3 pb-3"><div style="font-size: 2rem" class="iconfont icon-test m-3"></div>{{t('common.addTest')}}</button>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'

export default {
  name: 'WelcomePage',
  setup (props: any, context: any) {
    const store = useStore()
    const addTab = (page) => {
      store.commit('putTab', { page: page, name: 'common.loading' })
    }
    const { t } = useI18n()
    return {
      t,
      addTab
    }
  }
}
</script>
