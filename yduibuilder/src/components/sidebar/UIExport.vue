<template>
  <div v-if="!selectedUIItemId" class="d-flex h-100 text-muted justify-content-center align-content-center align-items-center">
    {{t('common.pleaseSelectUIItem')}}
  </div>
  <template v-if="selectedUIItemId">
    <div class="text-muted p-2"><i class="iconfont icon-tips"></i>{{t('uiexport.desc')}}</div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{t('common.endpoint')}}</label>
      <div class="col-sm-9">
        <div class="form-control-plaintext text-uppercase">{{endKind}}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{t('ui.framework')}}</label>
      <div class="col-sm-9">
        <div class="form-control-plaintext">{{ui}} {{uiVersion}}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{t('ui.type')}}</label>
      <div class="col-sm-9">
        <div class="form-control-plaintext">{{t(`ui.${selectedUIItem.type.toLowerCase()}`)}}</div>
      </div>
    </div>
    <div class="row">
      <label for="form-ui-name" class="col-sm-3 col-form-label text-end">{{t('uiexport.name')}}</label>
      <div class="col-sm-9">
        <input type="text" id="form-ui-name" class="form-control form-control-sm" v-model="uiName" autocomplete="off">
      </div>
    </div>
    <div class="row" v-if="uiHasInLibInfo">
      <div class="text-danger p-2" v-if="uiHasInLibInfo.hasBound"><i class="iconfont icon-tips"></i>{{t('uiexport.notExportEvent')}}</div>
      <div class="col-sm-9 offset-sm-3">
        <div class="form-control-plaintext text-primary"><small>{{uiHasInLibInfo.msg}}</small></div>
      </div>
    </div>
    <div class="rowc mt-3">
      <div class="col-sm-9 offset-sm-3">
        <button type="button" class="btn btn-sm btn-primary" @click="exportUI">{{t('uiexport.createComponent')}}</button>
      </div>
    </div>
  </template>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import { computed, onMounted, ref } from 'vue'
import ydhl from '@/lib/ydhl'
import { useStore } from 'vuex'

export default {
  name: 'UIExport',
  setup (props: any, context: any) {
    const info = initUI()
    const store = useStore()
    const uiName = ref('')
    const uiHasInLibInfo = ref<Object|null>(null)
    const { t } = useI18n()
    const currFunctionId = computed(() => store.state.design.function.id)
    const currPage = computed(() => store.state.design.page)
    const versionId = computed(() => store.state.design.pageVersionId[currPage.value.meta.id])

    const exportUI = () => {
      if (!uiName.value.trim()) {
        ydhl.alert(t('uiexport.nameIsEmpty'))
        return
      }
      const uiid = info.selectedUIItemId.value
      const pageid = info.selectedPageId.value

      ydhl.savePage(currFunctionId.value, currPage.value, versionId.value, (rst) => {
        if (rst?.success) {
          store.commit('updateSavedState', { pageUuid: currPage.value.meta.id, saved: 1, versionId: rst.data.versionId })
        }

        ydhl.post('api/uicomponent/export', { pageid, uiid, name: uiName.value }, [], (rst) => {
          if (rst && rst.success) {
            ydhl.alert(t('uiexport.success'))
            return
          }
          ydhl.alert(rst.msg || t('uiexport.error'))
        })
      })
    }

    const getLibInfo = () => {
      const uiid = info.selectedUIItemId.value
      if (!uiid) return
      uiHasInLibInfo.value = null
      uiName.value = ''
      ydhl.get('api/uicomponent/info', { uiid, page_uuid: info.selectedPageId.value }, (rst) => {
        if (rst && rst.success) {
          uiName.value = rst.data.name
          uiHasInLibInfo.value = rst.data
        }
      })
    }
    onMounted(getLibInfo)

    return {
      ...info,
      exportUI,
      uiHasInLibInfo,
      uiName,
      t
    }
  }
}
</script>

<style scoped>

</style>
