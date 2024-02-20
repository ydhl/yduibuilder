<template>
  <div class="model-item">
    <div class="d-flex align-items-center">
      <i class="iconfont icon-data-input bind-icon invisible" v-if="isArrayItem || myModel.type == 'object' || !canInput"></i>
      <i v-else @mousedown="startDrag(true)" @mouseup="startDrag(false)"
         @click.stop.prevent="showBound($event,'in')" @mousemove="beginDraw($event,'in')"
         @mouseover="highlight('in')" @mouseleave="offlight()"
         :class="{'iconfont icon-data-input bind-icon': true, 'bound': myModel.in}"
         data-bs-toggle="tooltip" :title="t('api.bindInput')"></i>

      <i class="iconfont icon-data-output bind-icon invisible" v-if="isArrayItem || myModel.type == 'object' || !canOutput"></i>
      <i v-else @mousedown="startDrag(true)" @mouseup="startDrag(false)"
         @click.stop.prevent="showBound($event,'out')" @mousemove="beginDraw($event,'out')"
         @mouseover="highlight('out')" @mouseleave="offlight()"
         :class="{'iconfont icon-data-output bind-icon': true, 'bound': myModel.out}"
         data-bs-toggle="tooltip" :title="t('api.bindOutput')"></i>
    </div>
    <div class="model-field text-truncate" @click="isOpen = !isOpen" :style="'width:0px;padding-left: ' + (intent * 16) + 'px'">
      <i v-if="myModel.type=='array' || myModel.type=='object'" :class="{'iconfont':true, 'icon-tree-close': !isOpen, 'icon-tree-open': isOpen}"></i>
      <i v-if="myModel.type!='array' && myModel.type!='object'" style="width: 16px;height: 24px;">&nbsp;</i>
      <template v-if="isArrayItem">
        <span class="text-success fw-bold">ITEM</span>
      </template>
      <template v-else>
        {{myModel.name}}
      </template>
      <span :class="'ps-1 param-' + myModel.type">
        {{myModel.type}} <span v-if="myModel.defaultValue" class="text-muted ps-1">{{myModel.defaultValue}}</span>
      </span>
      <span class="text-truncate ps-2 pointer text-muted" @click="showComment()">
        {{myModel.comment}}
      </span>
    </div>
    <!--title-->
    <div class="model-title">
      <template v-if="!isArrayItem">{{myModel.title}}</template>
    </div>
    <div class="model-action" v-if="canMutation">
      <i class="iconfont icon-plus pointer text-muted hover-primary" @click.stop="add" v-if="myModel.type=='object'"></i>
      <ConfirmRemove @remove="remove" v-if="!isArrayItem"></ConfirmRemove>
      <i class="iconfont icon-edit pointer text-muted hover-primary" @click.stop="edit"></i>
    </div>
  </div>
  <!--绑定的元素菜单-->
  <div class="list-group shadow-sm" ref="boundPop" v-if="showBoundPop">
    <div class="list-group-item justify-content-between d-flex align-items-center"
       @mouseover="highlight('', item.meta.id)" @mouseleave="offlight()"
       v-for="(item,index) in boundItems" :key="index">
      <div>
        <i :class="`iconfont text-primary icon-${item.type.toLowerCase()}`"></i>&nbsp;{{ item.type }}
      </div>
      <div class="input-group input-group-sm ms-2 me-2">
        <div class="input-group-text fs-7">{{t('api.outputAS')}}</div>
        <div class="dropdown">
          <button class="btn btn-outline-secondary btn-sm dropdown-toggle" @click.stop.prevent type="button" data-bs-toggle="dropdown">{{myModel.out[item.meta.id] || t('common.notSpecified')}}</button>
          <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
            <li >
              <a href="javascript:void(0)" @click.stop.prevent="changeOutputAs(item, '', showBoundType)" class="dropdown-item">{{t('common.notSpecified')}}</a>
            </li>
            <li v-for="(type, index) in outputAsItems(item)" :key="index">
              <a href="javascript:void(0)" @click.stop.prevent="changeOutputAs(item, type, showBoundType)" class="dropdown-item">{{type}}</a>
            </li>
          </ul>
        </div>
      </div>
      <i class="iconfont icon-remove text-danger" @click.stop.prevent="removeBind(item.meta.id, showBoundType)" ></i>
    </div>
  </div>
  <template v-if="myModel.type=='array' && isOpen">
    <ModelItem :model="myModel.item" :from-type="fromType" :from-id="fromId"
               :index="0" @update="updateItem" :can-mutation="canMutation" :intent="intent+1"
               :can-input="(myModel.item.type=='array' || myModel.item.type=='object') && canInput"
               :can-output="(myModel.item.type=='array' || myModel.item.type=='object') && canOutput"
               :is-array-item="myModel.item.type!='array' && myModel.item.type!='object'"></ModelItem>
  </template>
  <template v-else-if="myModel.type=='object' && isOpen">
    <div class="text-muted text-center" v-if="!myModel.props || myModel.props.length==0">{{t('api.model.noSubField')}}</div>
    <template v-else>
      <ModelItem v-for="(item, index) in myModel.props" :from-type="fromType" :from-id="fromId"
                 @remove="removeItem" @update="updateItem" :can-input="canInput" :can-output="canOutput"
                 :can-mutation="canMutation" :key="index" :intent="intent+1" :model="item" :index="index"></ModelItem>
    </template>
  </template>
  <lay-layer v-model="editDlgVisible" :title="isAdd ? t('api.addData') : t('api.editData')" :shade="true" :area="['520px', '500px']" :btn="buttons">
    <AddModelItem v-model="editModel" :is-array-item="!isAdd && isArrayItem"/>
  </lay-layer>
