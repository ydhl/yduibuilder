<template>
  <div class="d-flex align-items-center justify-content-between mb-2 pt-2 sticky-top bg-white pb-1">
    <div class="fs-6 text-muted user-select-none"><i class="iconfont icon-event"></i>&nbsp;{{t('common.event')}}</div>
    <div class="d-flex align-items-center">
      <button type="button" class="btn btn-primary btn-xs" @click.stop="openEventBindDlg">{{t('event.bind')}}</button>
      <button type="button" class="btn btn-light btn-xs" @click.stop="refresh">{{t('common.refresh')}}</button>
    </div>
  </div>
  <div v-if="loading" class="vh-100 d-flex align-items-center justify-content-center">
    {{t('page.loading')}}
  </div>
  <template v-else>
    <div class="style-panel">
      <template v-for="(events, key) in boundEvents" :key="key">
        <div class="style-header d-flex align-items-center justify-content-between">
          <span class="d-flex align-items-center">
            <i class="iconfont icon-tree-close"></i> {{t('event.'+key)}}&nbsp;
            <span class="badge bg-primary" v-if="events && events.length>0">{{events.length}}</span></span>
          <template v-if="events && events.length>0">
            <div v-if="!openState[key]" @click.stop="openState[key]=true"><i class="iconfont hover-primary icon-expandall"></i></div>
            <div v-if="openState[key]" @click.stop="openState[key]=false"><i class="iconfont hover-primary icon-collapseall"></i></div>
          </template>
        </div>
        <div class="style-body d-none">
          <small v-if="!events || events.length===0" class="text-muted"><i class="iconfont icon-empty"></i> {{t('event.notBindEvent')}}</small>
          <template  v-for="(event, index) in events" :key="index">
            <div class="d-flex align-items-center">
              <i @mousedown="startDrag(true)" v-if="key!='page'" @mouseup="startDrag(false)" @click.stop.prevent="showBoundUI($event, event)"
                 @mousemove="beginDraw($event, event)" @mouseover="highlight(event.uiid)" @mouseleave="offlight()"
                 :class="{'iconfont icon-data-input bind-icon flex-shrink-0': true, 'bound': event.uiid.length>0}"
                 data-bs-toggle="tooltip" :title="t('event.bindTip')"></i>
              <i v-else style="width: 16px;height: 16px"></i>
              <div class="flex-grow-1 fw-bold text-truncate">&nbsp;{{event.event}}
                <span class="text-primary fs-7 fw-light">{{event.modifier.length>0?'.':''}}{{event.modifier?.join('.')}}{{event.customKey?'.'+event.customKey:''}}</span>
                <span class="text-muted fs-7 fw-light">{{getEventArgName(key, event.event)}}</span>
                <span class="fs-7 text-muted ms-1 fw-light" v-if="event.actions && event.actions.length>0 && !openState[key]">{{event.actions.length}} Action</span>
                <span class="fs-7 text-muted ms-1" v-if="event.desc">{{event.desc}}</span>
              </div>
              <div class="flex-shrink-0 d-flex align-items-center">
                <i class="iconfont icon-edit hover-primary" @click.stop="editEvent(event)"></i>
                <i class="iconfont icon-plus hover-primary" @click.stop="addAction(event)"></i>
                <ConfirmRemove @remove="deleteEventBound(event)"></ConfirmRemove>
              </div>
            </div>
            <div class="list-group list-group-flush" v-if="openState[key]">
              <draggable v-model="event.actions" handle=".icon-drag" @change="(n) => sortEventAction(event, n)">
                <transition-group>
                  <div v-for="(action, actIdx) in event.actions" :key="action.uuid"
                       class="list-group-item p-1 pe-0 ps-3 list-group-item-action d-flex align-items-center border-0">
                    <div class="me-1"><i class="iconfont icon-drag text-muted" style="cursor: move"></i><i :class="'iconfont text-danger icon-' + action.type"></i></div>
                    <EventAction :event-name="event.event" :action="action" :variables="eventMap[key]?.[event.event]?.args" bind-type="bind_event" :bind-uuid="event.uuid"></EventAction>
                    <ConfirmRemove icon=" icon-remove" @remove="deleteAction(event, actIdx, action)"></ConfirmRemove>
                  </div>
                </transition-group>
              </draggable>
            </div>
          </template>
        </div>
      </template>
    </div>
    <!--组件自定义事件和弹窗页面的自定义事件-->
    <template v-if="selectedPage?.pageType == 'component' || pageIsPopup">
      <div class="d-flex align-items-center justify-content-between mb-2 pt-2">
        <div class="fs-6 text-muted"><i class="iconfont icon-event"></i>&nbsp;{{t('event.declareEvent')}}</div>
        <button type="button" class="btn btn-primary btn-xs" @click.stop="openDeclareDialog">{{t('event.defineEvent')}}</button>
      </div>
      <div class="style-panel pt-1">
        <template v-for="(event, key) in declaredEvents" :key="key">
          <div class="style-header d-flex align-items-center">
            <div class="flex-grow-1 text-truncate">
              <i class="iconfont icon-tree-close"></i> {{event.name}}
            </div>
            <ConfirmRemove @remove="removeDeclareEvent(event)"></ConfirmRemove>
            <i class="iconfont icon-edit pointer text-muted hover-primary" @click.stop="editDeclareEvent(event)"></i>
          </div>
          <div class="style-body d-none">
            <div class="fs-7 text-muted p-1" v-if="event.desc">{{event.desc}}</div>
            <template v-if="!event.args || event.args.length == 0">{{t('event.noArgs')}}</template>
            <template v-for="(arg, index) in event.args" :key="index">
              <DataComp :model="arg" :index="index" :can-input="false" :can-output="false" :can-mutation="false" :intent="0"></DataComp>
            </template>
          </div>
        </template>
      </div>
    </template>
    <!--注册组件或弹窗的自定义事件-->
    <template v-if="selectedUIItem?.type == 'UIComponent' || selectedPage.includePopup">
    <div class="d-flex align-items-center justify-content-between mb-2 pt-2 mt-3">
      <div class="fs-6 text-muted"><i class="iconfont icon-event"></i>&nbsp;{{t('event.componentEvent', [selectedUIItem?.type == 'UIComponent' ? componentName : t('common.popup')])}}</div>
    </div>
    <template  v-for="(customEvent, index) in customEvents" :key="index">
      <div class="d-flex align-items-center">
        <div class="flex-shrink-0 flex-grow-1 me-1 fw-bold text-truncate hover-text-primary pointer"
             @click="viewCustomEvent(customEvent)">&nbsp;{{customEvent.name}} <span class="text-muted fs-7 fw-normal">{{customEvent.desc}}</span></div>
        <div class="text-end" v-if="!customEvent?.bind">
          <i class="iconfont icon-event pointer text-primary" @click="openCustomBindDlg(customEvent)"></i>
        </div>
        <div class="text-end" v-else>
          <i class="iconfont icon-plus hover-primary" @click.stop="addAction(customEvent?.bind)"></i>
          <ConfirmRemove @remove="deleteEventBound(customEvent?.bind)"></ConfirmRemove>
        </div>
      </div>
      <div class="list-group list-group-flush " v-if="customEvent?.bind">
        <draggable :list="customEvent.bind.actions" handle=".icon-drag"  @change="(n) => sortEventAction(customEvent.bind, n)">
          <transition-group>
            <div v-for="(action, actionIndex) in customEvent.bind.actions" :key="action.uuid"
                 class="list-group-item border-0 list-group-item-action p-1 pe-0 ps-3 d-flex align-items-center">
              <i class="iconfont icon-drag text-muted" style="cursor: move"></i>
              <i :class="'iconfont text-danger me-1 icon-' + action.type"></i>
              <EventAction :action="action" bind-type="bind_event" :bind-uuid="customEvent.bind.uuid"
                          :event-name="customEvent.name" :variables="customEvent.args"></EventAction>
              <ConfirmRemove @remove="deleteAction(customEvent.bind, actionIndex, action)"></ConfirmRemove>
            </div>
          </transition-group>
        </draggable>
      </div>
      <div v-else class="text-muted fs-7">
        {{t('event.declareEventNoListen')}}
      </div>
    </template>
  </template>

  </template>
  <!--绑定的元素菜单-->
  <div class="list-group shadow-sm" ref="boundPop" v-if="boundUIDialogVisible">
    <a href="javascript:void(0)" class="list-group-item list-group-item-action justify-content-between d-flex align-items-center"
       @mouseover="highlight([item.meta.id])" @mouseleave="offlight()"
       v-for="(item,index) in currEventBoundUIs" :key="index">
      <div>
        <i :class="`iconfont text-primary icon-${item.type.toLowerCase()}`"></i>&nbsp;{{ item.meta.title || item.type }}
      </div>
      <i class="iconfont icon-remove text-danger" @click.stop.prevent="removeUIBind(item.meta.id, currBoundEvent.uuid)" ></i>
    </a>
  </div>
  <!-- bind event Dialog -->
  <lay-layer v-model="addEventBindDlgVisible" layer-classes="layui-layer-content-overflow" resize  :title="t('event.bind')" :shade="true" :area="['580px', '600px']" :btn="addEventBindButtons">
    <div class="ps-4 pe-4 pt-2 pb-2">
      <div class="row g-3 mt-1 align-items-center">
        <div class="col-sm-2">
          <label>{{t('common.event')}}</label>
        </div>
        <div class="col-sm-10">
          <select class="form-select form-select-sm" v-model="currBoundEvent.event">
            <template v-for="(subEvents, key) in allEvents" :key="key">
              <optgroup :label="t('event.'+key)" v-if="Object.keys(subEvents).length > 0">
                <option v-for="(index, event) in subEvents" :key="index" :value="event">{{event}}</option>
              </optgroup>
            </template>
          </select>
        </div>
      </div>
      <div class="row g-3 mt-1 align-items-center">
        <div class="col-sm-2">
          <label>{{t('common.desc')}}</label>
        </div>
        <div class="col-sm-10">
          <input type="text" class="form-control form-control-sm" maxlength="45" v-model.trim="currBoundEvent.desc"/>
        </div>
      </div>
      <div class="row g-3 mt-1 align-items-center" v-if="!currBoundEvent.uuid">
        <div class="col-sm-2">
          <label>{{t('common.action')}}</label>
        </div>
        <div class="col-sm-10">
          <AdvanceSelect btn-size="btn-sm btn-light" :options="actionTypes" :default-text="bindAction.name || t('action.add')" @change="(option)=>bindAction = option"></AdvanceSelect>
        </div>
      </div>
      <div class="row g-3 mt-1 align-items-center">
        <div class="col-sm-2 pointer">
          <label @click="openModifierDoc">{{t('event.modifier')}} <i class="iconfont icon-info"></i></label>
        </div>
        <div class="col-sm-10">
          <div class="text-muted fs-7">{{t('event.modifierEvent')}}:</div>
          <div class="d-flex gap-2 flex-wrap">
            <label class=" form-check-label text-truncate d-block" v-for="item in ['stop', 'prevent', 'self', 'capture', 'once', 'passive']" :key="item">
              <input type="checkbox" v-model="currBoundEvent.modifier" :value="item">&nbsp;{{item}}&nbsp;
            </label>
          </div>
          <div class="text-muted mt-2 fs-7">{{t('event.modifierKey')}}:</div>
          <div class="d-flex gap-2 flex-wrap">
            <label class=" form-check-label text-truncate d-block" v-for="item in ['enter', 'tab', 'delete', 'esc', 'space', 'up', 'down', 'left', 'right', 'ctrl', 'alt', 'shift', 'meta']" :key="item">
              <input type="checkbox" v-model="currBoundEvent.modifier" :value="item">&nbsp;{{item}}&nbsp;
            </label>
            <input type="text" class="form-control form-control-sm" v-model="currBoundEvent.customKey" placeholder="custom key code such as A">
          </div>
          <div class="text-muted mt-2 fs-7">{{t('event.modifierMouseButton')}}:</div>
          <div class="d-flex gap-2 flex-wrap">
            <label class=" form-check-label text-truncate d-block" v-for="item in ['left', 'right', 'middle']" :key="item">
              <input type="checkbox" v-model="currBoundEvent.modifier" :value="item">&nbsp;{{item}}&nbsp;
            </label>
          </div>
          <div class="text-muted mt-2 fs-7">{{t('event.modifierOther')}}:</div>
          <div class="d-flex gap-2 flex-wrap">
            <label class=" form-check-label text-truncate d-block" v-for="item in ['debounce', 'throttle']" :key="item">
              <input type="checkbox" v-model="debounceThrottle" :value="item">&nbsp;{{item}}&nbsp;
            </label>
          </div>
          <div class="input-group input-group-sm" v-if="currBoundEvent.modifier.indexOf('debounce')!=-1 || currBoundEvent.modifier.indexOf('throttle')!=-1">
            <div class="input-group-text">{{t('event.timeout')}}</div>
            <input type="number" class="form-control-sm form-control" v-model="currBoundEvent.timeout"/>
            <div class="input-group-text">
              {{t('common.millisecond')}}
              <label class="ms-2" v-if="currBoundEvent.modifier.indexOf('debounce')!=-1"><input type="checkbox" :value="1" v-model="currBoundEvent.immediate">{{t('common.immediate')}}</label>
            </div>
          </div>

          <draggable class="d-flex flex-wrap mb-2 p-2" v-model="currBoundEvent.modifier">
            <transition-group>
              <div v-for="(modifier, idx) in currBoundEvent.modifier" :key="idx" class="text-success" style="cursor: move;">
                .{{modifier}}
              </div>
            </transition-group>
          </draggable>
        </div>
      </div>
    </div>
  </lay-layer>
  <!-- bind action Dialog -->
  <lay-layer v-model="addActionDlgVisible" layer-classes="layui-layer-content-overflow"  :title="t('event.bind')" :shade="true" :area="['420px', '250px']" :btn="addActionButtons">
    <div class="ps-4 pe-4 pt-2 pb-2">
      <div class="row g-3 mt-1 align-items-center">
        <div class="col-sm-2">
          <label>{{t('common.action')}}</label>
        </div>
        <div class="col-auto">
          <AdvanceSelect btn-size="btn-sm btn-light" :options="actionTypes" :default-text="bindAction.name || t('action.add')" @change="(option)=>bindAction = option"></AdvanceSelect>
        </div>
      </div>
    </div>
  </lay-layer>
  <!-- bind custom event Dialog -->
  <lay-layer v-model="addCustomBindDlgVisible" layer-classes="layui-layer-content-overflow" :title="t('event.bind')" :shade="true" :area="['520px', '300px']" :btn="addCustomBindButtons">
    <div class="p-2">
      <label class="fw-bold">{{currCustomEvent.name}}</label>
      <div class="mb-2 text-muted">{{currCustomEvent.desc}}</div>

      <div class="row g-3 mt-1 align-items-center">
        <div class="col-sm-2">{{t('event.args')}}</div>
        <div class="col-auto col-sm-10">
          <template v-if="!currCustomEvent.args || !currCustomEvent.args.length">
            <div class="text-muted">{{t('event.noArgs')}}</div>
          </template>
          <template v-for="(arg, index) in currCustomEvent.args" :key="index">
            <DataComp :model="arg" :index="index" :can-input="false" :can-output="false" :can-mutation="false" :intent="0"></DataComp>
          </template>
        </div>
      </div>
      <div class="row g-3 mt-1 align-items-center">
        <div class="col-sm-2">{{t('common.action')}}</div>
        <div class="col-auto">
          <AdvanceSelect btn-size="btn-sm btn-light" :options="actionTypes" :default-text="bindAction.name || t('action.add')" @change="(option)=>bindAction = option"></AdvanceSelect>
        </div>
      </div>
    </div>
  </lay-layer>
  <!-- view custom event info Dialog -->
  <lay-layer v-model="viewCustomBindDlgVisible" :title="t('event.customEvent')" :shade="true" :area="['520px', '300px']">
    <div class="p-3">
      <label class="fw-bold">{{currCustomEvent.name}}</label>
      <div class="mb-2 text-muted">{{currCustomEvent.desc}}</div>

      <label>{{t('event.args')}}</label>
      <div class="bg-light">
        <template v-if="!currCustomEvent.args || !currCustomEvent.args.length">
          <div class="text-muted">{{t('event.noArgs')}}</div>
        </template>
        <template v-for="(arg, index) in currCustomEvent.args" :key="index">
          <DataComp :model="arg" :index="index" :can-input="false" :can-output="false" :can-mutation="false" :intent="0"></DataComp>
        </template>
      </div>
    </div>
  </lay-layer>
  <!-- 自定义事件 -->
  <lay-layer v-model="declareEventDialogVisible" :title="t('event.declareEvent')" :shade="true" :area="['520px', '500px']" :btn="declareEventButtons">
    <div class="p-2">
      <p class="text-muted">{{t(pageIsPopup ? 'event.declarePopupEventDesc' : 'event.declareEventDesc')}}</p>
      <label>{{t('event.name')}}:</label>
      <input type="text" class="form-control form-control-sm" v-model.trim="declareEvent.name">
      <label class=" mt-2">{{t('common.desc')}}:</label>
      <input type="text" class="form-control form-control-sm" v-model.trim="declareEvent.desc">
      <DataStruct :can-mutation="true" :can-output="false" :can-input="false" :data-title="t('event.args')"
                  :datas="declareEvent.args" @remove="removeArg" @update="updateArg"></DataStruct>
    </div>
  </lay-layer>
  <!-- 修饰符号说明文档 -->
  <lay-layer v-model="openModifierVisible" :title="t('event.modifier')" :shade="true" :area="['500px', '500px']">
    <div class="p-2">
      <ol>
        <li><strong>stop</strong>: {{t('event.modifierDesc.stop')}}</li>
        <li><strong>prevent</strong>: {{t('event.modifierDesc.prevent')}}</li>
        <li><strong>self</strong>: {{t('event.modifierDesc.self')}}</li>
        <li><strong>capture</strong>: {{t('event.modifierDesc.capture')}}</li>
        <li><strong>once</strong>: {{t('event.modifierDesc.once')}}</li>
        <li><strong>passive</strong>: {{t('event.modifierDesc.passive')}}</li>
        <li><strong>meta</strong>: {{t('event.modifierDesc.meta')}}</li>
        <li><strong>exact</strong>: {{t('event.modifierDesc.exact')}}</li>
        <li><strong>debounce</strong>: {{t('event.modifierDesc.debounce')}}</li>
        <li><strong>throttle</strong>: {{t('event.modifierDesc.throttle')}}</li>
      </ol>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import initUI from '@/components/Common'
