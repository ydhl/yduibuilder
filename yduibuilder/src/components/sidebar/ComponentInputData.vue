<template>
  <div v-if="loading" class="vh-100 d-flex align-items-center justify-content-center">
    {{t('page.loading')}}
  </div>
  <template v-else>
    <div class="style-panel pt-1">
      <div class="text-muted fs-7 text-center p-3">{{t('variable.inputDataDesc', [uiName])}}</div>
      <div v-if="!queryDatas || queryDatas.length===0" class="p-3 text-muted">
        <i class="iconfont icon-wuneirong"></i> {{t('common.empty')}}
      </div>
      <template v-else>
        <DataConnect v-for="(item, index) in queryDatas" @updateConnectData="updateConnectData"
                           :bound-data="inputDatas" path="" :root-uuid="item.uuid" connect="to"
                           :key="index" :intent="0" :model="item" :index="0">
        </DataConnect>
      </template>
    </div>
  </template>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, onMounted, ref, watch } from 'vue'
import ydhl from '@/lib/ydhl'
import { useStore } from 'vuex'
import DataConnect from '@/components/common/DataConnect.vue'
export default {
  name: 'ComponentInputData',
  components: { DataConnect },
  setup (pros: any, context: any) {
    const { t } = useI18n()
    const loading = ref(true)
    const project = computed(() => store.state.design.project)
    const store = useStore()
    const selectedPage = computed(() => store.state.design.page)
    const selectedPageId = computed(() => store.state.design.page?.meta?.id)
    const selectedUIItemId = computed(() => store.state.design?.selectedUIItemId || undefined)
    const selectedUIItem = computed(() => {
      if (!selectedUIItemId.value) return null
      const { uiConfig } = store.getters.getUIItemInPage(selectedUIItemId.value, selectedPageId.value)
      return uiConfig
    })
    const uiName = computed(() => {
      if (!selectedUIItem.value) return ''
      return selectedUIItem.value.meta.title
    })
    const subpageId = computed(() => {
      if (!selectedUIItem.value) return null
      return selectedUIItem.value.items?.[0].subPageId || undefined
    })

    const queryDatas = ref<any>([])
    const inputDatas = ref<any>([])

    const loadData = (showLoading = false) => {
      queryDatas.value = []
      if (selectedUIItem.value && selectedUIItem.value.type !== 'UIComponent') return
      if (!subpageId.value) return
      if (showLoading) loading.value = true
      ydhl.get('api/bind/input.json?to_page_uuid=' + subpageId.value + '&from_page_uuid=' + selectedPageId.value, [], (rst) => {
        if (showLoading) loading.value = false
        if (!rst?.success) {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
          return
        }
        queryDatas.value = rst.data.query
        inputDatas.value = rst.data.input
      }, 'json')
    }

    onMounted(() => {
      loadData(true)
    })
    watch(selectedUIItemId, (n, v) => {
      if (n !== v) loadData(false)
    })
    const removeBound = (uuid) => {
      ydhl.postJson('api/bind/deleteconnect.json', {
        to_page_uuid: subpageId.value,
        from_page_uuid: selectedPageId.value,
        to_uuid: uuid
      }).then((rst: any) => {
        if (!rst?.success) {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
          return
        }
        loadData()
      })
    }

    // 选择的数据toData绑定给 界面上的数据From
    const updateConnectData = (fromRootUuid, fromPath, fromUuid, toData, remove, desc) => {
      if (remove) {
        removeBound(fromRootUuid)
        return
      }
      if (!toData || ydhl.isEmptyObject(toData)) return
      ydhl.postJson('api/bind/connect.json', {
        to_page_uuid: subpageId.value,
        from_page_uuid: selectedPageId.value,
        to_uuid: fromRootUuid,
        to_data_id: fromUuid,
        to_path: fromPath,
        input: toData
      }).then((rst: any) => {
        if (!rst?.success) {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
          // return
        }
        // loadData()
      })
    }
    return {
      t,
      loading,
      project,
      uiName,
      updateConnectData,
      queryDatas,
      inputDatas,
      selectedPage
    }
  }
}
</script>
