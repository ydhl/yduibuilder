<template>
  <template v-if="myAction.popupPageId">
    <div @click="viewPopup()" class="btn d-flex align-items-center btn-white btn-sm popup-review" :style="`background-image:url(${imgSite}api/screenshot?pageid=${myAction.popupPageId})`">
      {{myAction.popupPageTitle}}
      <ConfirmRemove @remove="removePopup"></ConfirmRemove>
    </div>
  </template>
  <template v-else-if="myAction.popup_type=='alert'">
    <div class="d-flex align-items-center">
      Alert&nbsp;(&nbsp;<button type="button" class="btn btn-light btn-sm" @click="openVariable">{{alertVariable || t('action.notSet')}}</button>&nbsp;)
      <ConfirmRemove @remove="removePopup"></ConfirmRemove>
    </div>
  </template>
  <div v-else class="dropdown">
    <button class="btn btn-white btn-sm d-flex align-content-center text-truncate justify-content-center"
            data-bs-toggle="dropdown"><span class="text-danger">{{t('event.error.notDefined')}}<i class="dropdown-toggle ms-1"></i></span> </button>
    <ul class="dropdown-menu dropdown-menu-end">
      <li><a class="dropdown-item" href="javascript:;" @click="useAlert()">{{t('page.alert')}}</a></li>
      <li><a class="dropdown-item" href="javascript:;" @click="customPopup()">{{t('page.custom')}}</a></li>
      <li><a class="dropdown-item" href="javascript:;" @click="choosePage()">{{t('page.choosePage')}}</a></li>
    </ul>
  </div>
  <lay-layer v-model="variableDialogVisible" :title="t('variable.bound')" :shade="true" :area="['500px', '500px']" :btn="variableDlgButtons">
    <div class="p-3">
      <VariableBound :local-variables="variables" @updateCheckedUuid="updateCheckedUuid" :checked-uuid="boundVariableUuid?.data_uuid" :page-uuid="currPage.meta.id"></VariableBound>
    </div>
  </lay-layer>
  <lay-layer v-model="pagePickDialogVisible" :title="t('common.page')" :shade="true" :area="['500px', '500px']" :btn="pagePickButtons">
    <div class="p-3">
      <PagePicker :defualt-page-uuid="popupPageId" @update="pickedPage"></PagePicker>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import ydhl from '@/lib/ydhl'
import { computed, ref } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
import ConfirmRemove from '@/components/common/ConfirmRemove.vue'
import VariableBound from '@/components/common/VariableBound.vue'
import PagePicker from '@/components/common/PagePicker.vue'