import eventMap, { hasEvent, getEvents } from '@/store/events'
import baseUIDefines from '@/components/ui/define'
import { computed, onMounted, ref, watch } from 'vue'
import { useStore } from 'vuex'
import ydhl from '@/lib/ydhl'
import canvas from '@/lib/canvas'
import $ from 'jquery'
import ConfirmRemove from '@/components/common/ConfirmRemove.vue'
import DataStruct from '@/components/common/DataStruct.vue'
import DataComp from '@/components/common/Data.vue'
import EventAction from '@/components/common/EventAction.vue'
import AdvanceSelect from '@/components/common/AdvanceSelect.vue'
import { VueDraggableNext } from 'vue-draggable-next'
import Util from '@/components/Util'

/**
 * 行为绑定
 */
export default {
  name: 'BindEvent',
  components: {
    AdvanceSelect,
    EventAction,
    DataComp,
    DataStruct,
    ConfirmRemove,
    draggable: VueDraggableNext
  },
  setup (props: any, context: any) {
    const { t } = useI18n()
    const info = initUI()
    const store = useStore()
    const loading = ref(true)
    const openState = ref({})
    const boundPop = ref()
    const util = Util()
    const selectedPage = computed(() => store.state.design.page)
    const selectedPageId = computed(() => selectedPage.value?.meta?.id)
    const XYInIframe = computed(() => store.state.design.mouseXYInIframe)
    const mouseupInFrame = computed(() => store.state.design.mouseupInFrame)
    const pageScale = computed(() => store.state.design.scale)
    const actionTypes = computed(() => {
      const types: any = ['redirect', 'mutation', 'popup', 'webapi', 'interval', 'validate']
      if (selectedPage.value.pageType === 'component' || pageIsPopup.value) {
        types.push('emit')
      }
      if (selectedPage.value.pageType === 'popup' || pageIsPopup.value) {
        types.push('closepopup')
      }
      return util.getActions(...types)
    })
    const types = {}
    const componentName = computed(() => {
      if (!info.selectedUIItem.value) return ''
      return info.selectedUIItem.value.meta.title
    })

    const allEvents = computed(() => {
      if (!info.selectedUIItem.value) return eventMap
      return getEvents(baseUIDefines[info.selectedUIItem.value?.type], info.selectedUIItem.value)
    })
    const imgSite = ydhl.api
    const boundEvents = ref({})
    // console.log(events)
    const addEventBindDlgVisible = ref(false)
    const addActionDlgVisible = ref(false)
    const declareEventDialogVisible = ref(false)
    const addCustomBindDlgVisible = ref(false)
    const viewCustomBindDlgVisible = ref(false)
    const openModifierVisible = ref(false)
    const boundUIDialogVisible = ref(false)
    const currCustomEvent = ref<any>({})
    const currBoundEvent = ref<any>({})
    const bindAction = ref({ value: 'popup', name: t('action.popup') })
    const declareEvent = ref<Record<string, any>>({ uuid: '', name: '', desc: '', args: [] })
    // 自定义的事件
    const declaredEvents = ref([])
    // 注册的自定义事件
    const customEvents = ref([])
    const currEventBoundUIs = computed<Array<any>>(() => {
      if (!currBoundEvent.value?.uiid) return []
      const rst: any = []
      for (const itemid of currBoundEvent.value.uiid) {
        const { uiConfig } = store.getters.getUIItem(itemid)
        if (uiConfig) rst.push(uiConfig)
      }
      return rst
    })
    const addEventBindButtons = ref([
      {
        text: t('common.ok'),
        callback: () => {
          addEventBind()
        }
      },
      {
        text: t('common.cancel'),
        callback: () => {
          closeDialog()
        }
      }
    ])
    const addActionButtons = ref([
      {
        text: t('common.ok'),
        callback: () => {
          addActionBind()
        }
      },
      {
        text: t('common.cancel'),
        callback: () => {
          closeDialog()
        }
      }
    ])
    const addCustomBindButtons = ref([
      {
        text: t('common.ok'),
        callback: () => {
          addCustomBind()
        }
      },
      {
        text: t('common.cancel'),
        callback: () => {
          closeDialog()
        }
      }
    ])
    const declareEventButtons = ref([
      {
        text: t('common.ok'),
        callback: () => {
          addEventDeclare()
        }
      },
      {
        text: t('common.cancel'),
        callback: () => {
          closeDialog()
        }
      }
    ])

    const loadDeclaredEvent = () => {
      declaredEvents.value = []
      if (selectedPage.value.pageType !== 'component' && !pageIsPopup.value) return
      ydhl.get('api/event/declare.json?page_uuid=' + selectedPageId.value, [], (rst) => {
        if (!rst.success) return
        declaredEvents.value = rst.data
        store.commit('updateState', { declaredEvents: rst.data })
      }, 'json')
    }
    const loadListenEvent = () => {
      customEvents.value = []
      if (info.selectedUIItem?.value?.type !== 'UIComponent' && !selectedPage.value.includePopup) return
      const subPageUuid = info.selectedUIItem.value?.items?.[0]?.subPageId || ''
      ydhl.get('api/event/listen.json?page_uuid=' + selectedPageId.value + '&sub_page_uuid=' + subPageUuid, [], (rst) => {
        if (!rst.success) return
        customEvents.value = rst.data
      }, 'json')
    }
    const loadEventData = (showLoading = false) => {
      if (showLoading) loading.value = true
      for (const key in boundEvents.value) {
        boundEvents.value[key] = []
        openState.value[key] = true
      }
      ydhl.get('api/event.json?page_uuid=' + selectedPageId.value, [], (rst) => {
        if (showLoading) loading.value = false
        if (!rst.success) return
        for (const item of rst.data) {
          const type = types[item.event]
          if (!boundEvents.value[type]) boundEvents.value[type] = []
          boundEvents.value[type].push(item)
        }
      }, 'json')
    }
    watch(selectedPageId, (v) => {
      if (!v) return
      refresh()
    })
    watch(info.selectedUIItemId, (v) => {
      loadListenEvent()
    })
    const refresh = () => {
      loadEventData(true)
      loadDeclaredEvent()
      loadListenEvent()
    }
    onMounted(() => {
      boundEvents.value = {}
      for (const key in eventMap) {
        boundEvents.value[key] = []
        openState.value[key] = true
        for (const type in eventMap[key]) {
          types[type] = key
        }
      }
      refresh()
      $('body').on('click', (event: any) => {
        boundUIDialogVisible.value = false
      })
    })
    watch(XYInIframe, (v) => {
      if (canvas.getDrawFromId() !== currBoundEvent.value?.uuid) return
      const ifrmae = document.getElementById('wrapper' + selectedPageId.value)
      if (ifrmae) {
        const { x, y } = ifrmae.getBoundingClientRect()
        canvas.mouseoverInIframe(v.x * pageScale.value + x, v.y * pageScale.value + y)
      }
    })
    watch(mouseupInFrame, (v) => {
      if (!info.hoverUIItem.value || !canvas.isDrawline() || canvas.getDrawFromId() !== currBoundEvent.value?.uuid) return
      if (!hasEvent(baseUIDefines[info.hoverUIItem.value.type], currBoundEvent.value?.event, info.hoverUIItem.value)) {
        ydhl.alert(t('event.uiNotSupportEvent', [info.hoverUIItem.value.type, currBoundEvent.value?.event]))
        return
      }
      // 点击某个ui后结束画线,并设置绑定关系
      canvas.stopDrawline()
      ydhl.post('api/event/bindui.json', {
        event_id: currBoundEvent.value.uuid,
        page_uuid: selectedPageId.value,
        ui_id: info.hoverUIItemId.value
      }, [], (rst) => {
        loadEventData()
        if (!rst.success) {
          ydhl.alert(rst.msg || 'Oops, try again')
          return
        }
        store.commit('addUIEventBind', {
          itemid: info.hoverUIItemId.value,
          pageId: selectedPageId.value,
          eventId: rst.data.uuid
        })
      }, 'json')
    })
    const startDrag = (bool: boolean) => {
      canvas.setStartDrag(bool)
    }
    const beginDraw = (event, item) => {
      if (!canvas.isDrag() || canvas.isDrawline()) return
      currBoundEvent.value = item
      canvas.startDrawline(event.clientX, event.clientY, item.uuid)
    }

    // 高亮显示绑定的元素
    const highlight = (uuids = []) => {
      // console.log(uuids)
      store.commit('updatePageState', { highlightUIItemIds: uuids })
    }
    const offlight = () => {
      store.commit('updatePageState', { highlightUIItemIds: [] })
    }
    const showBoundUI = (event: any, item) => {
      currBoundEvent.value = item
      ydhl.togglePopper(boundUIDialogVisible, event.target, boundPop, 'bottom')
    }
    const openEventBindDlg = () => {
      currBoundEvent.value = { event: 'onClick', modifier: [] }
      addEventBindDlgVisible.value = true
    }
    const editEvent = (event) => {
      currBoundEvent.value = JSON.parse(JSON.stringify(event))
      addEventBindDlgVisible.value = true
    }
    const addAction = (event) => {
      currBoundEvent.value = event
      addActionDlgVisible.value = true
    }
    const openCustomBindDlg = (event) => {
      currCustomEvent.value = event
      addCustomBindDlgVisible.value = true
    }
    const viewCustomEvent = (event) => {
      currCustomEvent.value = event
      viewCustomBindDlgVisible.value = true
    }
    const closeDialog = () => {
      addEventBindDlgVisible.value = false
      declareEventDialogVisible.value = false
      addCustomBindDlgVisible.value = false
      viewCustomBindDlgVisible.value = false
      addActionDlgVisible.value = false
    }
    const removeUIEventBind = (uiid: string, eventId: string) => {
      store.commit('removeUIEventBind', {
        itemid: uiid,
        pageId: selectedPageId.value,
        bindId: eventId
      })
    }
    const removeUIBind = (uiid, bindId) => {
      ydhl.post('api/event/removebindui.json', {
        event_id: bindId,
        page_uuid: selectedPageId.value,
        ui_id: uiid
      }, [], (rst) => {
        currBoundEvent.value = rst.data
        loadEventData()
        // 更新ui中的events相关数据
        removeUIEventBind(uiid, bindId)
      }, 'json')
    }

    // 添加自定义事件绑定
    const addCustomBind = () => {
      ydhl.post('api/event/add.json', { page_uuid: selectedPageId.value, custom_event_uuid: currCustomEvent.value.uuid, type: bindAction.value.value, uiid: info.selectedUIItemId.value || '' }, [], (rst) => {
        if (!rst.success) {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
          return
        }
        closeDialog()
        loadListenEvent()
      })
    }
    // 添加事件绑定
    const addEventBind = () => {
      if (!currBoundEvent.value?.event) {
        ydhl.alert(t('event.error.bindEventEmpty'), t('common.ok'))
        return
      }
      if (!bindAction.value?.value) {
        ydhl.alert(t('event.error.bindActionEmpty'), t('common.ok'))
        return
      }
      ydhl.loading(t('common.pleaseWait')).then((dialodId) => {
        ydhl.post('api/event/add.json', {
          page_uuid: selectedPageId.value,
          uuid: currBoundEvent.value.uuid || '',
          uiid: info.selectedUIItemId.value || '',
          event: currBoundEvent.value.event,
          type: bindAction.value.value,
          desc: currBoundEvent.value.desc || '',
          modifier: currBoundEvent.value.modifier?.join(',') || '',
          immediate: currBoundEvent.value.immediate || 0,
          customKey: currBoundEvent.value.customKey || 0,
          timeout: currBoundEvent.value.timeout || 0
        }, [], (rst) => {
          ydhl.closeLoading(dialodId)
          if (!rst.success) {
            ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
            return
          }

          store.commit('addUIEventBind', {
            itemid: info.selectedUIItemId.value,
            pageId: selectedPageId.value,
            eventId: rst.data.uuid
          })
          loadEventData()
          closeDialog()
        })
      })
    }
    const addActionBind = () => {
      if (!bindAction.value?.value) {
        ydhl.alert(t('event.error.bindActionEmpty'), t('common.ok'))
        return
      }
      ydhl.loading(t('common.pleaseWait')).then((dialodId) => {
        ydhl.post('api/event/addaction.json', { page_uuid: selectedPageId.value, event_uuid: currBoundEvent.value.uuid, type: bindAction.value.value }, [], (rst) => {
          ydhl.closeLoading(dialodId)
          if (!rst.success) {
            ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
            return
          }
          loadEventData()
          loadListenEvent()
          closeDialog()
        })
      })
    }
    const addEventDeclare = () => {
      if (!declareEvent.value.name) {
        ydhl.alert(t('event.error.eventNameEmpty'), t('common.ok'))
        return
      }
      ydhl.loading(t('common.pleaseWait')).then((dialodId) => {
        ydhl.postJson('api/event/declare.json', { page_uuid: selectedPageId.value, event: declareEvent.value }).then((rst: any) => {
          declareEventDialogVisible.value = false
          ydhl.closeLoading(dialodId)
          if (!rst.success) {
            ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
            return
          }
          declareEvent.value = { uuid: '', name: '', desc: '', args: [] }
          loadDeclaredEvent()
        })
      })
    }
    const removeDeclareEvent = (ent) => {
      ydhl.loading(t('common.pleaseWait')).then((dialodId) => {
        ydhl.postJson('api/event/deletedeclare.json', { event_uuid: ent.uuid, page_uuid: selectedPageId.value }).then((rst: any) => {
          ydhl.closeLoading(dialodId)
          if (!rst.success) {
            ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
            return
          }
          loadDeclaredEvent()
        })
      })
    }
    const editDeclareEvent = (ent) => {
      declareEventDialogVisible.value = true
      declareEvent.value = JSON.parse(JSON.stringify(ent))
    }
    const openDeclareDialog = () => {
      declareEventDialogVisible.value = true
    }
    const updateArg = (data: any, index: number) => {
      if (!declareEvent.value.args) declareEvent.value.args = []
      if (index < 0) {
        declareEvent.value.args.push(data)
      } else {
        declareEvent.value.args[index] = data
      }
    }
    const removeArg = (data, index: number) => {
      declareEvent.value.args.splice(index, 1)
    }

    const deleteEventBound = (event) => {
      currBoundEvent.value = event
      ydhl.post('api/event/delete.json', { page_uuid: selectedPageId.value, uuid: event.uuid }, [], (rst) => {
        if (!rst.success) {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
          return
        }
        // 更新ui中的events相关数据
        for (const boundUI of currEventBoundUIs.value) {
          removeUIEventBind(boundUI.meta.id, event.uuid)
        }
        loadEventData()
        loadListenEvent()
      })
    }
    const deleteAction = (event, index, action) => {
      ydhl.post('api/action/deleteaction.json', { page_uuid: selectedPageId.value, action_uuid: action.uuid }, [], (rst) => {
        if (!rst.success) {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
          return
        }
        event.actions.splice(index, 1)
      })
    }

    const sortEventAction = (bindEvent, { moved }) => {
      const index = {}
      for (const idx in bindEvent.actions) {
        index[bindEvent.actions[idx].uuid] = idx
      }
      ydhl.postJson('api/action/sort.json', {
        page_uuid: selectedPageId.value, index
      })
    }
    const getEventArgName = (type, event) => {
      const names: any = []

      if (!eventMap[type]?.[event]?.args) return ''
      for (const arg of eventMap[type]?.[event]?.args) {
        names.push(arg.name)
      }
      return names.length > 0 ? '(' + names.join(', ') + ')' : ''
    }
    const pageIsPopup = computed(() => {
      return selectedPage.value.bePopup || selectedPage.value.pageType === 'popup'
    })
    const debounceThrottle = computed<Array<string>>({
      get () {
        let index = currBoundEvent.value.modifier.findIndex((item) => item === 'throttle')
        if (index !== -1) {
          return ['throttle']
        }

        index = currBoundEvent.value.modifier.findIndex((item) => item === 'debounce')
        if (index !== -1) {
          return ['debounce']
        }
        return []
      },
      set (v) {
        const last = v.pop()
        if (last === 'debounce') {
          const index = currBoundEvent.value.modifier.findIndex((item) => item === 'throttle')
          if (index !== -1) currBoundEvent.value.modifier.splice(index, 1)
          currBoundEvent.value.modifier.push('debounce')
        } else if (last === 'throttle') {
          const index = currBoundEvent.value.modifier.findIndex((item) => item === 'debounce')
          if (index !== -1) currBoundEvent.value.modifier.splice(index, 1)
          currBoundEvent.value.modifier.push('throttle')
        } else {
          let index = currBoundEvent.value.modifier.findIndex((item) => item === 'debounce')
          if (index !== -1) currBoundEvent.value.modifier.splice(index, 1)
          index = currBoundEvent.value.modifier.findIndex((item) => item === 'throttle')
          if (index !== -1) currBoundEvent.value.modifier.splice(index, 1)
        }
      }
    })
    const openModifierDoc = () => {
      openModifierVisible.value = true
    }
    return {
      t,
      ...info,
      loading,
      editEvent,
      addEventBindDlgVisible,
      openModifierVisible,
      addActionDlgVisible,
      addEventBindButtons,
      addActionButtons,
      addCustomBindButtons,
      declareEventDialogVisible,
      addCustomBindDlgVisible,
      viewCustomBindDlgVisible,
      declareEventButtons,
      boundUIDialogVisible,
      allEvents,
      actionTypes,
      bindAction,
      boundEvents,
      selectedPage,
      imgSite,
      boundPop,
      currEventBoundUIs,
      currBoundEvent,
      componentName,
      declareEvent,
      currCustomEvent,
      customEvents,
      declaredEvents,
      openState,
      pageIsPopup,
      eventMap,
      refresh,
      loadDeclaredEvent,
      addEventBind,
      loadEventData,
      loadListenEvent,
      addCustomBind,
      openEventBindDlg,
      deleteAction,
      addAction,
      openDeclareDialog,
      closeDialog,
      removeUIBind,
      startDrag,
      beginDraw,
      openCustomBindDlg,
      viewCustomEvent,
      highlight,
      offlight,
      showBoundUI,
      updateArg,
      removeArg,
      openModifierDoc,
      deleteEventBound,
      editDeclareEvent,
      removeDeclareEvent,
      sortEventAction,
      getEventArgName,
      debounceThrottle
    }
  }
}
</script>
