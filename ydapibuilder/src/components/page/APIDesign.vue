<template>
  <template v-if="pageLoading">
    <div class="d-flex justify-content-center align-items-center h-100">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
  </template>
</template>

<script lang="ts">
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { computed, ref, watch } from 'vue'
import ydhl from '@/lib/ydhl'
import { useStore } from 'vuex'

export default {
  name: 'APIDesign',
  setup (props: any, context: any) {
    const pageLoading = ref(true)
    const router = useRouter()
    const goto = (path, query = {}) => {
      router.push({ path, query })
    }
    const { t } = useI18n()
    const store = useStore()
    const currAPIId = computed<string>({
      get () {
        return store.state.design.currApi || ''
      },
      set (v) {
        store.state.design.currApi = v
      }
    })
    const project = computed(() => store.state.design.project)
    const loadAPI = (apiid: string) => {
      ydhl.get(`api/api.json?projectId=${project.value.id}&apiid=${apiid}`, false).then((rst: any) => {
        pageLoading.value = false
        store.commit('putApi', rst)
        store.commit('updateState', { currApi: rst.id })
      })
    }

    watch(currAPIId, () => {
      if (currAPIId.value) {
        pageLoading.value = false
        return
      }
      loadAPI(currAPIId.value)
    }, {
      immediate: true
    })

    return {
      t,
      pageLoading,
      goto
    }
  }
}
</script>
