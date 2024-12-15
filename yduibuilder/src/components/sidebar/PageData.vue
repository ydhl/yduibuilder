<template>
  <div v-if="loading" class="vh-100 d-flex align-items-center justify-content-center">
    {{t('page.loading')}}
  </div>
  <template v-else>
    <template v-if="selectedPage.pageType == 'component'" >
      <!--组件的入口数据-->
      <DataStruct :can-mutation="true" :can-output="true" :data-title="t('variable.inputData')"
            :datas="queryDatas" @remove="removeQuery" @update="updateQuery"></DataStruct>
    </template>
    <template v-else >
      <!--正常页面的入口数据-->
      <DataStruct v-if="project.rewrite" :data-title="t('action.pathParams')" :can-output="true" :datas="pathDatas"></DataStruct>
      <DataStruct :can-mutation="true" :types="['string', 'integer', 'number', 'array', 'any']" :can-output="true"
            :data-title="t('action.queryParams')" :datas="queryDatas" @remove="removeQuery" @update="updateQuery"></DataStruct>
    </template>

    <DataStruct :can-mutation="true" :can-input="true" :can-output="true" :data-title="t('variable.pageScope')"
            :datas="pageDatas" @remove="removePageData" @update="updatePageData"></DataStruct>

    <DataStruct :can-mutation="true" :can-input="false" :is-expression="true" :can-output="true" :data-title="t('expression.expression')"
                :datas="pageExpressions" @remove="removePageData" @update="updatePageData"></DataStruct>
  </template>
</template>

<script lang="ts" setup>
import { useI18n } from 'vue-i18n'
import { computed, onMounted, ref, watch } from 'vue'
import ydhl from '@/lib/ydhl'
import { useStore } from 'vuex'
import DataStruct from '@/components/common/DataStruct.vue'

defineEmits(['contextMenu'])
const { t } = useI18n()
const loading = ref(true)
const project = computed(() => store.state.design.project)
const store = useStore()
const selectedPage = computed(() => store.state.design.page)
const selectedPageId = computed(() => store.state.design.page?.meta?.id)

const pageDatas = ref<any>([])
const pageExpressions = ref<any>([])
const queryDatas = ref<any>([])
const pathDatas = ref<any>([])

const loadData = (showLoading = false) => {
  if (showLoading) loading.value = true
  ydhl.get('api/bind/data.json?page_uuid=' + selectedPageId.value, [], (rst) => {
    if (showLoading) loading.value = false
    if (!rst?.success) {
      ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
      return
    }
    pageDatas.value = rst.data.page
    pageExpressions.value = rst.data.expression
    queryDatas.value = rst.data.query
    pathDatas.value = rst.data.path
  }, 'json')
}
const updatePageData = (data: any, index: number, nameChanged: Record<string, string>) => {
  updateData('page', data, index, nameChanged)
}
const removePageData = (data) => {
  removeData('page', data)
}
const updateQuery = (data: any, index: number, nameChanged: Record<string, string>) => {
  updateData('query', data, index, nameChanged)
}
const removeQuery = (data) => {
  removeData('query', data)
}
const updateData = (dataFrom, data: any, index: number, nameChanged: Record<string, string>) => {
  data.page_uuid = selectedPageId.value
  data.data_from = dataFrom
  data.nameChanged = nameChanged
  ydhl.loading(t('common.pleaseWait')).then((id) => {
    ydhl.postJson('api/bind/data.json', data).then((rst: any) => {
      if (!rst || !rst.success) {
        ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
      }
      ydhl.closeLoading(id)
      loadData()
    }).catch((res: any) => {
      ydhl.alert(res || t('common.operationFail'), t('common.ok'))
    })
  })
}
const removeData = (dataFrom, data) => {
  ydhl.loading(t('common.pleaseWait')).then((id) => {
    ydhl.post('api/bind/deletedata.json', { uuid: data.uuid }, [], (rst) => {
      ydhl.closeLoading(id)
      loadData()
    }, 'json')
  })
}
onMounted(() => {
  loadData(true)
})
watch(selectedPageId, (n, v) => {
  if (n !== v) loadData(false)
})
</script>
