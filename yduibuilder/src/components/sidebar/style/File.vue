<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.file') }}</div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.file.accept') }}</label>
      <div class="col-sm-9">
        <input type="text" class="form-control form-control-sm" :placeholder="t('style.file.acceptTip')" v-model.trim="accept">
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.file.maxFileSize') }}</label>
      <div class="col-sm-9">
        <input type="text" class="form-control form-control-sm" v-model="maxFileSize">
      </div>
    </div>
    <div class="row mb-1">
      <label class="col-sm-9 offset-sm-3 d-flex align-items-center">
        <input type="checkbox" v-model="multiple"> {{ t('style.file.multiple') }}
      </label>
    </div>
    <div class="row mb-2">
      <label class="col-sm-9 offset-sm-3 d-flex align-items-center">
        <input type="checkbox" v-model="isAutoUpload">{{ t('style.file.isAutoUpload') }}
      </label>
      <label class="col-sm-9 offset-sm-3 text-muted d-flex align-items-center">
        {{ t('style.file.isAutoUploadTip') }}
      </label>
    </div>
    <div class="row" v-if="isAutoUpload">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.file.url') }}</label>
      <div class="col-sm-9 d-flex align-items-center">
        <template v-if="!apiInfo">
          <button type="button" @click="addApiDialogVisible=true" class="btn btn-light btn-sm">
            {{t('action.notSet')}}
          </button>
        </template>
        <template v-else>
          <button type="button" @click="addApiDialogVisible=true" class="btn btn-light btn-sm">
            <WebAPIBasicInfo :web-api="apiInfo"></WebAPIBasicInfo>
          </button>
          <div @click="bindApiUuid='';apiInfo=null">
            <i class="iconfont hover-danger icon-remove"></i>
          </div>
        </template>
      </div>
      <div class="col-sm-9 offset-sm-3 text-muted">
        {{t('style.file.urlTip')}}
      </div>
    </div>
  </div>
  <!-- API Dialog -->
  <lay-layer v-model="addApiDialogVisible" :title="t('style.file.urlTip')"
             :shade="true" :area="['600px', '60vh']" :btn="buttons">
    <div class="p-2">
      <ImportAPI :default-api="myApiUuid" :is-single="true" @checkAPI="checkedAPI"/>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import ImportAPI from '@/components/common/ImportAPI.vue'
import WebAPIBasicInfo from '@/components/common/WebAPIBasicInfo.vue'
import { computed, onMounted, ref } from 'vue'
import ydhl from '@/lib/ydhl'
import { useStore } from 'vuex'

export default {
  name: 'StyleFile',
  components: { ImportAPI, WebAPIBasicInfo },
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const store = useStore()
    const addApiDialogVisible = ref(false)
    const currPage = computed(() => store.state.design.page)
    const accept = info.computedWrap('accept', 'custom', '')
    const multiple = info.computedWrap('multiple', 'custom', false)
    const isAutoUpload = info.computedWrap('isAutoUpload', 'custom', false)
    const bindApiUuid = info.computedWrap('apiUuid', 'custom', '')
    const myApiUuid = ref('')
    const myApiInfo = ref(null)
    const apiInfo = ref(null)
    const maxFileSize = info.computedWrap('maxFileSize', 'custom', '2mb')

    const checkedAPI = (api) => {
      myApiUuid.value = api.id
      myApiInfo.value = api
    }
    const buttons = computed(() => {
      const btns = [
        {
          text: t('common.ok'),
          callback: () => {
            addApiDialogVisible.value = false
            ydhl.loading(t('common.pleaseWait')).then((id) => {
              ydhl.postJson('api/bind/webapi', { uiid: info.selectedUIItemId.value, page_uuid: currPage.value.meta.id, api_uuid: myApiUuid.value }).then((rst: any) => {
                ydhl.closeLoading(id)
                if (rst.success) {
                  bindApiUuid.value = rst.data.uuid
                  apiInfo.value = myApiInfo.value
                }
              })
            })
          }
        },
        {
          text: t('common.cancel'),
          callback: () => {
            addApiDialogVisible.value = false
          }
        }
      ]
      return btns
    })
    onMounted(() => {
      if (!bindApiUuid.value) return
      ydhl.get('api/bind/apiinfo.json', { uuid: bindApiUuid.value }, (rst) => {
        if (!rst.success) return
        apiInfo.value = rst.data
      }, 'json')
    })
    return {
      ...info,
      bindApiUuid,
      myApiUuid,
      addApiDialogVisible,
      multiple,
      apiInfo,
      isAutoUpload,
      accept,
      maxFileSize,
      buttons,
      checkedAPI,
      t
    }
  }
}
</script>
