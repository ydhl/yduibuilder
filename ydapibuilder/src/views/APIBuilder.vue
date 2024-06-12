<template>
  <template v-if="loaded">
    <TopPanelAPI />
    <LeftPanelAPI />
    <WorkspacePanel></WorkspacePanel>
    <div class="full-backdrop" v-if="backdropVisible"></div>
  </template>
</template>

<script lang="ts">
import TopPanelAPI from '@/components/page/TopPanelAPI.vue'
import LeftPanelAPI from '@/components/page/LeftPanelAPI.vue'
import { computed, getCurrentInstance, onMounted, ref } from 'vue'
import { useStore } from 'vuex'
import ydhl from '@/lib/ydhl'
import { layer } from '@layui/layui-vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import WorkspacePanel from '@/components/page/WorkspacePanel.vue'

export default {
  components: {
    WorkspacePanel,
    TopPanelAPI,
    LeftPanelAPI
  },
  setup (props: any, context: any) {
    const loaded = ref(true)
    const store = useStore()
    const route = useRoute()
    const router = useRouter()
    const oldDesign = computed(() => store.state.design)
    const oldUser = computed(() => store.state.user)
    const backdropVisible = computed(() => {
      return store.state.design.backdropVisible
    })
    const { t } = useI18n()
    onMounted(() => {
      const loadingId = layer.load(1, { content: t('common.pleaseWait') })
      ydhl.get('api/load/api.json?projectId=' + route.params.projectId, false, true).then((rst: any) => {
        layer.close(loadingId)
        const designData: any = rst.design
        const userData: any = rst.user

        const lang: any = rst.lang
        const ctx:any = getCurrentInstance()
        if (ctx != null) ctx.$i18n.locale = lang

        store.commit('cleanState')
        const design: any = JSON.parse(JSON.stringify(oldDesign.value))
        for (const dataKey in designData) {
          design[dataKey] = designData[dataKey]
        }
        // 初始化本地相关数据
        design.saved = -1
        const user: any = JSON.parse(JSON.stringify(oldUser.value))
        for (const dataKey in userData) {
          user[dataKey] = userData[dataKey]
        }

        store.replaceState({ design, user })
      }).catch((rst) => {
        layer.close(loadingId)
        router.push({
          path: '/error',
          query: {
            error: rst ? rst.msg : 'Oops, Please try again'
          }
        })
      })
    })
    return {
      loaded,
      t,
      backdropVisible
    }
  },
  name: 'APIBuilder'
}
</script>