</template>

<script lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { layer } from '@layui/layer-vue'
import ConfirmRemove from '@/components/common/ConfirmRemove.vue'
import AddModelItem from '@/components/common/AddModelItem.vue'
import canvas from '@/lib/canvas'
import { useStore } from 'vuex'
import ydhl from '@/lib/ydhl'
import $ from 'jquery'
import baseUIDefines from '@/components/ui/define'

declare const bootstrap: any
export default {
  name: 'ModelItem',
  components: { AddModelItem, ConfirmRemove },
  emits: ['remove', 'update'],
  props: {
    model: Object,
    index: Number,
    canInput: Boolean,
    canOutput: Boolean,
    fromId: String, // 该数据来源于哪里
    fromType: String,
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
    const boundPop = ref()
    const isAdd = ref(false)
    const isOpen = ref(true)
    const showBoundType = ref('')
    const store = useStore()
    const XYInIframe = computed(() => store.state.design.mouseXYInIframe)
    const mouseupInFrame = computed(() => store.state.design.mouseupInFrame)
    const pageScale = computed(() => store.state.design.scale)
    const boundItems = computed(() => {
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
    const outputAsItems = (item) => {
      return baseUIDefines[item.type].outputAs
    }
    const changeOutputAs = (item, type, BoundType) => {
      if (!myModel.value.out) myModel.value.out = {}
      myModel.value.out[item.meta.id] = type
      saveBound(item.meta.id, BoundType, myModel.value.out?.[item.meta.id] || '')
    }

    const currBindType = ref('')
    const hoverUIItemId = computed({
      get: () => store.state.design?.hoverUIItemId || undefined,
      set: (v) => {
        store.commit('updatePageState', { hoverUIItemId: v })
      }
    })
    const selectedPageId = computed(() => store.state.design?.page?.meta?.id)
    const hoverUIItem = computed(() => {
      if (!hoverUIItemId.value) return null
      const { uiConfig } = store.getters.getUIItemInPage(hoverUIItemId.value, selectedPageId.value)
      return uiConfig
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
    watch(mouseupInFrame, (v) => {
      if (!canvas.isDrawline() || canvas.getDrawFromId() !== props.model.uuid) return
      // 点击某个ui后结束画线,并设置绑定关系
      canvas.stopDrawline()
      if (currBindType.value === 'in') {
        if (!hoverUIItem.value?.meta.form) {
          layer.msg(t('api.bindInputInvalid'))
          return
        } else {
          myModel.value.in = hoverUIItemId.value
        }
      } else {
        if (!myModel.value.out) myModel.value.out = {}
        myModel.value.out[hoverUIItemId.value] = ''
      }
      saveBound(hoverUIItemId.value, currBindType.value, myModel.value.out?.[hoverUIItemId.value])
    })
    const saveBound = (uiid, type, outputAs) => {
      // console.log(props.model.name, props.model.type, canvas.getDrawFromId(), currBindType.value)
      ydhl.post('api/bind/io.json', {
        data_id: myModel.value.uuid,
        page_uuid: selectedPageId.value,
        ui_id: uiid,
        type,
        output_as: outputAs,
        from_type: props.fromType,
        from_uuid: props.fromId
      }, [], (rst) => {
        //
      }, 'json')
    }
    const startDrag = (bool: boolean) => {
      // console.log('mousedown')
      canvas.setStartDrag(bool)
    }
    const beginDraw = (event, type) => {
      if (!canvas.isDrag() || canvas.isDrawline()) return
      // console.log(props.model.name, props.model.type, props.model.uuid, type)
      currBindType.value = type
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

    const buttons = ref([
      {
        text: t('common.ok'),
        callback: () => {
          if (!editModel.value.name && !props.isArrayItem) {
            layer.msg(t('api.pleaseInputName'))
            return
          }
          editDlgVisible.value = false
          if (isAdd.value) { // object
            if (!myModel.value.props) myModel.value.props = []
            myModel.value.props.push(editModel.value)
            context.emit('update', props.index, myModel.value)
          } else { // 数组
            context.emit('update', props.index, editModel.value)
          }
        }
      },
      {
        text: t('common.cancel'),
        callback: () => {
          editDlgVisible.value = false
        }
      }
    ])
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
      showBoundType.value = type
      ydhl.togglePopper(showBoundPop, event.target, boundPop, 'bottom')
    }

    const removeBind = (uiid: any, type: string) => {
      ydhl.post('api/bind/remove.json', { ui_id: uiid, type, data_id: props.model.uuid, page_uuid: selectedPageId.value }, [], (rst) => {
        if (rst.success) {
          if (type === 'in') {
            myModel.value.in = null
          } else {
            delete myModel.value.out[uiid]
            context.emit('update', props.index, myModel.value) // 通知最顶层通过接口更新后端
          }
        } else {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
        }
      })
    }

    onMounted(() => {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, { delay: { show: 800, hide: 300 } })
      })
      $('body').on('click', (event: any) => {
        showBoundPop.value = false
      })
    })
    return {
      editModel,
      t,
      editDlgVisible,
      isOpen,
      confirmRemove,
      myModel,
      buttons,
      isAdd,
      outputAsItems,
      boundItems,
      currBindType,
      removeItem,
      updateItem,
      showComment,
      highlight,
      offlight,
      edit,
      add,
      remove,
      startDrag,
      changeOutputAs,
      beginDraw,
      removeBind,
      showBoundType,
      boundPop,
      showBoundPop,
      showBound
    }
  }
}
</script>
