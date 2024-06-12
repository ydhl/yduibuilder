<template>
  <div class="row">
    <label class="col-sm-3 col-form-label text-end">{{ t('common.subPage') }}</label>
    <div class="col-sm-9 d-flex align-items-center justify-content-end">
      <div class="dropdown">
        <a class="dropdown-toggle btn btn-block btn-primary btn-xs" role="button" data-bs-toggle="dropdown" aria-haspopup="true" href="javascript:;" aria-expanded="false">
          {{t('common.addSubPage')}}
        </a>
        <div class="dropdown-menu">
          <a href='javascript:;' class='dropdown-item' @click="addSlide">{{t('common.addSubPage')}}</a>
          <a href='javascript:;' class='dropdown-item' @click="pagePickDialogVisible=true">{{t('page.selectPage')}}</a>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-9 offset-sm-3">
      <div class="list-group list-group-flush">
        <draggable v-model="valueItems" handle=".icon-drag" @change="saveValueItems">
          <transition-group>
            <div class="list-group-item d-flex align-items-center p-0" @click="setActiveSlide(index)" v-for="(item, index) of valueItems" :key="index">
              <div><i class="iconfont icon-drag" style="cursor: move"></i></div>
              <i class="iconfont icon-radio text-primary" v-if="uiItem.meta.custom?.activeIndex==index"></i>
              <i class="iconfont icon-radio text-muted" v-if="uiItem.meta.custom?.activeIndex!=index"></i>
              <div class="flex-grow-1 text-truncate">{{item.meta.title}}</div>
              <div class="btn-group btn-group-sm">
                <div class="hover-text-primary" @click="editSlide(index)" title="Edit"><i class="iconfont icon-edit"></i></div>
                <div class="hover-text-primary" @click="copySlide(index)" title="Copy"><i class="iconfont icon-copy"></i></div>
                <ConfirmRemove @remove="removeSlide(index)"></ConfirmRemove>
              </div>
            </div>
          </transition-group>
        </draggable>
      </div>
    </div>
  </div>
  <lay-layer v-model="pagePickDialogVisible" :title="t('common.page')" :shade="true" :area="['500px', '500px']" :btn="pagePickButtons">
    <div class="p-3">
      <PagePicker :page-types="['subpage']" @update="pickedPage"></PagePicker>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'
import ydhl from '@/lib/ydhl'
import { VueDraggableNext } from 'vue-draggable-next'
import { onMounted, ref } from 'vue'
import PagePicker from '@/components/common/PagePicker.vue'
import ConfirmRemove from '@/components/common/ConfirmRemove.vue'
import { useRouter } from 'vue-router'

export default {
  name: 'SubpageList',
  props: {
    excludeUi: {
      default: () => [],
      type: Array
    },
    uiItem: Object,
    pageId: String
  },
  components: {
    ConfirmRemove,
    PagePicker,
    draggable: VueDraggableNext
  },
  setup (props: any, context: any) {
    const { t } = useI18n()
    const store = useStore()
    const pagePickDialogVisible = ref(false)
    const valueItems = ref<any>([])
    const router = useRouter()

    const pickedPageInfo = ref<any>({ // 用于缓存pickPage中的数据
      pageId: '',
      pageTitle: ''
    })
    const addSlide = () => {
      // 创建新对话框页面
      store.commit('createSubpage', {
        itemid: props.uiItem.meta.id,
        pageId: props.pageId,
        excludeUI: props.excludeUi
      })
    }
    const removeSlide = (index: number) => {
      store.commit('deleteSubpage', { itemid: props.uiItem.meta.id, index, pageId: props.pageId })
    }
    const copySlide = (index: number) => {
      const selectedItem = props.uiItem
      const item = selectedItem.items?.[index]
      if (!item) return

      ydhl.loading(t('common.pleaseWait')).then((dlg) => {
        ydhl.closeLoading(dlg)
        ydhl.postJson('api/copy/page.json', { copy_page_uuid: item.subPageId }).then((rst: any) => {
          if (!rst || !rst.success) {
            ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
            return
          }
          const newItem = rst.data
          newItem.subPageId = rst.data.meta.id
          valueItems.value.push(newItem)
          saveValueItems()
        })
      })
    }
    const editSlide = (index: number) => {
      const selectedItem = props.uiItem
      const item = selectedItem.items?.[index]
      if (!item) return
      router.push({
        path: '/',
        query: {
          uuid: item.subPageId
        }
      })
    }
    const setActiveSlide = (index: number) => {
      store.commit('updateItemMeta', { type: 'custom', pageId: props.pageId, itemid: props.uiItem.meta.id, props: { activeIndex: index } })
    }
    const saveValueItems = () => {
      const values = JSON.parse(JSON.stringify(valueItems.value))
      store.commit('updateUIInfo', { itemid: props.uiItem.meta.id, pageId: props.pageId, props: { items: values } })
    }
    const pickedPage = (pageTitle, pageUuid) => {
      pickedPageInfo.value.pageId = pageUuid
      pickedPageInfo.value.pageTitle = pageTitle
    }
    const pagePickButtons = ref([
      {
        text: t('common.ok'),
        callback: () => {
          pagePickDialogVisible.value = false
          ydhl.get('api/load.json', { pageId: pickedPageInfo.value.pageId, justPage: 1 }, (rst) => {
            if (!rst || !rst.success) {
              ydhl.alert(rst ? rst.msg : 'Oops, Please try again')
              return
            }
            const item = rst.data.design.page
            item.subPageId = pickedPageInfo.value.pageId
            valueItems.value.push(item)
            saveValueItems()
          })
        }
      }
    ])

    onMounted(() => {
      valueItems.value = props.uiItem.items || []
    })

    return {
      t,
      valueItems,
      pagePickButtons,
      pagePickDialogVisible,
      pickedPage,
      saveValueItems,
      addSlide,
      removeSlide,
      editSlide,
      copySlide,
      setActiveSlide
    }
  }
}
</script>
