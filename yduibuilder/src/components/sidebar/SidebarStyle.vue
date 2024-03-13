<template>
  <div class="d-flex align-items-center justify-content-between p-3">
    <i class="iconfont icon-plus hover-primary" @click="openDefineDialog">{{t('style.addSelector')}}</i>
    <i class="iconfont icon-cleanup hover-primary"  @click="cleanup">{{t('common.cleanup')}}</i>
  </div>
  <template v-if="loading">
    {{t('page.loading')}}
  </template>
  <div class="p-2" v-else>
    <div v-for="(selector, index) in selectors" :key="index">
      <div class="mb-2 text-muted">{{selector.text}}</div>
      <div v-for="(item, iindex) in selector.children" :key="iindex">
        <div class="d-flex align-items-center justify-content-between"  v-if="!renameSelectorId" @mouseover="currHoverSelectorId = item.id" @mouseout="currHoverSelectorId = ''">
          <button type="button" @click="loadStyle(item.id)"
                  class="btn btn-sm btn-success">{{item.text}}</button>
          <div :class="{'btn-group btn-group-sm': true, 'invisible': currHoverSelectorId!=item.id}">
            <button type="button" @click="loadUsedInfo(item.id)" class="btn btn-xs btn-light"><i class="iconfont icon-tips hover-primary"></i></button>
            <button type="button" @click="renameSelectorId = item.id;className = item.text" class="btn btn-xs btn-light"><i class="iconfont icon-rename hover-primary"></i></button>
            <button type="button" @click="openEditDialog(item.id, item.text)" class="btn btn-xs btn-light"><i class="iconfont icon-edit hover-primary"></i></button>
          </div>
        </div>
        <div class="d-flex align-items-center justify-content-between" v-if="renameSelectorId==item.id">
          <input type="text" class="form-control form-control-sm" v-model="className" @keyup.enter="renameSelector(item.id)">
          <div class="btn-group btn-group-sm">
            <button type="button" @click="renameSelector(item.id)" class="btn btn-xs btn-light"><i class="iconfont icon-ok hover-primary"></i></button>
            <button type="button" @click="renameSelectorId = '';className=''" class="btn btn-xs btn-light"><i class="iconfont icon-delete hover-primary"></i></button>
          </div>
        </div>
        <div class="fs-7">{{currHoverSelectorId!=item.id ? '&nbsp;' : item.desc}}</div>
      </div>
    </div>
  </div>
  <lay-layer v-model="selectorDefineDialogVisible" :title="t('style.selectorDefine')" :shade="true" :area="['800px', '80vh']" :btn="buttons">
    <div class="p-2 d-flex">
      <div class="w-50 d-flex flex-column align-items-center" style="height: calc(80vh - 200px)">
        <div class="border-1 border" style="width: 200px;height: 200px">
          <div :style="previewStyle" :class="previewClass">Style Preview</div>
        </div>
      </div>
      <div class="style-panel w-50" style="overflow-y:auto;overflow-x:hidden;height: calc(80vh - 200px)">
        <Typography :preview-mode="true"></Typography>
        <StyleBackground :preview-mode="true"></StyleBackground>
        <StyleLayout :preview-mode="true"></StyleLayout>
        <StyleSize :preview-mode="true"></StyleSize>
        <StyleMarginPadding :preview-mode="true"></StyleMarginPadding>
        <StyleBorder :preview-mode="true"></StyleBorder>
        <StyleUtilities :preview-mode="true"></StyleUtilities>
      </div>
    </div>
    <div class="ps-5 pe-5">
      <div class="text-muted">{{t('style.selectorName')}}:</div>
      <input class="form-control form-control-sm" type="text" v-model="className" >
    </div>
  </lay-layer>
  <lay-layer v-model="styleCodeDialogVisible" :title="t('style.selectorDefine')" :shade="true" :area="['600px', '80vh']">
    <div id="styleEditor"  style="height: calc(80vh - 80px)"></div>
  </lay-layer>
  <lay-layer v-model="usedInfoDialogVisible" title="YDECloud" :shade="true" :area="['300px', '400px']" :offset="['60px', '60px']">
      <div class="p-3">
        <div class="text-muted">{{t('style.elementsAffectedOnThisPage')}}</div>
        <div class="list-group">
          <template v-if="usedInfo.thisPage?.length">
            <a href="javascript:void(0)" class="list-group-item list-group-item-action justify-content-between d-flex align-items-center"
               @mouseover="highlight([item.uuid])" @mouseleave="offlight()"
               v-for="(item, index) in usedInfo.thisPage" :key="index">
              <div>
                <i :class="`iconfont text-primary icon-${item.type.toLowerCase()}`"></i>&nbsp;{{ item.title || item.type }}
              </div>
            </a>
          </template>
          <div v-else class="list-group-item">{{t('common.none')}}</div>
        </div>
        <div class="text-muted mt-3">{{t('style.elementsAffectedOnOtherPage')}}</div>
        <div class="list-group">
          <template v-if="usedInfo.otherPage?.length">
            <a href="javascript:void(0)" @click="gotoPage(item.uuid)" class="list-group-item list-group-item-action justify-content-between d-flex align-items-center"
               v-for="(item, index) in usedInfo.otherPage" :key="index">
              {{ item.title }}
            </a>
          </template>
          <div v-else class="list-group-item">{{t('common.none')}}</div>
        </div>
        <div class="text-muted mt-3">{{t('style.componentsAffected')}}</div>
        <div class="list-group">
          <template v-if="usedInfo.component?.length">
            <div class="list-group-item"
               v-for="(item, index) in usedInfo.component" :key="index">
              <i :class="`iconfont text-primary icon-${item.type.toLowerCase()}`"></i>&nbsp;{{ item.title || item.type }}
            </div>
          </template>
          <div v-else class="list-group-item">{{t('common.none')}}</div>
        </div>
      </div>
    </lay-layer>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import initUI from '@/components/Common'
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useStore } from 'vuex'
import Typography from '@/components/sidebar/style/Typography.vue'
import StyleBackground from '@/components/sidebar/style/Background.vue'
import StyleLayout from '@/components/sidebar/style/Layout.vue'
import StyleSize from '@/components/sidebar/style/Size.vue'
import StyleMarginPadding from '@/components/sidebar/style/MarginPadding.vue'
import StyleBorder from '@/components/sidebar/style/Border.vue'
import StyleUtilities from '@/components/sidebar/style/Utilities.vue'
import baseUIDefines from '@/components/ui/define'
import UIBase from '@/components/ui/js/UIBase'
import ydhl from '@/lib/ydhl'
import * as monaco from 'monaco-editor'
import { useRouter } from 'vue-router'

