<template>
  <div class="model-item">
    <!--绑定按钮区域-->
    <div class="d-flex align-items-center" v-if="canOutput || canInput">
      <i class="iconfont icon-data-input bind-icon invisible" v-if="!myCanInput"></i>
      <i v-else @mousedown.stop="startDrag(true)" @mouseup.stop="startDrag(false)"
         @click.stop.prevent="showBound($event,'in')" @mousemove="beginDraw($event,'in')"
         @mouseover="highlight('in')" @mouseleave="offlight()"
         :class="{'iconfont icon-data-input bind-icon': true, 'bound': myModel.in}"></i>

      <i class="iconfont icon-data-output bind-icon invisible" v-if="!myCanOutput"></i>
      <i v-else @mousedown.stop="startDrag(true)" @mouseup.stop="startDrag(false)"
         @click.stop.prevent="showBound($event,'out')" @mousemove="beginDraw($event,'out')"
         @mouseover="highlight('out')" @mouseleave="offlight()"
         :class="{'iconfont icon-data-output bind-icon': true, 'bound-out': myModel.out}"></i>
    </div>
    <div class="model-field text-truncate" @click="isOpen = !isOpen" :style="'width:0px;padding-left: ' + (intent * 16) + 'px'">
      <i v-if="myModel.type=='array' || myModel.type=='object'" :class="{'iconfont':true, 'icon-tree-close': !isOpen, 'icon-tree-open': isOpen}"></i>
      <i v-if="myModel.type!='array' && myModel.type!='object'" class="flex-shrink-0" style="width: 16px;height: 24px;">&nbsp;</i>
      <div :class="{'pointer hover-text-primary': true, 'fw-bolder':intent==0}" @click.stop="viewDetail">
        <template v-if="isArrayItem">
          <span class="text-success">ITEM</span>
        </template>
        <template v-else>
          {{myModel.name}}
        </template>
      </div>
      <span :class="'ps-1 param-' + myModel.type">
        {{myModel.type}}
        <span class="text-muted fs-7" :title="myModel.mock=='1' ? 'Has Mock' : ('Mock: '+ myModel.mock)" v-if="myModel.mock">M</span>
        <template v-if="myModel.defaultValue">
          <span v-if="['object','array','map','any'].indexOf(myModel.type)==-1" class="text-info ps-1 fs-7 text-truncate">{{myModel.defaultValue}}</span>
          <span v-else @click.stop="viewDefaultValue(myModel.defaultValue)" class="text-info ps-1 fs-7 text-truncate">{{t('common.view')}}</span>
        </template>
      </span>
      <span class="text-truncate ps-2 fs-7 pointer text-muted" @click="showComment()">
        {{enumValues}}
        {{myModel.comment}}
      </span>
    </div>
    <!--title-->
    <div class="model-title fs-7">
      {{myModel.title}}
    </div>
    <div class="model-action" v-if="canMutation">
      <i class="iconfont icon-plus pointer text-muted hover-primary" @click.stop="add" v-if="myModel.type=='object'"></i>
      <i v-else style="width: 16px;height: 24px;">&nbsp;</i>
      <ConfirmRemove @remove="remove" v-if="!isArrayItem"></ConfirmRemove>
      <i v-else style="width: 16px;height: 24px;">&nbsp;</i>
      <i class="iconfont icon-edit pointer text-muted hover-primary" @click.stop="edit"></i>
    </div>
  </div>
  <!--弹窗选择绑定类型菜单-->
  <teleport to="#app">
    <div>
      <div class="shadow-sm" ref="outputTypeMenu" v-if="showOutputTypeMenu" :style="outputTypeStyle" @click.stop.prevent>
        <AdvanceSelect :options="outputAndBoundItems(currBindOutUI)" :hide-toggle="true" @click="(option) => changeOutputOrBound(currBindOutUI, option)"></AdvanceSelect>
      </div>
    </div>
  </teleport>
  <!--绑定的元素菜单-->
  <div class="list-group shadow-sm ms-2" ref="boundPop" v-if="showBoundPop"  @click.stop.prevent>
    <div class="list-group-item justify-content-between d-flex align-items-center" @click="closeDropmenu"
       @mouseover="highlight('', item.meta.id)" @mouseleave="offlight()"
       v-for="(item,index) in boundUIItems" :key="index">
      <div class="text-truncate flex-shrink-0">
        <i :class="`iconfont text-primary icon-${item.type.toLowerCase()}`"></i>&nbsp;{{ item.meta.title || item.type }}
      </div>
      <div class="text-muted" v-if="!hasOutputAs(item.type) && showBoundType==='in'"> as data input</div>
      <div class="text-muted" v-if="!hasOutputAs(item.type) && showBoundType==='out'"> as data output</div>
      <div class="input-group flex-grow-1 flex-nowrap input-group-sm ms-2 me-2" v-if="hasOutputAs(item.type)">
        <div class="input-group-text border-0 fs-7 p-0 bg-white">{{t('api.outputAS')}}&nbsp;</div>
        <AdvanceSelect :options="outputAsItems(item)" @click="(option) => changeOutputAs(item, option.value, showBoundType)" :default-text="myModel.out[item.meta.id] || ''"></AdvanceSelect>
      </div>
      <div class="input-group flex-grow-1 flex-nowrap input-group-sm me-2" v-if="hasBoundAs(item.type)">
        <div class="input-group-text border-0 fs-7 p-0 bg-white">{{t('api.boundAS')}}&nbsp;</div>
        <AdvanceSelect :options="boundAsItems" @click="(option) => changeBoundAs(item, option.value)" :default-text="myModel.bound?.[item.meta.id] || 'none'"></AdvanceSelect>
      </div>
      <i class="iconfont icon-remove text-danger" @click.stop.prevent="removeBind(item, showBoundType)" ></i>
    </div>
  </div>
  <template v-if="myModel.type=='array' && isOpen">
    <Data :model="myModel.item" :from-type="fromType" :from-id="fromId" :open="open" :path="[...path, myModel.name]"
               :index="0" @update="updateItem" :can-mutation="canMutation" :intent="intent+1"
               :can-input="false" :can-output="canOutput" :is-array-item="true"></Data>
  </template>
  <template v-else-if="myModel.type=='object' && isOpen">
    <div class="text-muted text-center" v-if="!myModel.props || myModel.props.length==0">{{t('api.model.noSubField')}}</div>
    <template v-else>
      <Data v-for="(item, index) in myModel.props" :from-type="fromType" :from-id="fromId" :open="open" :path="[...path, myModel.name]"
                 @remove="removeItem" @update="updateItem" :can-input="canInput" :can-output="canOutput"
                 :can-mutation="canMutation" :key="index" :intent="intent+1" :model="item" :index="index"></Data>
    </template>
  </template>
  <lay-layer v-model="editDlgVisible" :title="isAdd ? t('api.addData') : t('api.editData')" :shade="true" :area="['520px', '500px']" :btn="buttons">
    <AddData v-model="editModel" :has-default-value="path.length==0" :is-array-item="!isAdd && isArrayItem"/>
  </lay-layer>
  <CodeEditor :read-only="true" v-model="viewDefaultDlgVisible" :code="code"></CodeEditor>
  <DataInfo :data="myModel" v-model="detailDlgVisible"></DataInfo>
