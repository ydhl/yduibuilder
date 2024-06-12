<template>
  <div class="d-flex align-items-center">
    <AdvanceSelect :options="events" @click="(option)=>updateEmit(option.value)" :default-text="modelValue.emit?.event || t('action.notSet')"></AdvanceSelect>
    <div v-if="!pageDataInline && modelValue.emit?.event" class="pointer fs-7 ms-3 d-flex align-items-center" @click="openBindPageDataDlg()">
      <div v-if="boundCount>0" class="text-danger fw-bold">
        <i class="iconfont icon-connect fs-7 hover-primary"></i>{{boundCount}}
      </div>
      <template v-else>
        <i class="iconfont icon-connect fs-7 hover-primary text-muted"></i>
      </template>
    </div>
  </div>

  <div class="flex-grow-1" v-if="pageDataInline && modelValue.emit?.event">
    <DataConnect v-for="(item, index) in myAction.emit?.args" @updateConnectData="updateConnectData"
                 connect="to"
                 :bound-data="myAction.input" :variables="variables" path="" :root-uuid="item.uuid"
                 :key="index" :intent="0" :model="item" :index="0">
    </DataConnect>
  </div>

  <lay-layer v-model="pageDataBindDlgVisible" :title="t('variable.bound')" :shade="true" :area="['500px', '500px']" :btn="pageDataBindDlgButtons">
    <div class="p-3">
      <div class="text-muted" v-if="!myAction.emit?.args || !myAction.emit?.args.length">{{t('common.empty')}}</div>
      <DataConnect v-for="(item, index) in myAction.emit?.args" @updateConnectData="updateConnectData"
                   connect="to"
                   :bound-data="myAction.input" :variables="variables" path="" :root-uuid="item.uuid"
                   :key="index" :intent="0" :model="item" :index="0">
      </DataConnect>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, onMounted, ref } from 'vue'
import ydhl from '@/lib/ydhl'
import { useStore } from 'vuex'
import AdvanceSelect from '@/components/common/AdvanceSelect.vue'
import DataConnect from '@/components/common/DataConnect.vue'
import { Expression } from '@/store/model'
// 当前页面触发自定义事件
export default {
  name: 'EmitSetting',
  components: { DataConnect, AdvanceSelect },
  props: {
    modelValue: Object,
    variables: Object,
    pageDataInline: Boolean, // true 页面数据绑定直接展示，false 弹窗展示, 弹窗在关闭时会调用api保存
    autosave: {
      default: true,
      type: Boolean
    } // 是否自动提交接口保存
  },
  emits: ['update:modelValue'],
  setup (props: any, context: any) {
    const { t } = useI18n()// 自定义的事件
    const store = useStore()
    const myAction = ref(props.modelValue)
    const pageDataBindDlgVisible = ref(false)
    const boundData = ref(props.modelValue.input || {})
    const selectedPage = computed(() => store.state.design.page)
    const selectedPageId = computed(() => selectedPage.value?.meta?.id)
    const events = computed(() => {
      const _: any = []
      for (const e of declaredEvents.value) {
        _.push({ name: e.name, value: e.name, desc: e.desc })
      }
      return _
    })
    const boundCount = computed(() => {
      if (ydhl.isEmptyObject(myAction.value.input)) return 0
      return Object.keys(myAction.value.input).length
    })
    const declaredEvents = computed<Array<any>>({
      set (v) {
        store.commit('updateState', { declaredEvents: v })
      },
      get () {
        return store.state.design.declaredEvents || []
      }
    })
    onMounted(() => {
      if (declaredEvents.value.length === 0) {
        loadDeclaredEvent()
      }
    })
    const loadDeclaredEvent = () => {
      declaredEvents.value = []
      if (selectedPage.value.pageType !== 'component') return
      ydhl.get('api/event/declare.json?page_uuid=' + selectedPageId.value, [], (rst) => {
        if (!rst.success) return
        declaredEvents.value = rst.data
      }, 'json')
    }
    const updateEmit = (value) => {
      if (!myAction.value.emit) myAction.value.emit = {}
      myAction.value.emit.event = value
      const event = declaredEvents.value.find((item) => item.name === value)
      myAction.value.emit.args = event?.args || []
      save(value, {})
    }

    // eslint-disable-next-line camelcase
    const save = (eventName, input) => {
      return new Promise((resolve, reject) => {
        // 某些情况下不需要自动保存，比如在对话框中，因为他总的有个保存按钮
        if (!props.autosave) {
          reject(new Error(''))
          return
        }
        // console.log(input)
        ydhl.postJson('api/action/emit.json',
          { action_uuid: myAction.value.uuid, input, event: eventName, page_uuid: selectedPageId.value }).then((res: any) => {
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
      })
    }

    const openBindPageDataDlg = () => {
      pageDataBindDlgVisible.value = true
    }

    const pageDataBindDlgButtons = ref([
      {
        text: t('common.ok'),
        callback: () => {
          myAction.value.input = boundData.value
          save(myAction.value.emit.event, JSON.parse(JSON.stringify(boundData.value))).finally(() => {
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

    // 弹窗的数据绑定保存在action的input中，
    const updateConnectData = (fromRootUuid, fromPath, fromUuid, toData: Expression, remove, expDesc: string) => {
      if (remove) {
        boundData.value = null
      } else {
        if (!boundData.value) {
          boundData.value = {}
        }
        boundData.value[fromUuid] = {
          expression: toData,
          desc: expDesc
        }
      }
      myAction.value.input = boundData.value
      context.emit('update:modelValue', myAction.value)
    }
    return {
      t,
      declaredEvents,
      events,
      myAction,
      boundCount,
      pageDataBindDlgVisible,
      pageDataBindDlgButtons,
      updateEmit,
      openBindPageDataDlg,
      updateConnectData,
      save
    }
  }
}
</script>