export default {
  name: 'SidebarStyle',
  components: { Typography, StyleBackground, StyleLayout, StyleMarginPadding, StyleBorder, StyleUtilities, StyleSize },
  setup (props: any, context: any) {
    const { t } = useI18n()
    const info = initUI()
    const store = useStore()
    let editorInstance: any = null
    const selectorDefineDialogVisible = ref(false)
    const styleCodeDialogVisible = ref(false)
    const usedInfoDialogVisible = ref(false)
    const usedInfo = ref({})
    const project = computed(() => store.state.design.project)
    const className = ref('')
    const renameSelectorId = ref('')
    const loading = ref(true)
    const selectors = ref([])
    const router = useRouter()
    const types = baseUIDefines
    const currHoverSelectorId = ref('')
    watch(styleCodeDialogVisible, (v) => {
      if (!v) {
        editorInstance = null
      }
    })

    // 高亮显示绑定的元素
    const highlight = (uuids = []) => {
      store.commit('updatePageState', { highlightUIItemIds: uuids })
    }
    const offlight = () => {
      store.commit('updatePageState', { highlightUIItemIds: [] })
    }

    const gotoPage = (pid) => {
      router.push({
        path: '/',
        query: {
          uuid: pid
        }
      })
    }
    const openDefineDialog = () => {
      store.commit('updatePageState', { selectedUIItemId: '' })
      store.commit('updateState', { rightSidebarIsOpen: false, previewStyleItem: {} })
      selectorDefineDialogVisible.value = true
    }
    const cleanup = () => {
      ydhl.confirm(t('style.cleanupTip'), t('common.cleanup'), t('common.cancel')).then((dialogID) => {
        ydhl.closeLoading(dialogID)
        ydhl.post('api/style/cleanup.json', { project_uuid: project.value.id }, [], () => {
          loadSelector()
        })
      })
    }
    const renameSelector = (uuid) => {
      if (!className.value) {
        ydhl.alert(t('style.selectorNameTip'))
        return
      }
      ydhl.post('api/style/rename.json', { uuid, name: className.value }, [], () => {
        className.value = ''
        renameSelectorId.value = ''
        loadSelector()
      })
    }
    const loadUsedInfo = (uuid) => {
      usedInfo.value = {}
      ydhl.get('api/style/used.json', { uuid, page_uuid: info.selectedPageId.value }, (rst) => {
        usedInfo.value = rst.data || {}
        usedInfoDialogVisible.value = true
      }, 'json')
    }
    const openEditDialog = (id, name) => {
      ydhl.loading(t('common.pleaseWait')).then((dialogId: any) => {
        ydhl.get('api/style/detail.json', { uuid: id }, (rst) => {
          ydhl.closeLoading(dialogId)
          if (!rst.success) {
            ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
            return
          }
          store.commit('updatePageState', { selectedUIItemId: '' })
          store.commit('updateState', { rightSidebarIsOpen: false, previewStyleItem: rst.data })
          selectorDefineDialogVisible.value = true
          className.value = name
        })
      })
    }
    const loadStyle = (id) => {
      ydhl.loading(t('common.pleaseWait')).then((dialogId: any) => {
        ydhl.get('api/style/code.json', { uuid: id }, (rst) => {
          ydhl.closeLoading(dialogId)
          if (!rst.success) {
            ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
            return
          }
          nextTick(() => {
            // editor.getAction('editor.action.formatDocument').run()
            editorInstance = monaco.editor.create(document.getElementById('styleEditor') as HTMLElement, {
              roundedSelection: true,
              scrollBeyondLastLine: false,
              readOnly: true,
              language: 'css'
            })
            editorInstance.setValue(rst.data)
          })
          styleCodeDialogVisible.value = true
        })
      })
    }
    const previewStyle = computed(() => {
      const uibase = new UIBase(props, context, store)
      return uibase.getUIStyle(info.previewStyleItem.value)
    })
    const previewClass = computed(() => {
      const uibase = new UIBase(props, context, store)
      return Object.values(uibase.getUICss(info.previewStyleItem.value))
    })

    const buttons = ref([
      {
        text: t('common.save'),
        callback: () => {
          if (!className.value) {
            ydhl.alert(t('style.selectorNameTip'))
            return
          }
          ydhl.postJson('api/style/save.json', { class_name: className.value, meta: JSON.parse(JSON.stringify(info.previewStyleItem.value.meta)), page_uuid: info.selectedPageId.value }).then(function (rst: any) {
            if (!rst.success) {
              ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
              return
            }
            loadSelector()
            selectorDefineDialogVisible.value = false
          })
        }
      },
      {
        text: t('common.cancel'),
        callback: () => {
          selectorDefineDialogVisible.value = false
        }
      }
    ])
    const loadSelector = () => {
      ydhl.get('api/style.json', { page_uuid: info.selectedPageId.value }, function (rst) {
        loading.value = false
        selectors.value = rst.data
      }, 'json')
    }
    onMounted(() => {
      loadSelector()
    })
    return {
      t,
      loading,
      selectorDefineDialogVisible,
      styleCodeDialogVisible,
      usedInfoDialogVisible,
      openDefineDialog,
      openEditDialog,
      loadUsedInfo,
      renameSelector,
      loadStyle,
      cleanup,
      gotoPage,
      renameSelectorId,
      highlight,
      offlight,
      currHoverSelectorId,
      buttons,
      previewStyle,
      selectors,
      className,
      usedInfo,
      previewClass,
      types,
      ...info
    }
  }
}
</script>