</template>

<script lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { layer } from '@layui/layer-vue'
import ConfirmRemove from '@/components/common/ConfirmRemove.vue'
import AddData from '@/components/common/AddData.vue'
import canvas from '@/lib/canvas'
import { useStore } from 'vuex'
import ydhl from '@/lib/ydhl'
import $ from 'jquery'
import baseUIDefines from '@/components/ui/define'
import DataInfo from '@/components/common/DataInfo.vue'
import CodeEditor from '@/components/common/CodeEditor.vue'
import AdvanceSelect from '@/components/common/AdvanceSelect.vue'
// 数据模型展示，可绑定ui
export default {
  name: 'Data',
  components: { AdvanceSelect, CodeEditor, DataInfo, AddData, ConfirmRemove },
  emits: ['remove', 'update'],
  props: {
    model: Object,
    index: Number,
    path: {
      default: () => [],
      type: Array
    },
    canInput: Boolean,
    canOutput: Boolean,
    open: Boolean, // 默认是否打开
    fromId: String, // 该数据来源于哪里
    fromType: String, // 数据来源那个表
    isArrayItem: Boolean, // 数组结点标识,用于标识数组的第一个结点
    canMutation: Boolean, // 是否能编辑和删除
    intent: Number // 缩进次数
  },
  setup (props: any, context: any) {
    const editModel = ref()
    const myModel = computed<any>(() => props.model)
    const { t } = useI18n()
    const editDlgVisible = ref(false)
    const confirmRemove = ref(false)
    const showBoundPop = ref(false)
    const showOutputTypeMenu = ref(false)
    const detailDlgVisible = ref(false)
    const viewDefaultDlgVisible = ref(false)
    const boundPop = ref()
    const outputTypeMenu = ref()
    const drawFromEl = ref()
    const currBindOutUI = ref()
    const isAdd = ref(false)
    const isOpen = ref(props.open)
    const showBoundType = ref('')
    const code = ref('')
    const store = useStore()
    const XYInIframe = computed(() => store.state.design.mouseXYInIframe)
    const mouseupInFrame = computed(() => store.state.design.mouseupInFrame)
    const pageScale = computed(() => store.state.design.scale)
    const currFunctionId = computed(() => store.state.design.function.id)
    const currPage = computed(() => store.state.design.page)
    const versionId = computed(() => store.state.design.pageVersionId[currPage.value.meta.id])
    const myCanInput = computed(() => {
      // 数组项，复杂数组或者上级指定不能绑定输入的
      const canNot = props.isArrayItem ||
        myModel.value.type === 'map' || myModel.value.type === 'object' ||
        !props.canInput ||
        (myModel.value.type === 'array' && ['array', 'map'].indexOf(myModel.value.item.type) !== -1)
      return !canNot
    })
    const myCanOutput = computed(() => {
      // 上级指定不能绑定输出的
      if (!props.canOutput) return false
      if (props.isArrayItem) {
        // 数组子项是数组的能，其他不能
        return myModel.value.type === 'array'
      }
      return props.canOutput
    })
    const dataInfo = computed(() => {
      return [
        { name: 'type', value: '' }
      ]
    })
    const enumValues = computed(() => {
      if (!myModel.value.enumValue) {
        return ''
      }
      const enumValue: any = []
      for (const key in myModel.value.enumValue) {
        enumValue.push(key)
      }
      return enumValue.join(' | ')
    })
    const boundUIItems = computed(() => {
      let items: any = []
      if (showBoundType.value === 'in') {
        if (myModel.value.in) items.push(myModel.value.in)
      } else if (showBoundType.value === 'out') {
        items = myModel.value.out ? Object.keys(myModel.value.out) : []
      }
      const rst: any = []
      for (const itemid of items) {
        const { uiConfig } = store.getters.getUIItem(itemid)
        if (uiConfig) rst.push(uiConfig)
      }
      return rst
    })
    const currBindType = ref('')
    const hoverUIItemId = computed({
      get: () => store.state.design?.hoverUIItemId || undefined,
      set: (v) => {
        store.commit('updatePageState', { hoverUIItemId: v })
      }
    })
    const selectedUIItemId = computed(() => store.state.design?.selectedUIItemId || undefined)
    const selectedPageId = computed(() => store.state.design?.page?.meta?.id)
    const outputTypeStyle = computed(() => {
      if (!showOutputTypeMenu.value || !mouseupInFrame.value || !outputTypeMenu.value) return ''
      const rect = outputTypeMenu.value.getBoundingClientRect()
      const position = mouseupInFrame.value.split('_')
      const top = Math.max(0, rect.height + parseFloat(position[1]) > document.body.clientHeight ? (document.body.clientHeight - rect.height - 30) : parseFloat(position[1]))
      return `position:fixed; left:${position[0]}px; top:${top}px; z-index:12;background-color: transparent !important;`
    })
    const hoverUIItem = computed(() => {
      if (!hoverUIItemId.value) return null
      const { uiConfig } = store.getters.getUIItemInPage(hoverUIItemId.value, selectedPageId.value)
      return uiConfig
    })
    const save = (index) => {
      if (!props.isArrayItem) {
        if (!editModel.value.name) {
          layer.msg(t('api.pleaseInputName'))
          return
        }
        if (!editModel.value.name.match(/^[a-zA-Z_][a-zA-Z0-9_]*$/)) {
          layer.msg(t('api.inputNameInvalid'))
          return
        }
      }
      editDlgVisible.value = false
      if (isAdd.value) { // object
        if (!myModel.value.props) myModel.value.props = []
        myModel.value.props.push(editModel.value)
        context.emit('update', index, JSON.parse(JSON.stringify(myModel.value)))
      } else { // 数组
        context.emit('update', index, JSON.parse(JSON.stringify(editModel.value)))
      }
    }
    const buttons = computed(() => {
      const btns = [
        {
          text: t('common.save'),
          callback: () => save(props.index)
        },
        {
          text: t('common.cancel'),
          callback: () => {
            editDlgVisible.value = false
          }
        }
      ]
      if (myModel.value.isRoot) {
        btns.splice(1, 0,
          {
            text: t('common.copy'),
            callback: () => {
              editModel.value.uuid = ydhl.uuid()
              save(-1)
            }
          })
      }
      return btns
    })

    watch(selectedUIItemId, (n, o) => {
      if (n !== o) showOutputTypeMenu.value = false
    })
    watch(() => props.open, (n) => {
      isOpen.value = n
    })
    watch(XYInIframe, (v) => {
      if (canvas.getDrawFromId() !== props.model.uuid) return
      const ifrmae = document.getElementById('wrapper' + selectedPageId.value)
      if (ifrmae) {
        const { x, y } = ifrmae.getBoundingClientRect()
        // console.log(v.x, v.y, x, y)
        canvas.mouseoverInIframe(v.x * pageScale.value + x, v.y * pageScale.value + y)
      }
    })
    // 在某个ui上松开后结束画线,并设置绑定关系
    watch(mouseupInFrame, (v) => {
      if (!canvas.isDrawline() || canvas.getDrawFromId() !== props.model.uuid) return
      canvas.stopDrawline()
      currBindOutUI.value = hoverUIItem.value
      if (currBindType.value === 'in') {
        if (!hoverUIItem.value?.meta.form && !baseUIDefines[hoverUIItem.value.type].isIterable) {
          ydhl.alert(t('variable.bindInputInvalid'))
          return
        }
        if (hoverUIItem.value?.dataIn) {
          ydhl.alert(t('variable.hasBoundInput'))
          return
        }
        saveBound(hoverUIItem.value, currBindType.value, '', hoverUIItem.value.type)
      } else {
        const items = outputAsItems(hoverUIItem.value)
        if (items.length === 0) {
          ydhl.alert(t('variable.cannotBindOutput'))
          return
        }
        showOutputTypeMenu.value = true
      }
    })
    const boundAsItems = computed(() => {
      return [
        { name: 'None', value: 'none' },
        { name: 'Value', value: 'VALUE', desc: t('variable.boundAsValue') },
        { name: 'Bound', value: 'BOUND', desc: t('variable.boundAsBound') }
      ]
    })
    const outputAsItems = (uiItem) => {
      const uiType = uiItem.type
      const _: any = []
      const map: any = {
        Iterable: {
          HTML: [],
          TEXT: [],
          VALUELIST: ['map', 'object', 'array'],
          STYLE: ['string', 'number', 'integer', 'map', 'object', 'array'],
          NONE: ['array'],
          CSS: ['string', 'number', 'integer', 'map', 'object', 'array'],
          TITLE: ['string', 'number', 'integer', 'map', 'object', 'array'],
          ALT: ['string', 'number', 'integer', 'map', 'object', 'array'],
          KEYVALUE: ['map', 'object']
        },
        unIterable: {
          HTML: ['string', 'number', 'integer', 'map', 'object', 'array', 'boolean', 'any'],
          TEXT: ['string', 'number', 'integer', 'map', 'object', 'array', 'boolean', 'any'],
          VALUELIST: ['string', 'number', 'integer', 'array'],
          VALUE: ['string', 'number', 'integer'],
          STYLE: ['string', 'number', 'integer', 'map', 'object', 'array'],
          CSS: ['string', 'number', 'integer', 'map', 'object', 'array'],
          TITLE: ['string', 'number', 'integer', 'map', 'object', 'array', 'boolean', 'any'],
          ALT: ['string', 'number', 'integer', 'map', 'object', 'array', 'boolean', 'any'],
          NONE: ['string', 'number', 'integer', 'map', 'object', 'array', 'boolean', 'any'],
          KEYVALUE: ['object', 'map', 'array']
        }
      }
      const dataType = myModel.value.type
      const scaleTypes = ['string', 'number', 'integer']
      const is2DArray = myModel.value.type === 'array' && myModel.value.item.type === 'array'
      const isScale = scaleTypes.indexOf(dataType) !== -1
      const isObject = ['object', 'map'].indexOf(dataType) !== -1
      const isArray = myModel.value.type === 'array'
      const is1DArray = myModel.value.type === 'array' && myModel.value.item.type !== 'array'
      const is1DObjectArray = myModel.value.type === 'array' && ['object', 'map'].indexOf(myModel.value.item.type) !== -1
      const is1DScaleArray = myModel.value.type === 'array' && scaleTypes.indexOf(myModel.value.item.type) !== -1
      const is2DScaleArray = is2DArray && scaleTypes.indexOf(myModel.value.item.item.type) !== -1
      const iterate = baseUIDefines[uiType].isIterable ? 'Iterable' : 'unIterable'
      for (const output of baseUIDefines[uiType].outputAs) {
        if (map[iterate][output].indexOf(dataType) === -1) continue
        // css 输出不支持object和map
        if (output === 'CSS' && isObject) continue
        if (iterate === 'Iterable') {
          if (output === 'STYLE' && !isObject && !is1DScaleArray && !isScale) continue
          if (output === 'CSS' && !is1DScaleArray && !isScale) continue
          if (output === 'NONE' && !is2DArray) continue
          if (output === 'VALUELIST' && ['Collapse', 'Carousel'].indexOf(uiType) !== -1 && (isObject || is1DObjectArray || (is2DArray && !is2DScaleArray))) continue
        } else {
          if (output === 'VALUELIST' && !isScale && !is1DScaleArray) continue
          if (output === 'STYLE' && !isScale && !isObject && !is1DArray && (is2DArray && !is2DScaleArray)) continue
          if (output === 'CSS' && !isScale && !is1DScaleArray && (is2DArray && !is2DScaleArray)) continue
          if (output === 'KEYVALUE' && !isObject && !is1DObjectArray) continue
          if (output === 'NONE' && !isArray) continue
        }
        _.push({ name: output + (uiItem.dataOut?.[output] ? ` (${t('variable.hasBeenBound')})` : ''), value: output, desc: t('variable.output.' + output), disabled: !!uiItem.dataOut?.[output] })
      }
      return _
    }
    const outputAndBoundItems = (uiItem) => {
      const items: any = outputAsItems(uiItem)
      if (hasBoundAs(uiItem.type)) {
        items.splice(0, 0, { header: t('api.outputAS') })
        items.push({ header: t('api.boundAS') })
        items.push({ name: 'VALUE', value: 'VALUE', type: 'bound', desc: t('variable.boundAsValue') })
        items.push({ name: 'BOUND', value: 'BOUND', type: 'bound', desc: t('variable.boundAsBound') })
      }
      return items
    }
    const hasOutputAs = (uiType) => {
      if (showBoundPop.value && showBoundType.value !== 'out') return false
      if (baseUIDefines[uiType].outputAs.length === 0) return false
      return true
    }
    const hasBoundAs = (uiType) => {
      if (!hasOutputAs(uiType)) return false
      if (baseUIDefines[uiType].isInput) return false
      if (baseUIDefines[uiType].isIterable) return false
      return true
    }
    const changeOutputAs = (item, type, boundType) => {
      showOutputTypeMenu.value = false
      saveBound(item, boundType, type, item.type)
    }
    const changeBoundAs = (item, boundAs) => {
      showOutputTypeMenu.value = false
      saveBound(item, 'out', myModel.value.out?.[item.meta.id] || 'NONE', item.type, boundAs)
    }
    const changeOutputOrBound = (uiItem, option) => {
      if (option?.type === 'bound') {
        changeBoundAs(uiItem, option.value)
      } else {
        changeOutputAs(uiItem, option.value, 'out')
      }
    }

    const startDrag = (bool: boolean) => {
      // console.log('mousedown')
      canvas.setStartDrag(bool)
    }
    const beginDraw = (event, type) => {
      if (!canvas.isDrag() || canvas.isDrawline()) return
      // console.log(props.model.name, props.model.type, props.model.uuid, type)
      currBindType.value = type
      drawFromEl.value = event.target
      canvas.startDrawline(event.clientX, event.clientY, props.model.uuid)
    }

    const edit = () => {
      isAdd.value = false
      editModel.value = JSON.parse(JSON.stringify(props.model))
      editDlgVisible.value = true
    }
    const add = () => {
      isAdd.value = true
      editModel.value = { type: 'string', uuid: ydhl.uuid() }
      editDlgVisible.value = true
    }
    const showComment = () => {
      layer.confirm(props.model.comment, { title: 'YDUIBuilder' })
    }

    // 删除根级数据
    const remove = () => {
      context.emit('remove', props.index)
    }
    // 删除数据中的数据
    const removeItem = (index: number) => {
      myModel.value.props.splice(index, 1)
      context.emit('update', props.index, myModel.value) // 通知最顶层通过接口更新后端
    }
    const updateItem = (index: number, item: any) => {
      if (myModel.value.type === 'array') {
        myModel.value.item = item
      } else {
        myModel.value.props[index] = item
      }
      context.emit('update', props.index, myModel.value) // 通知最顶层通过接口更新后端
    }
    // 高亮显示绑定的元素
    const highlight = (type, uuid = null) => {
      if (uuid) {
        store.commit('updatePageState', { highlightUIItemIds: [uuid] })
        return
      }
      let items: any = []
      if (type === 'in') {
        if (myModel.value.in) items.push(myModel.value.in)
      } else {
        items = myModel.value.out ? Object.keys(myModel.value.out) : []
      }
      store.commit('updatePageState', { highlightUIItemIds: items })
    }
    const offlight = () => {
      store.commit('updatePageState', { highlightUIItemIds: [] })
    }
    const showBound = (event: any, type: string) => {
      if (canvas.isDrawline()) return
      showBoundType.value = type
      // 由于阻止了时间冒泡，所以这里通过触发body上的click来通知其他的data.vue中的弹窗关闭
      $('body').trigger('click')
      nextTick(() => {
        ydhl.togglePopper(showBoundPop, event.target, boundPop, 'bottom')
      })
    }
    const viewDetail = () => {
      detailDlgVisible.value = true
    }

    const saveBound = (uiItem, type, outputAs, uiType, boundAs = '') => {
      const uiid = uiItem.meta.id
      ydhl.savePage(currFunctionId.value, currPage.value, versionId.value, (rst) => {
        const data: any = { saving: false }
        if (rst?.success) {
          data.saved = 1
          data.pageUuid = currPage.value.meta.id
          data.versionId = rst.data.versionId
        }
        store.commit('updateSavedState', data)
        const output = hasOutputAs(uiType) ? outputAs : ''
        ydhl.post('api/bind/io.json', {
          data_id: myModel.value.uuid,
          page_uuid: selectedPageId.value,
          ui_id: uiid,
          type,
          output_as: output,
          bound_as: boundAs,
          from_uuid: props.fromId
        }, [], (rst) => {
          if (!rst || !rst.success) {
            ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
            return
          }
          const metaProps: any = {}
          const path = [...props.path]// 断掉引用
          path.push(myModel.value.name)

          if (type === 'out') {
            if (!myModel.value.out) myModel.value.out = {}
            if (!myModel.value.bound) myModel.value.bound = {}
            const oldOutput = myModel.value.out[uiid]
            myModel.value.out[uiid] = output
            // 删除原来的输入类型，改成新的输入类型
            const old = uiItem.dataOut ? JSON.parse(JSON.stringify(uiItem.dataOut)) : {}
            delete old[oldOutput]
            old[output] = path.join('.')
            metaProps.dataOut = old

            if (boundAs) {
              const oldBound = myModel.value.bound[uiid]
              myModel.value.bound[uiid] = boundAs
              const _old = uiItem.dataBound ? JSON.parse(JSON.stringify(uiItem.dataBound)) : {}
              delete _old[oldBound]
              _old[boundAs] = path.join('.')
              metaProps.dataBound = _old
            }
          } else {
            if (myModel.value.in) {
              // 数据只能有一个ui提供输入，删除原来的输入绑定ui
              const { uiConfig } = store.getters.getUIItem(myModel.value.in)
              if (uiConfig) {
                store.commit('updateUIInfo', {
                  itemid: myModel.value.in,
                  pageId: selectedPageId.value,
                  props: {
                    dataIn: null
                  }
                })
              }
            }

            myModel.value.in = uiid
            metaProps.dataIn = { path: path.join('.') }
          }
          context.emit('update', props.index, myModel.value) // 通知最顶层通过接口更新后端
          store.commit('updateUIInfo', {
            itemid: uiid,
            pageId: selectedPageId.value,
            props: metaProps
          })
        }
        , 'json')
      })
    }
    const removeBind = (uiItem: any, type: string) => {
      const uiid = uiItem.meta.id
      const outputAs = myModel.value.out?.[uiid]
      ydhl.post('api/bind/remove.json', { ui_id: uiid, type, data_id: props.model.uuid, page_uuid: selectedPageId.value }, [], (rst) => {
        if (!rst.success) {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
          return
        }
        if (type === 'in') {
          myModel.value.in = null
        } else {
          delete myModel.value.out?.[uiid]
          if (ydhl.isEmptyObject(myModel.value.out)) myModel.value.out = null
          context.emit('update', props.index, myModel.value) // 通知最顶层通过接口更新后端
        }

        const metaProps: any = {}
        if (type === 'out') {
          delete uiItem.dataOut?.[outputAs]
          metaProps.dataOut = uiItem.dataOut ? JSON.parse(JSON.stringify(uiItem.dataOut)) : {}
        } else {
          uiItem.dataIn = null
          metaProps.dataIn = null
        }
        store.commit('updateUIInfo', {
          itemid: uiid,
          pageId: selectedPageId.value,
          props: metaProps
        })
      })
    }

    const closeDropmenu = () => {
      if (!boundPop.value) return
      boundPop.value.querySelectorAll('.dropdown-toggle').forEach((el) => el.classList.remove('show'))
      boundPop.value.querySelectorAll('.dropdown-menu').forEach((el) => el.classList.remove('show'))
    }
    const viewDefaultValue = (newCode) => {
      viewDefaultDlgVisible.value = true
      code.value = newCode
    }

    onMounted(() => {
      document.body.addEventListener('click', (event: any) => {
        showBoundPop.value = false
        showOutputTypeMenu.value = false
      }, {})
    })
    return {
      editModel,
      t,
      viewDefaultDlgVisible,
      enumValues,
      editDlgVisible,
      isOpen,
      confirmRemove,
      detailDlgVisible,
      dataInfo,
      myModel,
      buttons,
      isAdd,
      code,
      outputAsItems,
      outputAndBoundItems,
      baseUIDefines,
      boundUIItems,
      currBindType,
      myCanInput,
      myCanOutput,
      removeItem,
      updateItem,
      showComment,
      highlight,
      offlight,
      viewDefaultValue,
      edit,
      add,
      remove,
      startDrag,
      changeOutputOrBound,
      changeOutputAs,
      changeBoundAs,
      beginDraw,
      removeBind,
      viewDetail,
      showBoundType,
      showOutputTypeMenu,
      outputTypeStyle,
      boundAsItems,
      currBindOutUI,
      boundPop,
      showBoundPop,
      outputTypeMenu,
      closeDropmenu,
      showBound,
      hasOutputAs,
      hasBoundAs
    }
  }
}
</script>
