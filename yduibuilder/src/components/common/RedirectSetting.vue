<template>
  <div class="flex-grow-1 ps-2 pe-2">
    <div class="row align-items-center">
      <label class="col-sm-3 p-1 col-form-label text-start text-truncate">{{ t('action.redirectType') }}</label>
      <div class="col-sm-9 p-1">
        <template v-if="readonly">{{myAction.redirect_type}}</template>
        <AdvanceSelect v-else :options="redirectTypes" :default-text="myAction.redirect_type" @change="(option)=>chnageRedirectType(option.value)"></AdvanceSelect>
      </div>
    </div>
    <template v-if="myAction.redirect_type=='outside'">
      <div class="row">
        <label class="col-sm-12 p-1 col-form-label text-start text-truncate">{{ t('action.redirectUrl') }}</label>
        <div class="col-sm-12 p-1">
          <template v-if="readonly">{{myAction.redirect}}</template>
          <textarea v-else v-model="myAction.redirect" placeholder="/foo/bar/{data}" class="form-control form-control-sm"></textarea>
          <DataConnect v-for="(item, index) in tplDatas" @updateConnectData="updateConnectData"
                       connect="to" :readonly="readonly"
                       :bound-data="myAction.input" :variables="variables" path="" :root-uuid="item.uuid"
                       :key="index" :intent="0" :model="item" :index="0">
          </DataConnect>
        </div>
      </div>
    </template>
    <template v-else-if="myAction.redirect_type=='inside'">
    <div class="row">
      <label class="col-sm-3 p-1 col-form-label text-start text-truncate">{{ t('action.redirectPage') }}</label>
      <div class="col-sm-9 p-1">
        <template v-if="readonly">{{myAction.popup_page_type=='page' && myAction.popupPageTitle ? myAction.popupPageTitle : t('action.notSet')}}</template>
        <button v-else class="btn btn-light btn-xs" type="button" @click="pagePickDialogVisible=true">
          {{myAction.popup_page_type=='page' && myAction.popupPageTitle ? myAction.popupPageTitle : t('action.notSet')}}
        </button>
      </div>
      <div class="col-sm-12 p-0">
        <DataConnect v-for="(item, index) in pageDatas" @updateConnectData="updateConnectData"
                     connect="to" :readonly="readonly"
                     :bound-data="myAction.input" :variables="variables" path="" :root-uuid="item.uuid"
                     :key="index" :intent="0" :model="item" :index="0">
        </DataConnect>
      </div>
    </div>
  </template>
  </div>
  <lay-layer v-model="pagePickDialogVisible" :title="t('common.page')" :shade="true" :area="['500px', '500px']" :btn="pagePickButtons">
    <div class="p-3">
      <PagePicker :page-types="['page']" :defualt-page-uuid="pickedPageInfo.popupPageId" @update="pickedPage"></PagePicker>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { computed, onMounted, ref, watch, toRef } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import ydhl from '@/lib/ydhl'
import PagePicker from '@/components/common/PagePicker.vue'
import DataConnect from '@/components/common/DataConnect.vue'
import _ from 'lodash'
import { Expression } from '@/store/model'
import AdvanceSelect from '@/components/common/AdvanceSelect.vue'

export default {
  name: 'RedirectSetting',
  components: { AdvanceSelect, DataConnect, PagePicker },
  props: {
    modelValue: Object,
    readonly: Boolean,
    variables: Array,
    autosave: {
      default: true,
      type: Boolean
    } // 是否自动提交接口保存
  },
  emits: ['update:modelValue'],
  setup (props: any, context: any) {
    const store = useStore()
    const { t } = useI18n()
    const pagePickDialogVisible = ref(false)
    const urls = ref([])
    const pageDatas = ref<any>([])
    const tplDatas = ref<any>([])
    const project = computed(() => store.state.design.project)
    const myAction = toRef(props, 'modelValue')
    const pickedPageInfo = ref<any>({ // 用于缓存pickPage中的数据
      popupPageId: myAction.value.popupPageId,
      popupPageTitle: myAction.value.popupPageTitle
    })
    const redirectTypes = ref<any>([
      { name: 'Inside', value: 'inside', desc: 'Redirect to another page' },
      { name: 'Outside', value: 'outside', desc: 'Navigation to external address' }
    ])
    watch(() => myAction.value.redirect, _.debounce((redirect: string) => {
      tplDatas.value = []
      saveRedirect().then(() => {
        update()
      }).catch(() => {
        update()
      })
      if (!redirect) return
      parseTplData(redirect)
    }, 800))
    watch(() => myAction.value.redirect_type, (v) => {
      saveRedirect().then(() => {
        update()
      }).catch(() => {
        update()
      })
    })
    const parseTplData = (redirect) => {
      tplDatas.value = []
      if (!redirect) return
      const match = redirect.match(/\{[^}]+\}/g)
      if (!match) return
      for (const item of match) {
        const name = item.replace(/{|}/g, '')
        tplDatas.value.push({
          uuid: name,
          name,
          type: 'string'
        })
      }
    }
    const pickedPage = (pageTitle, pageUuid) => {
      pickedPageInfo.value.popupPageId = pageUuid
      pickedPageInfo.value.popupPageTitle = pageTitle
    }
    onMounted(() => {
      parseTplData(myAction.value.redirect)
      loadPageData()
    })
    const loadPageData = () => {
      // 重定向时只重定向到页面
      if (myAction.value.popupPageId && myAction.value.popup_page_type === 'page') {
        ydhl.get('api/bind/data.json?data_from=path,query&page_uuid=' + myAction.value.popupPageId, [], (rst: any) => {
          pageDatas.value = rst.data.query || []
          if (rst.data.path) pageDatas.value.push(...rst.data.path)
        }, 'json')
      }
    }

    // eslint-disable-next-line camelcase
    const saveRedirect = () => {
      return new Promise((resolve, reject) => {
        // 某些情况下不需要自动保存，比如在对话框中，因为他总的有个保存按钮
        if (!props.autosave) {
          reject(new Error(''))
          return
        }
        if (myAction.value.uuid) {
          ydhl.postJson('api/action/redirect.json', myAction.value).then((res: any) => {
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
          myAction.value.popupPageId = pickedPageInfo.value.popupPageId
          myAction.value.popupPageTitle = pickedPageInfo.value.popupPageTitle
          myAction.value.popup_page_type = 'page'
          saveRedirect().then(() => {
            update()
          }).catch(() => {
            update()
          })
          loadPageData()
        }
      }
    ])

    const update = () => {
      context.emit('update:modelValue', myAction.value)
    }
    const chnageRedirectType = (value) => {
      myAction.value.redirect_type = value
    }
    // v: { scope, path, data, rootDataId }
    const updateConnectData = (fromRootUuid, fromPath, fromUuid, toData: Expression, remove, toDataDesc: string) => {
      if (remove) {
        delete myAction.value.input
        return
      }
      if (!myAction.value.input) {
        myAction.value.input = {}
      }
      myAction.value.input[fromUuid] = {
        expression: toData,
        desc: toDataDesc
      }
      saveRedirect().then(() => {
        update()
      }).catch(() => {
        update()
      })
    }
    return {
      project,
      pageDatas,
      tplDatas,
      pagePickDialogVisible,
      myAction,
      t,
      urls,
      pagePickButtons,
      pickedPageInfo,
      redirectTypes,
      chnageRedirectType,
      pickedPage,
      updateConnectData
    }
  }
}
</script>
