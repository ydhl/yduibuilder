<template>
  <div @click="!readonly ? openApiDialog() : ''" class="text-start pointer hover-text text-truncate">
    <template v-if="myAction.bindApi">
      <WebAPIBasicInfo :web-api="myAction.bindApi"></WebAPIBasicInfo>
    </template>
    <template v-else>
      <div class="btn btn-xs btn-light">{{ t('action.api.notBindAnyApi') }}</div>
    </template>
  </div>
  <!-- API Dialog -->
  <lay-layer v-model="addApiDialogVisible" :title="t('event.bindAPI')" :shade="true" :area="['80vw', '60vh']" :btn="bindAPIButtons">
    <div  class="p-2">
      <ImportAPI :default-api="myAction.bindApi?.apiUuid" :is-single="true" @checkAPI="checkedAPI"/>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { computed, ref, toRef } from 'vue'
import { useI18n } from 'vue-i18n'
import ydhl from '@/lib/ydhl'
import { useStore } from 'vuex'
import ImportAPI from '@/components/common/ImportAPI.vue'
import WebAPIBasicInfo from '@/components/common/WebAPIBasicInfo.vue'

export default {
  name: 'WebAPISetting',
  components: { WebAPIBasicInfo, ImportAPI },
  props: {
    modelValue: Object,
    readonly: Boolean,
    // 绑定api的类型（谁绑定的api） bind_api_action bind_event
    bindType: String,
    // 绑定的api的绑定对象uuid（谁绑定的api）
    bindUuid: String,
    autosave: {
      default: true,
      type: Boolean
    } // 是否自动提交接口保存
  },
  emits: ['update:modelValue'],
  setup (props: any, context: any) {
    const myAction = toRef(props, 'modelValue')
    const { t } = useI18n()
    const store = useStore()
    const addApiDialogVisible = ref<any>(false)
    const selectedPage = computed(() => store.state.design.page)
    const selectedPageId = computed(() => selectedPage.value?.meta?.id)

    const checkApi = ref<any>({})// 临时记录选中的api信息
    const checkedAPI = (api) => {
      checkApi.value = api
    }
    const bindAPIButtons = computed(() => {
      const btns = [
        {
          text: t('common.add'),
          callback: () => {
            addApiDialogVisible.value = false
            if (!checkApi.value.id) return // 没有选择
            if (!props.autosave) {
              update()
              return
            }
            ydhl.loading(t('common.pleaseWait')).then((id) => {
              ydhl.postJson('api/action/webapi.json',
                {
                  page_uuid: selectedPageId.value,
                  action_uuid: myAction.value.uuid,
                  api_uuid: checkApi.value.id,
                  bind_type: props.bindType,
                  bind_uuid: props.bindUuid
                }).then((rst) => {
                ydhl.closeLoading(id)
                update()
              })
            })
          }
        },
        {
          text: t('common.cancel'),
          callback: () => {
            addApiDialogVisible.value = false
          }
        },
        {
          text: t('common.delete'),
          callback: () => {
            addApiDialogVisible.value = false
            checkApi.value = {}
            if (!props.autosave) {
              update()
              return
            }
            ydhl.loading(t('common.pleaseWait')).then((id) => {
              ydhl.postJson('api/action/webapi.json',
                { page_uuid: selectedPageId.value, action_uuid: myAction.value.uuid, api_uuid: '' }).then((rst) => {
                ydhl.closeLoading(id)
                update()
              })
            })
          }
        }
      ]
      if (!myAction.value.bindApi) {
        btns.splice(2, 1)
      }
      return btns
    })
    const update = () => {
      if (ydhl.isEmptyObject(checkApi.value)) {
        myAction.value.bindApi = null
      } else {
        const data = JSON.parse(JSON.stringify(checkApi.value))
        data.apiUuid = data.id
        delete data.id
        myAction.value.bindApi = data
      }
      context.emit('update:modelValue', myAction.value)
    }
    const openApiDialog = () => {
      addApiDialogVisible.value = true
    }

    return {
      myAction,
      addApiDialogVisible,
      t,
      bindAPIButtons,
      checkApi,
      checkedAPI,
      openApiDialog
    }
  }
}
</script>
