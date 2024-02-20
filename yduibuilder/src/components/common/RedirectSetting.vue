<template>
  <div class="row">
    <label class="col-sm-3 col-form-label text-end">{{ t('action.redirectType') }}</label>
    <div class="col-sm-9">
      <select class="form-select form-select-sm" v-model="myModelValue.redirect_type">
        <option value="inside">Inside</option>
        <option value="outside">Outside</option>
      </select>
    </div>
  </div>
  <template v-if="myModelValue.redirect_type=='outside'">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('action.redirectUrl') }}</label>
      <div class="col-sm-9">
        <textarea v-model="myModelValue.redirect" class="form-control form-control-sm"></textarea>
      </div>
    </div>
  </template>
  <template v-else-if="myModelValue.redirect_type=='inside'">
    <template v-if="project.rewrite">
      <div class="row align-items-center">
        <label class="col-sm-3 col-form-label text-end">{{ t('action.redirectUrl') }}</label>
        <div class="col-sm-9">
          <div class="dropdown">
            <button class="btn btn-sm btn-light w-100" data-bs-toggle="dropdown">{{myModelValue.redirect|| t('action.notSet')}}</button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li v-for="(url, index) in urls" :key="index">
                <div class="dropdown-item" @click="updateUrl(url)">{{ url.url }}&nbsp;<small class="text-muted">{{ url.name }}</small></div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </template>
    <template v-else>
      <div class="row">
        <label class="col-sm-3 col-form-label text-end">{{ t('action.redirectPage') }}</label>
        <div class="col-sm-9">
          <button class="btn btn-light btn-sm" type="button" @click="pagePickDialogVisible=true">{{myModelValue.popupPageTitle|| t('action.notSet')}}</button>
          <template v-if="pageDatas.length>0">
            <div class="text-muted">{{t('action.pageInputDesc')}}</div>
          </template>
          <ModelItemInput v-for="(item, index) in pageDatas" @updateBoundInput="updateBoundInput"
                          :bound-input="myModelValue.input" :variables="variables"
                          :key="index" :intent="0" :model="item" :index="0">
          </ModelItemInput>
        </div>
      </div>
    </template>
  </template>

  <lay-layer v-model="pagePickDialogVisible" :title="t('common.page')" :shade="true" :area="['500px', '500px']" :btn="pagePickButtons">
    <div class="p-3">
      <PagePicker :defualt-page-uuid="pickedPageInfo.popupPageId" @update="pickedPage"></PagePicker>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import ydhl from '@/lib/ydhl'
import PagePicker from '@/components/common/PagePicker.vue'
import ModelItemInput from '@/components/common/ModelItemInput.vue'

export default {
  name: 'RedirectSetting',
  components: { ModelItemInput, PagePicker },
  props: {
    modelValue: Object,
    variables: Array
  },
  emits: ['update:modelValue'],
  setup (props: any, context: any) {
    const store = useStore()
    const { t } = useI18n()
    const pagePickDialogVisible = ref(false)
    const urls = ref([])
    const pageDatas = ref<any>([])
    const project = computed(() => store.state.design.project)
    const myModelValue = ref(props.modelValue)
    const pickedPageInfo = ref<any>({ // 用于缓存pickPage中的数据
      popupPageId: myModelValue.value.popupPageId,
      popupPageTitle: myModelValue.value.popupPageTitle
    })

    const updateUrl = (url) => {
      myModelValue.value.redirect = url.url
      myModelValue.value.url_type = 'url'
      update()
    }
    const pickedPage = (pageTitle, pageUuid) => {
      pickedPageInfo.value.popupPageId = pageUuid
      pickedPageInfo.value.popupPageTitle = pageTitle
      myModelValue.value.url_type = 'page'
    }
    onMounted(() => {
      if (project.value.rewrite) {
        ydhl.get('api/url.json', { project_uuid: project.value.id }, (rst: any) => {
          urls.value = rst.data || []
        })
      }
      loadPageData()
    })
    const loadPageData = () => {
      if (myModelValue.value.popupPageId) {
        ydhl.get('api/bind/data.json?data_from=path,query&page_uuid=' + myModelValue.value.popupPageId, [], (rst: any) => {
          pageDatas.value = rst.data.query || []
          if (rst.data.path) pageDatas.value.push(...rst.data.path)
        }, 'json')
      }
    }
    const pagePickButtons = ref([
      {
        text: t('common.ok'),
        callback: () => {
          pagePickDialogVisible.value = false
          myModelValue.value.popupPageId = pickedPageInfo.value.popupPageId
          myModelValue.value.popupPageTitle = pickedPageInfo.value.popupPageTitle
          update()
          loadPageData()
        }
      }
    ])

    const update = () => {
      context.emit('update:modelValue', myModelValue.value)
    }
    const updateBoundInput = (uuid, v) => {
      if (!myModelValue.value.input) {
        myModelValue.value.input = {}
      }
      myModelValue.value.input[uuid] = v
    }
    return {
      project,
      pageDatas,
      pagePickDialogVisible,
      myModelValue,
      t,
      urls,
      pagePickButtons,
      pickedPageInfo,
      pickedPage,
      updateUrl,
      updateBoundInput
    }
  }
}
</script>
