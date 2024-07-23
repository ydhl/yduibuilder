<template>
  <template v-if="myAction.popupPageId">
    <div class="d-flex align-items-center">
      <div @click="!readonly ? viewPopup() : ''" class="d-flex align-items-center pointer hover-text">
        {{myAction.popupPageTitle}}
        <ConfirmRemove v-if="!readonly" icon="icon-remove" @remove="removePopup"></ConfirmRemove>
      </div>
      <div v-if="!pageDataInline" class="pointer fs-7 ms-3 d-flex align-items-center" @click="!readonly ? openBindPageDataDlg() : ''">
        <div v-if="boundCount>0" class="text-danger fw-bold">
          <i class="iconfont icon-connect fs-7 hover-primary"></i>{{boundCount}}
        </div>
        <template v-else>
          <i class="iconfont icon-connect fs-7 hover-primary text-muted"></i>
        </template>
      </div>
    </div>
  </template>
  <template v-else-if="myAction.popup_type=='alert'">
    <div class="d-inline-flex align-items-center justify-content-start">
      Alert(&nbsp;
      <ExpressionDropdown :readonly="readonly" :variables="variables" @updateExpression="updateBoundAlert" :expression="alertExpression.expression"></ExpressionDropdown>
      <ConfirmRemove icon="icon-remove" v-if="!readonly" @remove="removePopup"></ConfirmRemove>
      &nbsp;)
    </div>
  </template>
  <template v-else-if="!readonly">
    <AdvanceSelect :options="popupTypes" @change="(option)=>clickPopupType(option.value)" :default-text="t('event.error.notDefined')"></AdvanceSelect>
  </template>
  <template v-else>
    {{t('event.error.notDefined')}}
  </template>

  <div class="flex-grow-1" v-if="pageDataInline">
    <DataConnect v-for="(item, index) in pageDatas" @updateConnectData="updateConnectData"
                 connect="to" :readonly="readonly"
                 :bound-data="myAction.input" :variables="variables" path="" :root-uuid="item.uuid"
                 :key="index" :intent="0" :model="item" :index="0">
    </DataConnect>
  </div>

  <lay-layer v-model="pageDataBindDlgVisible" :title="t('variable.bound')" :shade="true" :area="['500px', '500px']" :btn="pageDataBindDlgButtons">
    <div class="p-3">
      <div class="text-muted" v-if="!pageDatas || !pageDatas.length">{{t('common.empty')}}</div>
      <DataConnect v-for="(item, index) in pageDatas" @updateConnectData="updateConnectData"
                   connect="to"
                   :bound-data="myAction.input" :variables="variables" path="" :root-uuid="item.uuid"
                   :key="index" :intent="0" :model="item" :index="0">
      </DataConnect>
    </div>
  </lay-layer>
  <lay-layer v-model="pagePickDialogVisible" :title="t('common.page')" :shade="true" :area="['500px', '500px']" :btn="pagePickButtons">
    <div class="p-3">
      <PagePicker :page-types="['page','popup']" :defualt-page-uuid="pickPageCache.popupPageId" @update="pickedPage"></PagePicker>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import ydhl from '@/lib/ydhl'
import { computed, onMounted, ref, toRef } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
import ConfirmRemove from '@/components/common/ConfirmRemove.vue'
import PagePicker from '@/components/common/PagePicker.vue'
import DataConnect from '@/components/common/DataConnect.vue'
import { Expression } from '@/store/model'
import AdvanceSelect from '@/components/common/AdvanceSelect.vue'
import ExpressionDropdown from '@/components/common/ExpressionDropdown.vue'