export default {
  name: 'PopupSetting',
  components: { PagePicker, VariableBound, ConfirmRemove },
  props: {
    modelValue: Object, // Action
    autoSave: Boolean, // true 时改变值就保存
    variables: Array // 当前弹窗可以关联的局部变量定义
  },
  emits: ['update:modelValue', 'beforeCreatePopupBind'],
  setup (props: any, context: any) {
    const { t } = useI18n()
    const myAction = ref(props.modelValue)
    const variableDialogVisible = ref(false)
    const pagePickDialogVisible = ref(false)
    const boundVariableUuid = ref(myAction.value.input)
    const popupPageId = ref(myAction.value.popupPageId)
    const imgSite = ydhl.api
    const store = useStore()
    const router = useRouter()
    const currFunctionId = computed(() => store.state.design.function.id)
    const currPage = computed(() => store.state.design.page)
    const alertVariable = computed(() => myAction.value.input?.path || null)
    const projectKeyId = computed(() => store.state.design.project.keyId)
    const customPopup = () => {
      myAction.value.popup_type = 'page'
      context.emit('update:modelValue', myAction.value)
      if (!myAction.value.uuid) { // 对应的action不存在，这时先通知上层组件先把action在后端保存起来
        context.emit('beforeCreatePopupBind', (newAction) => {
          myAction.value = newAction
          createPopupBind()
        })
      } else {
        createPopupBind()
      }
    }
    const createPopupBind = () => {
      // 创建新对话框页面
      savePopup([], 'page', ydhl.uuid(5, 0, 'Page' + projectKeyId.value)).then((res: any) => {
        router.push({
          path: '/',
          query: {
            uuid: res.popupPageId,
            fromPageId: currPage.value.meta.id
          }
        })
      })
    }
    const pickedPage = (pageName, pageUuid) => {
      savePopup({}, 'page', pageUuid).then((res: any) => {
        myAction.value.popupPageId = res.popupPageId
        myAction.value.popupPageTitle = res.popupPageTitle
        context.emit('update:modelValue', myAction.value)
      }).catch(() => {
        myAction.value.popupPageId = pageUuid
        myAction.value.popupPageTitle = pageName
        context.emit('update:modelValue', myAction.value)
      })
    }
    const choosePage = () => {
      myAction.value.popup_type = 'page'
      context.emit('update:modelValue', myAction.value)
      pagePickDialogVisible.value = true
    }
    const viewPopup = () => {
      // 已经有了对话框页面
      if (myAction.value.popupPageId) {
        router.push({
          path: '/',
          query: {
            uuid: myAction.value.popupPageId,
            fromPageId: currPage.value.meta.id
          }
        })
      }
    }
    const useAlert = () => {
      savePopup({}, 'alert').finally(() => {
        myAction.value.popup_type = 'alert'
        myAction.value.popupPageId = ''
        context.emit('update:modelValue', myAction.value)
      })
    }
    const openVariable = () => {
      variableDialogVisible.value = true
    }
    const removePopup = () => {
      savePopup({}, 'page').finally(() => {
        myAction.value.popupPageId = ''
        myAction.value.popup_type = ''
        context.emit('update:modelValue', myAction.value)
      })
    }
    const updateCheckedUuid = ({ scope, path, data }) => {
      boundVariableUuid.value = { scope, data_uuid: data.uuid, path }
    }
    const variableDlgButtons = ref([
      {
        text: t('common.ok'),
        callback: () => {
          savePopup(boundVariableUuid.value, 'alert').finally(() => {
            variableDialogVisible.value = false
            myAction.value.input = boundVariableUuid.value
            context.emit('update:modelValue', myAction.value)
          })
        }
      },
      {
        text: t('common.cancel'),
        callback: () => {
          variableDialogVisible.value = false
        }
      }
    ])
    // eslint-disable-next-line camelcase
    const savePopup = (input, popup_type, popupPageId = '') => {
      return new Promise((resolve, reject) => {
        if (!props.autoSave) {
          reject(new Error(''))
          return
        }
        if (myAction.value.uuid) {
          ydhl.postJson('api/action/popup.json',
            { action_uuid: myAction.value.uuid, input, popupPageId, function_uuid: currFunctionId.value, popup_type }).then((res: any) => {
            if (!res.success) {
              ydhl.alert(res.msg || t('common.operationFail'), t('common.ok'))
              reject(new Error(''))
              return
            }
            resolve(res.data)
          }).catch((rst) => {
            ydhl.alert(rst || t('common.operationFail'), t('common.ok'))
            reject(rst)
          })
        } else {
          reject(new Error(''))
        }
      })
    }

    const pagePickButtons = ref([
      {
        text: t('common.ok'),
        callback: () => {
          pagePickDialogVisible.value = false
        }
      }
    ])
    return {
      t,
      currPage,
      imgSite,
      removePopup,
      myAction,
      useAlert,
      pagePickDialogVisible,
      variableDialogVisible,
      variableDlgButtons,
      alertVariable,
      boundVariableUuid,
      pagePickButtons,
      popupPageId,
      openVariable,
      customPopup,
      choosePage,
      updateCheckedUuid,
      viewPopup,
      pickedPage
    }
  }
}
</script>
<style scoped>
.popup-review{
  background-repeat: no-repeat; background-position:center; background-size: contain; height: 30px;
}
</style>
