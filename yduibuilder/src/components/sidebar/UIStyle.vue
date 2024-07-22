<template>
  <div v-if="!selectedUIItemId" class="d-flex flex-column h-100 text-muted justify-content-center align-content-center align-items-center">
    <i class="iconfont icon-click" style="font-size: 6rem"></i>
    <div class="pe-2">{{t('common.pleaseSelectUIItem')}}</div>
  </div>
  <div v-if="selectedUIItemId" class="d-flex flex-column h-100">
    <div class="pt-2 d-flex align-items-center user-select-none"><i :class="`iconfont text-primary icon-${selectedUIItem.type.toLowerCase()}`"></i>&nbsp;{{selectedUIItem.meta.title || selectedUIItem.type}}&nbsp;<small class="text-muted">#{{uiID}}</small></div>

    <div class="d-flex  flex-grow-1 border-top border-light border-start">
      <div class="vertical-tab user-select-none">
        <a :class="{'vertical-tab-item': true, 'active': activeStyleState.type=='normal'}" @click="switchStyleState('normal', 'normal')" href="javascript:void(0)">&nbsp;Normal
          <i v-if="styleStates?.normal?.style" class="iconfont icon-point text-danger fs-7"></i>
        </a>
        <a :class="{'vertical-tab-item': true, 'active': activeStyleState.type=='pseudo'}" @click="switchStyleState('pseudo', ':active')" href="javascript:void(0)">&nbsp;Pseudo
          <i v-if="hasPseudoStyle" class="iconfont icon-point text-danger fs-7"></i>
        </a>
        <a :class="{'vertical-tab-item': true, 'active': activeStyleState.type=='activated'}" @click="switchStyleState('activated', 'activated')" href="javascript:void(0)">&nbsp;Activated
          <i v-if="styleStates?.activated?.style" class="iconfont icon-point text-danger fs-7"></i>
        </a>
        <a :class="{'vertical-tab-item': true, 'active': activeStyleState.type=='hidden'}" @click="switchStyleState('hidden', 'hidden')" href="javascript:void(0)">&nbsp;Hidden
          <i v-if="styleStates?.hidden?.style" class="iconfont icon-point text-danger fs-7"></i>
        </a>
        <template v-for="(styleState, id, index) in styleStates" :key="index">
          <a v-if="styleState.state.type=='custom'" :class="{'vertical-tab-item': true, 'active': activeStyleState.state==id}" @click="switchStyleState('custom', id)" href="javascript:void(0)">&nbsp;{{ styleState.state.name || "Untitled" }}
            <i v-if="styleState.style" class="iconfont icon-point text-danger fs-7"></i>
          </a>
        </template>
        <a class="vertical-tab-item" @click="addStyleState('custom')" href="javascript:void(0)">&nbsp;+{{t('style.state.addState')}}</a>
      </div>
      <div class="flex-grow-1 style-panel" style="width: 90%">
        <template v-if="activeStyleState.type=='normal'">
          <div class="text-muted fs-7 p-2">
            {{t('style.state.normalStateTip')}}
            <div class="d-flex align-items-center">
              <i class="iconfont icon-point text-danger"></i>{{t('style.valueCustom')}}&nbsp;&nbsp;
              <i class="iconfont icon-point text-success"></i>{{t('style.valueInherit')}}
            </div>
          </div>
        </template>
        <template v-else-if="activeStyleState.type=='pseudo'">
          <div class="text-muted fs-7 p-2">
            {{t('style.state.pseudoDesc')}}
          </div>
        </template>
        <template v-else>
          <div class="fs-7 d-flex p-2 justify-content-between align-items-center">
            <div>
              <div class="fs-7 text-muted" v-if="activeStyleState.type=='hidden'">{{t('style.state.hiddenDesc')}}</div>
              <div class="fs-7 text-muted" v-else>{{t('style.state.stateConditionTip')}}</div>
              <div v-if="styleStates[activeStyleState.state]?.state?.expression_code" class="p-1">
                <span class="text-success">{{styleStates[activeStyleState.state].state.expression_code}}</span>
              </div>
              <div v-else class="text-danger p-1">
                {{ t('action.notSet') }}
              </div>
            </div>
            <div class="flex-shrink-0">
              <button class="btn btn-primary btn-xs" @click="editStyleState(activeStyleState.type, styleStates[activeStyleState.state]?.state)">
                {{t('common.edit')}}
              </button>
              <button @click="removeStyleState(styleStates[activeStyleState.state]?.state)" v-if="styleStates[activeStyleState.state]?.state" class="btn btn-danger btn-xs ms-1">
                {{t('common.remove')}}
              </button>
            </div>
          </div>
        </template>
        <template v-if="activeStyleState.type!='hidden'">
          <template v-if="activeStyleState.type=='pseudo'">
            <div class="nav mb-1 justify-content-center">
              <li class="nav-item position-relative">
                <a :class="{'nav-link p-1 fs-7':true,'border-bottom border-primary border-2':activeStyleState.state==':active'}" @click="switchStyleState('pseudo', ':active')" href="javascript:void(0)">:active</a>
                <i v-if="pseudoStates?.[':active']?.style" class="iconfont icon-point text-danger position-absolute" style="right: -6px;top: -7px"></i>
              </li>
              <template v-for="(pseudoState, id, index) in pseudoStates" :key="index">
                <li class="nav-item d-flex align-items-center" v-if="pseudoState.state.name != ':active'">
                  <a :class="{'nav-link p-1 fs-7 position-relative':true,'border-bottom border-primary border-2':activeStyleState.state==id}" @click="switchStyleState('pseudo', id)" href="javascript:void(0)">
                    {{ pseudoState.state.name }}
                    <i v-if="pseudoState.style" class="iconfont fs-7 icon-point text-danger position-absolute" style="right: 0px;top: -5px"></i>
                  </a>
                  <i class="iconfont icon-delete hover-danger" @click="removeStyleState(pseudoState.state)"></i>
                </li>
              </template>
              <li class="nav-item dropdown">
                <a class="nav-link fs-7 p-1 dropdown-toggle" data-bs-toggle="dropdown" @click="initEditState('pseudo')"
                   href="javascript:void(0)" role="button" aria-expanded="false">+{{t('style.state.addPseudo')}}</a>
                <ul class="dropdown-menu">
                  <li v-for="(pseudo, index) in pseudos" :key="index"><a class="dropdown-item" href="javascript:void(0)"
                                                                         @click="addPseudo(pseudo)">{{pseudo}}</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><h6 class="dropdown-header pt-0 pb-0">{{ t('style.state.customPseudo') }}:</h6></li>
                  <li class="p-2">
                    <input class="form-control form-control-sm" v-model="editState.name" @keyup.enter="addPseudo(editState.name)"/>
                    <button type="button" class="btn btn-xs mt-1 btn-primary" @click="addPseudo(editState.name)">{{t('style.state.addPseudo')}}</button>
                  </li>
                </ul>
              </li>
            </div>
          </template>
          <StyleSelector :state-uuid="styleStates[activeStyleState.state]?.state?.uuid || ''" :preview-mode="previewMode"></StyleSelector>
          <Typography :preview-mode="previewMode"></Typography>
          <StyleBackground :preview-mode="previewMode"></StyleBackground>
          <StyleLyout :preview-mode="previewMode"></StyleLyout>
          <StyleSize :preview-mode="previewMode" v-if="hasSize"></StyleSize>
          <MarginPadding :preview-mode="previewMode"></MarginPadding>
          <StyleBorder :preview-mode="previewMode" v-if="hasBorder"></StyleBorder>
          <StyleUtilities :preview-mode="previewMode"></StyleUtilities>
        </template>
      </div>
    </div>
  </div>

  <lay-layer v-model="addStyleStateVisible" skin="layui-layer-content-overflow" :title="t('style.state.addState')" :shade="true" :area="['520px', '500px']" :btn="buttons">
    <div class="p-2" style="width: 500px">
      <div class="row">
        <label class="col-sm-3 col-form-label text-truncate">{{t("style.state.stateName")}}</label>
        <div class="col-sm-9 d-flex align-items-center">
          <template v-if="editState.type=='custom'">
            <input class="form-control form-control-sm" v-model="editState.name">
          </template>
          <div v-else class="form-control-plaintext">{{editState.type}}</div>
        </div>
      </div>
      <div class="row mb-3 p-2 text-muted">{{t('style.state.variableTip')}}</div>
      <div class="p-3 d-flex align-items-start justify-content-start">
        <Expression :expression="editState.expression"></Expression>
      </div>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import Typography from '@/components/sidebar/style/Typography.vue'