export default {
  name: 'PopupSetting',
  components: { ExpressionDropdown, AdvanceSelect, DataConnect, PagePicker, ConfirmRemove },
  props: {
    modelValue: Object, // Action
    pageDataInline: Boolean, // true 页面数据绑定直接展示，false 弹窗展示, 弹窗在关闭时会调用api保存
    variables: Array, // 当前弹窗可以关联的局部变量定义
    readonly: Boolean,
    autosave: {
      default: true,
      type: Boolean
    } // 是否自动提交接口保存
  },
  emits: ['update:modelValue', 'beforeSave'],
  setup (props: any, context: any) {
    const { t } = useI18n()
    const myAction = toRef(props, 'modelValue')
    const myAutosave = ref(props.autosave)
    const pageDataBindDlgVisible = ref(false)
    const pagePickDialogVisible = ref(false)
    const boundData = ref(myAction.value.input)
    const boundDataPageUuid = ref('')
    const popupPageId = ref(myAction.value.popupPageId)
    const pageDatas = ref<any>([])
    const imgSite = ydhl.api
    const store = useStore()
    const router = useRouter()
    const currFunctionId = computed(() => store.state.design.function.id)
    const currPage = computed(() => store.state.design.page)
    const projectKeyId = computed(() => store.state.design.project.keyId)
    const popupTypes = ref([
      { name: t('page.alert'), value: 'alert', desc: t('page.alertDesc') },
      { name: t('page.custom'), value: 'custom', desc: t('page.customDesc') },
      { name: t('page.choosePage'), value: 'choosePage', desc: t('page.choosePageDesc') }
    ])
    const alertExpression = ref(boundData.value ? JSON.parse(JSON.stringify(boundData.value)) : { expression: {}, desc: '' })
    const boundCount = computed(() => {
      if (ydhl.isEmptyObject(myAction.value.input)) return 0
      return Object.keys(myAction.value.input).length
    })
    const updateBoundAlert = (toData: Expression, expDesc: string) => {
      boundData.value = {
        expression: toData,
        desc: expDesc
      }
      myAction.value.input = boundData.value
      context.emit('update:modelValue', myAction.value)
      savePopup(boundData.value, 'alert').finally(() => {
        context.emit('update:modelValue', myAction.value)
      }).catch((e) => {
        context.emit('update:modelValue', myAction.value)
      })
    }

    const pageDataBindDlgButtons = ref([
      {
        text: t('common.ok'),
        callback: () => {
          myAction.value.input = boundData.value
          savePopup(boundData.value, 'page', popupPageId.value).finally(() => {
            pageDataBindDlgVisible.value = false
            context.emit('update:modelValue', myAction.value)
          }).catch(() => {
            context.emit('update:modelValue', myAction.value)
          })
        }
      },
      {
        text: t('common.cancel'),
        callback: () => {
          pageDataBindDlgVisible.value = false
        }
      }
    ])
    const pickPageCache = ref<any>({ // 用于缓存pickPage中的数据
      popupPageId: myAction.value.popupPageId,
      popupPageTitle: myAction.value.popupPageTitle
    })

    const pickedPage = (pageTitle, pageUuid, pageType) => {
      pickPageCache.value.popupPageId = pageUuid
      pickPageCache.value.popupPageTitle = pageTitle
      pickPageCache.value.popup_page_type = pageType
    }
    const pagePickButtons = ref([
      {
        text: t('common.ok'),
        callback: () => {
          pagePickDialogVisible.value = false
          myAction.value.popupPageId = pickPageCache.value.popupPageId
          myAction.value.popupPageTitle = pickPageCache.value.popupPageTitle
          myAction.value.popup_page_type = pickPageCache.value.popup_page_type
          loadPageData()
          savePopup({}, 'page', myAction.value.popupPageId).then((res: any) => {
            context.emit('update:modelValue', myAction.value)
          }).catch(() => {
            context.emit('update:modelValue', myAction.value)
          })
        }
      }
    ])
    const customPopup = () => {
      myAction.value.popup_type = 'page'
      myAction.value.popup_page_type = 'popup'
      context.emit('update:modelValue', myAction.value)
      if (!myAction.value.uuid) { // 对应的action不存在，这时先通知上层组件先把action在后端保存起来; 主要时bindApiPostprocessor在对话框整体保存数据的情况
        myAutosave.value = true
        context.emit('beforeSave', (newAction) => {
          createPopupBind()
        })
      } else {
        createPopupBind()
      }
    }
    const createPopupBind = () => {
      // 创建新对话框页面
      const pageId = ydhl.uuid(5, 0, 'Page' + projectKeyId.value)
      savePopup({}, 'page', pageId).then((res: any) => {
        router.push({
          path: '/',
          query: {
            uuid: res.popupPageId,
            fromPageId: currPage.value.meta.id
          }
        }).catch((e) => {
          console.log(e)
        })
      }).catch((e) => {
        myAction.value.popup_type = 'page'
        myAction.value.popup_page_type = 'popup'
        myAction.value.popupPageTitle = 'unnamed popup'
        myAction.value.popupPageId = pageId
        context.emit('update:modelValue', myAction.value)
        // console.log(e)
      })
    }
    const openPagePickDlg = () => {
      myAction.value.popup_type = 'page'
      context.emit('update:modelValue', myAction.value)
      pagePickDialogVisible.value = true
      pickPageCache.value.popupPageId = myAction.value.popupPageId
      pickPageCache.value.popupPageTitle = myAction.value.popupPageTitle
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
      }).catch((e) => {
        console.log(e)
      })
    }
    const openBindPageDataDlg = () => {
      pageDataBindDlgVisible.value = true
    }

    const removePopup = () => {
      savePopup({}, 'page').finally(() => {
        myAction.value.popupPageId = ''
        myAction.value.popup_type = 'page'
        pageDatas.value = []
        context.emit('update:modelValue', myAction.value)
      }).catch((e) => {
        console.log(e)
      })
    }
    // eslint-disable-next-line camelcase
    const savePopup = (input, popup_type, popupPageId = '') => {
      return new Promise((resolve, reject) => {
        // 某些情况下不需要自动保存，比如在对话框中，因为他总的有个保存按钮
        if (!myAutosave.value) {
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
    const loadPageData = () => {
      if (myAction.value.popupPageId) {
        ydhl.get('api/bind/data.json?data_from=path,query&page_uuid=' + myAction.value.popupPageId, [], (rst: any) => {
          pageDatas.value = rst?.data?.query || []
          if (rst?.data?.path) pageDatas.value.push(...rst.data.path)
        }, 'json')
      }
    }
    // 弹窗的数据绑定保存在action的input中，
    const updateConnectData = (fromRootUuid, fromPath, fromUuid, toData: Expression, remove, expDesc: string) => {
      if (remove) {
        boundData.value = null
      } else {
        if (!boundData.value) {
          boundData.value = {}
        }
        boundData.value[fromUuid] = { expression: toData, desc: expDesc }
      }
      myAction.value.input = boundData.value
      context.emit('update:modelValue', myAction.value)
    }
    onMounted(() => {
      loadPageData()
    })
    const clickPopupType = (type) => {
      switch (type) {
        case 'alert':
          useAlert()
          break
        case 'custom':
          customPopup()
          break
        case 'choosePage':
          openPagePickDlg()
          break
      }
    }

    return {
      t,
      currPage,
      imgSite,
      removePopup,
      myAction,
      useAlert,
      boundCount,
      pagePickDialogVisible,
      pageDataBindDlgVisible,
      updateBoundAlert,
      pageDataBindDlgButtons,
      boundData,
      pagePickButtons,
      boundDataPageUuid,
      alertExpression,
      popupPageId,
      pageDatas,
      pickPageCache,
      popupTypes,
      openBindPageDataDlg,
      customPopup,
      openPagePickDlg,
      viewPopup,
      clickPopupType,
      updateConnectData,
      pickedPage
    }
  }
}
</script>
