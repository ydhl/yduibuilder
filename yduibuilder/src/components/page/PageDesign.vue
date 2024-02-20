<template>
  <div class="page-container" :style="containerStyle">
    <!--页面的工具按钮区域-->
    <div class="page-action" ref="pageAction" :style="actionStyle">
      <small class="text-success fw-bolder" v-if="isHomePage">{{t('page.homePage')}}</small>
      <div class="text-danger" v-if="page?.pageType=='component'" data-bs-toggle="tooltip" :title="t('common.uicomponentEditTip')">
        <i class="iconfont icon-uicomponent"></i>
      </div>
      <div class="flex-grow-1 text-truncate" @click="isInEditPageTitle=true">
        <input type="text" :readonly="!isInEditPageTitle"
               :class="{'text-secondary user-select-none': !isInEditPageTitle,'form-control-sm form-control border-0 bg-transparent shadow-none': true}" v-model="title"
               @blur="isInEditPageTitle=false" :title="t('page.editPage')">
      </div>
      <div class="item" data-bs-toggle="tooltip" :title="t('page.copyPage')" @click="copyPage"><i class="iconfont icon-copy"></i></div>
      <div class="item" data-bs-toggle="tooltip" :title="t('page.deletePage')" @click="deletePage"><i class="iconfont icon-remove"></i></div>
      <div class="item" data-bs-toggle="tooltip" :title="t('page.code')" @click="openExportCodeDialog"><i class="iconfont icon-code"></i></div>
    </div>
    <!--控制页面的缩放-->
    <div :style="wrapperStyle + wrapperHeight" :id="'wrapper' + uiconfig.meta.id">
      <div :class="{'page shadow-sm': true, 'simulate-border': simulateModel!='pc'}" ref="page" :style="pageStyle">
        <iframe :height="contentHeight" style="user-select: none;display: block" :title="uiconfig.meta.title" :id="uiconfig.meta.id" width="100%" :src="pageUrl"/>
      </div>
    </div>
  </div>
  <!-- Dialog -->
  <template v-if="exportDialogVisible" >
    <teleport to="body">
      <div style="z-index: 1040;position: absolute;top:0;left:0px;right: 0px">
        <div class="card m-3 shadow-lg user-select-none">
          <div class="card-header d-flex justify-content-between">
            <ul class="nav nav-tabs card-header-tabs" v-if="codeTypes">
              <li class="nav-item" v-for="(language,type) in codeTypes" :key="type">
                <a :class="['nav-link', {'active': currCodeType==type}]" href="javascript:;" @click="loadCode(type, language)">{{type}}</a>
              </li>
            </ul>
            <span v-if="!codeTypes">{{t('page.code')}}</span>
            <button type="button" class="btn btn-light btn-sm" @click="exportDialogVisible = false" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div id="exportedCodeEditor" style="height: 500px"></div>
        </div>
      </div>
    </teleport>
  </template>
</template>

<script lang="ts">
import UIInit from '@/components/Common'
import { ref, watch, computed, nextTick, Ref, onUpdated, onMounted } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import * as monaco from 'monaco-editor'
import ydhl from '@/lib/ydhl'
import { YDJSStatic } from '@/lib/ydjs'
import { useRoute } from 'vue-router'
declare const YDJS: YDJSStatic

