<template>
  <div class="row">
    <label class="col-sm-3 col-form-label text-end">{{ t('common.subPage') }}</label>
    <div class="col-sm-9 d-flex align-items-center justify-content-end gap-1">
      <div class="dropdown">
        <a class="dropdown-toggle btn btn-block btn-primary btn-xs" role="button" data-bs-toggle="dropdown" aria-haspopup="true" href="javascript:;" aria-expanded="false">
          {{t('common.addSubPage')}}
        </a>
        <div class="dropdown-menu">
          <a href='javascript:;' class='dropdown-item' @click="addSlide">{{t('common.addSubPage')}}</a>
          <a href='javascript:;' class='dropdown-item' @click="pagePickDialogVisible=true">{{t('page.selectPage')}}</a>
        </div>
      </div>
      <Upload v-model="image" :project-id="projectId" btn-css="btn-xs" :button-title="t('common.uploadImage')" :showImage="false"></Upload>
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
              <div class="btn-group btn-group-sm align-items-center gap-1">
                <template v-if="item.type==='Image'">
                  <div class="dropdown">
                    <div class="hover-text-primary" @click="editImageIndex = index" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" href="javascript:;" aria-expanded="false">
                      <i class="iconfont icon-setting"></i>
                    </div>
                    <div class="dropdown-menu" style="width: 300px">
                      <div class="pe-2">
                        <div class="row mb-1">
                          <label class="col-sm-3 col-form-label text-end">{{ t('page.title') }}</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control form-control-sm"
                                   placeholder="title" :value="item.meta.title" @blur="updateImage('title', item.meta.id, $event.target.value, '')">
                          </div>
                        </div>
                        <div class="row mb-1">
                          <label class="col-sm-3 col-form-label text-end">{{ t('style.sizing') }}</label>
                          <div class="col-sm-9">
                            <div class="input-group">
                              <input type="text" class="form-control form-control-sm"
                                     placeholder="width" :value="item.meta.style?.width" @blur="updateImage('width', item.meta.id, $event.target.value)">
                              <span class="input-group-text">x</span>
                              <input type="text" class="form-control form-control-sm"
                                     placeholder="height" :value="item.meta.style?.height" @blur="updateImage('height', item.meta.id, $event.target.value)">
                            </div>
                          </div>
                        </div>
                        <div class="row mb-1">
                          <label class="col-sm-3 col-form-label text-end">{{ t('style.image.objectFit') }}</label>
                          <div class="col-sm-9">
                            <select @change="updateImage('object-fit', item.meta.id, $event.target.value)" class="form-select form-select-sm">
                              <option :value="fit" v-for="(fit) in fits" :key="fit" :selected="fit===item.meta.style?.['object-fit']">{{fit}}</option>
                            </select>
                          </div>
                        </div>

                        <div class="row">
                          <label class="col-sm-3 col-form-label text-end">{{ t('style.image.objectPosition') }}</label>
                          <div class="col-sm-9">
                            <div class="mt-2 d-flex">
                              <Position v-model="position"></Position>
                              <div class="ms-1 flex-grow-1">
                                <div class="input-group input-group-sm mb-1">
                                  <span class="input-group-text">X</span>
                                  <input type="text" class="form-control" placeholder="X Position" v-model="positionX">
                                </div>
                                <div class="input-group input-group-sm mb-1">
                                  <span class="input-group-text">Y</span>
                                  <input type="text" class="form-control" placeholder="Y Position" v-model="positionY">
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
                <template v-else>
                  <div class="hover-text-primary" @click="editSlide(index)" title="Edit"><i class="iconfont icon-edit"></i></div>
                  <div class="hover-text-primary" @click="copySlide(index)" title="Copy"><i class="iconfont icon-copy"></i></div>
                </template>
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
import { computed, onMounted, ref } from 'vue'
import PagePicker from '@/components/common/PagePicker.vue'
import ConfirmRemove from '@/components/common/ConfirmRemove.vue'
import { useRouter } from 'vue-router'
import Upload from '@/components/common/Upload.vue'
import Position from '@/components/common/Position.vue'

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
    Position,
    Upload,
    ConfirmRemove,
    PagePicker,
    draggable: VueDraggableNext
  },
  setup (props: any, context: any) {
    const { t } = useI18n()
    const store = useStore()
    const pagePickDialogVisible = ref(false)
    const valueItems = ref<any>([])
    const editImageIndex = ref<any>('')
    const router = useRouter()
    const fits = ref(['fill', 'contain', 'cover', 'none', 'scale-down', 'initial', 'inherit'])
    const projectId = computed(() => store.state.design.project.id)

    const image = computed({
      get () {
        return {}
      },
      set (v: any) {
        store.commit('addItem', {
          type: 'Image',
          meta: {
            id: ydhl.uuid(5, 0, props.pageId),
            title: 'Image',
            value: v.url,
            isContainer: false,
            files: {
              value: v
            }
          },
          pageId: props.pageId,
          placement: 'in',
          targetId: props.uiItem.meta.id
        })
      }
    })
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
    function updateImage (name, itemid, value, type = 'style') {
      store.commit('updateItemMeta', {
        itemid,
        type,
        pageId: props.pageId,
        props: {
          [name]: value
        }
      })
    }

    const position = computed<Array<string>>({
      get () {
        const size = props.uiItem.items[editImageIndex.value]?.meta.style?.['object-position']
        return size?.split(' ') || []
      },
      set (v) {
        updateImage('object-position', props.uiItem.items[editImageIndex.value]?.meta.id, v.join(' '), 'style')
      }
    })
    const positionX = computed<Array<string>>({
      get () {
        const size = props.uiItem.items[editImageIndex.value]?.meta.style?.['object-position']
        return size?.split(' ')[0] || ''
      },
      set (v) {
        const size = props.uiItem.items[editImageIndex.value]?.meta.style?.['object-position']
        const old = size?.split(' ') || []
        old[0] = v
        updateImage('object-position', props.uiItem.items[editImageIndex.value]?.meta.id, old.join(' '), 'style')
      }
    })
    const positionY = computed<Array<string>>({
      get () {
        const size = props.uiItem.items[editImageIndex.value]?.meta.style?.['object-position']
        return size?.split(' ')[1] || ''
      },
      set (v) {
        const size = props.uiItem.items[editImageIndex.value]?.meta.style?.['object-position']
        const old = size?.split(' ') || []
        if (old.length < 2) {
          old[0] = '0px'
        }
        old[1] = v
        updateImage('object-position', props.uiItem.items[editImageIndex.value]?.meta.id, old.join(' '), 'style')
      }
    })
    return {
      t,
      valueItems,
      pagePickButtons,
      pagePickDialogVisible,
      projectId,
      pickedPage,
      fits,
      position,
      positionX,
      positionY,
      image,
      editImageIndex,
      saveValueItems,
      addSlide,
      removeSlide,
      editSlide,
      copySlide,
      updateImage,
      setActiveSlide
    }
  }
}
</script>