import initUI from '@/components/Common'
import StyleLyout from '@/components/sidebar/style/Layout.vue'
import StyleBackground from '@/components/sidebar/style/Background.vue'
import StyleSelector from '@/components/sidebar/style/StyleSelector.vue'
import { ref, computed, onMounted, watch, onDeactivated } from 'vue'
import MarginPadding from '@/components/sidebar/style/MarginPadding.vue'
import StyleBorder from '@/components/sidebar/style/Border.vue'
import { useI18n } from 'vue-i18n'
import StyleUtilities from '@/components/sidebar/style/Utilities.vue'
import StyleSize from '@/components/sidebar/style/Size.vue'
import { layer } from '@layui/layer-vue'
import { useStore } from 'vuex'
import ydhl from '@/lib/ydhl'
import Expression from '@/components/common/Expression.vue'
export default {
  name: 'UIStyle',
  components: { Expression, StyleSize, StyleLyout, StyleSelector, StyleUtilities, MarginPadding, StyleBorder, Typography, StyleBackground },
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const store = useStore()
    const addStyleStateVisible = ref<any>(false)
    const variableDialogVisible = ref<any>(false)
    const previewMode = ref<any>(false)
    const activeStyleState = computed<{ type: string, state: string }>({
      get () {
        return store.state.design.selectedUIItemActiveState || { type: 'normal', state: 'normal' }
      },
      set (v) {
        store.commit('updatePageState', { selectedUIItemActiveState: v })
      }
    })
    const styleStates = ref<any>({})
    const pseudoStates = computed(() => {
      const _ = {}
      for (const valueKey in styleStates.value) {
        if (styleStates.value?.[valueKey].state.type === 'pseudo') {
          _[valueKey] = styleStates.value?.[valueKey]
        }
      }
      return _
    })
    const setDataType = ref<any>('left')
    const variableId = ref<any>('')
    // 该变量记录当前加载的style state是从那个页面加载下来的，主要解决页面改变后导致当前选中的ui被清空时，保存该ui的style state用，因为这是store中的page已经改变了
    const loadStyleFromPageUiid = ref<any>('')
    const editState = ref<any>({})
    const previewStyleItem = computed<Record<any, any>>({
      get () {
        return store.state.design.previewStyleItem
      },
      set (v) {
        store.commit('updatePageState', { previewStyleItem: v || {} })
      }
    })
    const hasPseudoStyle = computed(() => {
      for (const valueKey in pseudoStates.value) {
        const state = pseudoStates.value[valueKey]
        if (state.style) return true
      }
      return false
    })
    const uiID = computed(() => {
      if (!info.selectedUIItem.value) return ''
      return info.selectedUIItem.value.meta.id
    })
    const currPage = computed(() => {
      return store.state.design.page
    })
    const hasSize = computed(() => {
      return isNotPage.value
    })
    const isMobile = computed(() => {
      return store.state.design.endKind === 'mobile'
    })
    const isPC = computed(() => {
      return store.state.design.endKind === 'pc'
    })
    const isNotPage = computed(() => {
      return info.selectedUIItem?.value?.type !== 'Page'
    })
    const hasBorder = computed(() => {
      return isNotPage.value && info.selectedUIItem?.value?.type !== 'Hr'
    })
    onMounted(() => {
      loadStateStyle()
      initEditState()
    })
    // 面板关闭时，保存当前选择ui的样式
    onDeactivated(() => {
      saveStateStyle(uiID.value, loadStyleFromPageUiid.value, () => {
        styleStates.value = {}
        previewStyleItem.value = {}
      })
    })
    // ui改变后（同一个页面上的ui改变，或者切换了page，或者取消了选择），保存上一个ui的样式
    watch(uiID, (n, old) => {
      if (n === old) return
      if (old) saveStateStyle(old, loadStyleFromPageUiid.value)
      if (n) {
        // 切换新的ui的
        styleStates.value = {}
        previewStyleItem.value = {}
        loadStateStyle()
      } else {
        // 当前没有选择ui了
        activeStyleState.value = { type: 'normal', state: 'normal' }
        styleStates.value = {}
        previewStyleItem.value = {}
      }
    })

    // 保存样式
    const saveStateStyle = (uiid: any, pageUuid: string, cb: any = null) => {
      if (!uiid || !pageUuid || !previewStyleItem.value?.meta || activeStyleState.value.type === 'normal') return
      const stateType = activeStyleState.value.type
      const stateName = activeStyleState.value.state
      const editState = styleStates.value[stateName]?.state || null
      const style = JSON.parse(JSON.stringify(previewStyleItem.value.meta))
      ydhl.postJson('api/state/style.json', {
        state_uuid: editState?.uuid || '',
        page_uuid: pageUuid,
        state_type: stateType,
        state_name: stateName,
        uiid: uiid,
        style
      }).then((rst: any) => {
        if (!rst.success) {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
        } else {
          if (!rst.data?.uuid) { // 保存的数据为空，则删除
            delete styleStates.value[stateName]
            previewStyleItem.value = {}
          }
          if (cb) cb()
        }
      })
    }
    const initEditState = (type = 'custom') => {
      editState.value = {
        name: '', // 状态名
        type, // pseudo 伪代码 custom 自定义状态
        expression: {}
      }
    }
    const saveState = (editState) => {
      if (!editState.name) {
        layer.msg(t('api.pleaseInputName'))
        return
      }
      ydhl.loading(t('common.pleaseWait')).then((dialogid) => {
        ydhl.postJson('api/state/add.json', { page_uuid: currPage.value?.meta.id, uiid: uiID.value, state: editState }).then((rst: any) => {
          ydhl.closeLoading(dialogid)
          if (!rst.success) {
            ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
            return
          }
          styleStates.value[rst.data.key] = rst.data.state // state 里面包含了state和style
          switchStyleState(editState.type, editState.name)
          initEditState()
          addStyleStateVisible.value = false
        })
      })
    }
    const buttons = ref([
      {
        text: t('common.ok'),
        callback: () => {
          if (editState.value.type !== 'custom') {
            editState.value.name = editState.value.type
          }
          saveState(editState.value)
        }
      },
      {
        text: t('common.cancel'),
        callback: () => {
          initEditState()
          addStyleStateVisible.value = false
        }
      }
    ])

    const addStyleState = (type) => {
      addStyleStateVisible.value = true
      initEditState(type)
    }
    const editStyleState = (stateType, styleState) => {
      addStyleStateVisible.value = true
      if (styleState) {
        editState.value = JSON.parse(JSON.stringify(styleState))
      } else {
        editState.value.type = stateType
      }
    }
    const loadStateStyle = () => {
      loadStyleFromPageUiid.value = currPage.value?.meta.id
      ydhl.get('api/state.json', { page_uuid: currPage.value?.meta.id, uiid: uiID.value }, (rst) => {
        if (!rst.success) return
        styleStates.value = rst.data || {}
        switchStyleState(activeStyleState.value.type, activeStyleState.value.state)
      })
    }
    const removeStyleState = (styleState) => {
      ydhl.confirm(t('event.deleteConfirm'), t('common.delete'), t('common.cancel')).then((dialogid) => {
        ydhl.postJson('api/state/remove.json', { page_uuid: currPage.value?.meta.id, state_uuid: styleState.uuid }).then(() => {
          ydhl.closeLoading(dialogid)
          if (activeStyleState.value.type === 'custom') {
            activeStyleState.value = { type: 'normal', state: 'normal' }
          } else if (activeStyleState.value.type === 'pseudo') {
            activeStyleState.value.state = ':active'
          }

          for (const valueKey in styleStates.value) {
            if (styleStates.value?.[valueKey].state.uuid === styleState.uuid) {
              delete styleStates.value?.[valueKey]
              break
            }
          }
          previewStyleItem.value = {}
        }).catch((msg) => {
          ydhl.closeLoading(dialogid)
          ydhl.alert(msg)
        })
      })
    }

    const switchStyleState = (stateType, stateName) => {
      if (stateName !== activeStyleState.value.state) {
        saveStateStyle(uiID.value, loadStyleFromPageUiid.value)
      }
      activeStyleState.value = { type: stateType, state: stateName }
      previewMode.value = stateType !== 'normal'
      previewStyleItem.value = styleStates.value[stateName]?.style
    }

    const addPseudo = (pseudo) => {
      for (const ps in pseudoStates.value) {
        if (pseudoStates.value[ps].state.name === pseudo) {
          ydhl.alert(t('style.state.pseudoExist'))
          return
        }
      }
      initEditState('pseudo')
      editState.value.name = pseudo
      saveState(editState.value)
    }
    return {
      ...info,
      currPage,
      setDataType,
      uiID,
      hasPseudoStyle,
      previewMode,
      variableDialogVisible,
      addStyleState,
      addPseudo,
      editStyleState,
      removeStyleState,
      switchStyleState,
      initEditState,
      buttons,
      editState,
      styleStates,
      pseudoStates,
      activeStyleState,
      variableId,
      addStyleStateVisible,
      t,
      hasSize,
      pseudos: [':disabled', ':empty', ':enabled', ':first-child', ':first-of-type', ':focus', ':hover', ':last-child', ':last-of-type'],
      isMobile,
      isPC,
      isNotPage,
      hasBorder
    }
  }
}
</script>