export default {
  name: 'PageDesign',
  props: {
    uiconfig: Object
  },
  setup (props: any, ctx: any) {
    const wrapperStyle = ref('')
    const actionStyle = ref('')
    const containerStyle = ref('')
    const page: Ref<Element | null> = ref(null)
    const route = useRoute()
    const store = useStore()
    const isReady = ref(false)
    const exportDialogVisible = ref(false)
    const { t } = useI18n()
    watch(exportDialogVisible, function (v) {
      store.commit('updateState', { backdropVisible: v })
      if (!v) editorInstance = null
    })
    let editorInstance

    const simulateWidth = computed(() => store.state.design.simulateWidth)
    const pageScale = computed(() => store.state.design.scale)
    const versionId = computed(() => store.state.design.pageVersionId[props.uiconfig.meta.id])
    const codeTypes = computed(() => store.state.design.codeTypes)
    const currCodeType = ref('')
    watch(codeTypes, (v) => {
      if (codeTypes.value) {
        currCodeType.value = Object.keys(codeTypes.value)[0]
      }
    }, { immediate: true })

    const currFunctionId = computed(() => store.state.design.function.id)
    const simulateModel = computed(() => store.state.design.simulateModel || 'pc')
    const minHeight = computed(() => {
      if (simulateModel.value === 'tablet') {
        return 1024
      } else if (simulateModel.value === 'portrait') {
        return 720
      }
      return 768
    })

    /**
     * 计算page的Style，其中不包含高度，高度由浏览器自适应
     */
    const pageStyle = computed(() => {
      let width: any
      const minH = minHeight.value

      if (simulateModel.value === 'tablet') {
        width = 768
      } else if (simulateModel.value === 'portrait') {
        width = 576
      } else {
        const leftPanel = document.getElementById('left-panel')
        const leftPanelWidth = leftPanel ? leftPanel.getBoundingClientRect().width : 60 // 和scss中的$leftPanelWidth保持一致
        const pageWidth = simulateWidth.value // 模拟页面的宽度，0表示自适应的
        width = pageWidth || (document.body.clientWidth - leftPanelWidth - 100) // 100是workspace body左右的间距
      }
      // console.log(`transform:scale(${pageScale.value});width:${width}px;`)
      return `transform:scale(${pageScale.value});width:${width}px;min-height:${minH}`
    })

    /**
     * 在页面第一次加载时，watch pageStyle 会先触发，但这时UILoader并没有把所有的页面元素加载出来，所以这时
     * page.value.getBoundingClientRect()返回的高度只有1，并不是实际的页面高度，导致wrapperStyle计算出错，然后UILoader
     * 才把元素加载出来，导致页面重叠错乱，这里通过isReady，默认是false，这样在pageStyle更新时，不触发watch pageStyle
     * 当页面元素都渲染出来后（由于uiloader已经对元素做了缓存处理，不会导致组件重新情况渲染），这时pageStyle改变时page.value.getBoundingClientRect()才是实际的页面尺寸
     */
    onUpdated(() => {
      // console.log('onUpdated')
      isReady.value = true
    })

    const refreshStyle = () => {
      // console.log('pageStyle changed isReady true nextTick')
      nextTick(() => {
        if (page.value) {
          const pageRect = page.value.getBoundingClientRect()
          // console.log(pageRect)
          wrapperStyle.value = `width:${pageRect.width}px;` // 25px 是页面间的排版间距
          actionStyle.value = `width:${pageRect.width}px`
          containerStyle.value = `width:${pageRect.width}px`
          isReady.value = false
        }
      })
    }

    // + 2 是为了让selected状态都outline都显示出来
    const contentHeight = computed(() => {
      let height = store.state.design.pageContentHeight?.[props.uiconfig.meta.id]
      if (!height || height <= minHeight.value) height = minHeight.value
      return height + (simulateModel.value !== 'pc' ? 2 : 0)
    })

    const wrapperHeight = computed(() => {
      const simulateExtraHeight = simulateModel.value !== 'pc' ? 57 + 80 : 0 // tablet mobile模拟器额外都高度， 由.simulate-border 都padding定义
      return `;height:${pageScale.value * (contentHeight.value + simulateExtraHeight) + 25}px`
    })
    /**
     * watch pageStyle和contentHeight，有变化时刷新wrapper等样式
     */
    watch(pageStyle, () => {
      if (!isReady.value) return
      // console.log('pageStyle changed isReady true')
      // 当scale刷新后等scale作用到page上后在计算，主要是里面需要用到getBoundingClientRect
      refreshStyle()
    })

    onMounted(() => {
      const timer = setInterval(function () {
        // 判断文档和所有子资源是否已完成加载
        if (document.readyState === 'complete') {
          refreshStyle()
          window.clearInterval(timer)
        }
      }, 100)
    })

    const isInEditPageTitle = ref(false)
    const title = computed({
      get () {
        return props.uiconfig.meta.title
      },
      set (v) {
        store.commit('updatePage', { pageId: props.uiconfig.meta.id, props: { meta: { title: v } } })
      }
    })

    const copyPage = () => {
      // console.log(props)
      ydhl.confirm(t('common.copyPageConfirm'), t('common.copy'), t('common.cancel')).then((dialogid) => {
        ydhl.closeLoading(dialogid)
        store.commit('copyPage', { pageid: props.uiconfig.meta.id })
      })
    }

    const loadCode = (type, language) => {
      const loadingId = YDJS.loading(t('common.pleaseWait'))
      currCodeType.value = type
      ydhl.get('code/page/' + props.uiconfig.meta.id + '?code_type=' + currCodeType.value, {}, (code) => {
        exportDialogVisible.value = true
        // console.log(code)
        YDJS.hide_dialog(loadingId)
        nextTick(() => {
          // editor.getAction('editor.action.formatDocument').run()
          if (!editorInstance) {
            editorInstance = monaco.editor.create(document.getElementById('exportedCodeEditor') as HTMLElement, {
              roundedSelection: true,
              scrollBeyondLastLine: false,
              readOnly: true,
              language: language || 'html'
            })
          }
          editorInstance.setValue(code)
          monaco.editor.setModelLanguage(editorInstance.getModel(), language || 'html')
        })
      }, 'html')
    }
    const openExportCodeDialog = function () {
      const loadingId = YDJS.loading(t('common.pleaseWait'))
      ydhl.savePage(currFunctionId.value, props.uiconfig, versionId.value, (rst) => {
        if (rst?.success) {
          store.commit('updateSavedState', { pageUuid: props.uiconfig.meta.id, saved: 1, versionId: rst.data.versionId })
        }
        YDJS.hide_dialog(loadingId)
        loadCode(currCodeType.value, null)
      })
    }
    const deletePage = function () {
      YDJS.confirm(t('page.deletePageConfirm'), '', (dialogid) => {
        YDJS.hide_dialog(dialogid)
        store.commit('deletePage', { pageid: props.uiconfig.meta.id })
      }, '', t('page.deletePage'), t('common.cancel'))
    }
    const isHomePage = computed(() => props.uiconfig.meta?.custom?.isHomePage)
    const isPopup = computed(() => store.state.design.page.pageType === 'popup' || store.state.design.page.pageType === 'subpage')
    const pageUrl = computed(() => {
      let url = '/page?uuid=' + props.uiconfig.meta.id
      for (const queryKey in route?.query) {
        if (queryKey === 'uuid') continue
        url += `&${queryKey}=${route.query[queryKey]}`
      }
      return url
    })
    return {
      ...UIInit(),
      title,
      isInEditPageTitle,
      t,
      actionStyle,
      wrapperStyle,
      wrapperHeight,
      containerStyle,
      simulateWidth,
      pageScale,
      page,
      pageStyle,
      isReady,
      contentHeight,
      exportDialogVisible,
      simulateModel,
      isHomePage,
      isPopup,
      pageUrl,
      copyPage,
      openExportCodeDialog,
      loadCode,
      deletePage,
      currCodeType,
      codeTypes
    }
  }
}
</script>
