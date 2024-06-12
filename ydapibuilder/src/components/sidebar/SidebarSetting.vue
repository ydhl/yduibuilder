<template>
  <div class="ps-2 mt-2">
    <div class="list-group">
      <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center list-group-flush"
         @click="openSetting('Environment')"
         href="javascript:void(0)">
        {{t('setting.environment')}}
        <i class="iconfont icon-arrowright"></i>
      </a>
    </div>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'

export default {
  name: 'SidebarSetting',
  setup (props: any, context: any) {
    const { t } = useI18n()
    const store = useStore()
    const openSetting = (page) => {
      store.commit('putTab', { page, name: 'common.loading' })
    }
    return {
      t,
      openSetting
    }
  }
}
</script>
