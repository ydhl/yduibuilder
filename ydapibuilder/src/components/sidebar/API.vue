<template>
  <div class="ps-2 mt-2">
    <button type="button" style="text-align: left" class="btn w-100 btn-sm btn-white"><i class="iconfont icon-graphql"></i>
      {{t('common.graphql')}}</button>
    <div  @click="apiIsOpen = !apiIsOpen" class="justify-content-between d-flex btn btn-sm btn-white w-100 align-items-center">
      <div><i class="iconfont icon-api"></i> {{t('common.apiManage')}}<i :class="{'iconfont': true, 'icon-tree-open': apiIsOpen, 'icon-tree-close': !apiIsOpen}"></i></div>
      <div class="d-flex gap-2 align-items-center">
        <div class="pointer" @click.stop="folder={},showAddFolder=true"><i class="iconfont hover-primary icon-plus"></i></div>
        <div class="pointer" v-if="!openState" @click.stop="expandAll"><i class="iconfont hover-primary icon-expandall"></i></div>
        <div class="pointer" v-if="openState" @click.stop="collapseAll"><i class="iconfont hover-primary icon-collapseall"></i></div>
      </div>
    </div>
    <ul class="tree" v-if="apiIsOpen">
      <FolderTree :tree="tree" v-for="(tree, index) in folders" :open="openState" :indent="1" :key="index">
      <template #leaf="{data, hover}">
        <div class="dropdown pe-1" @click.stop>
          <div type="button" class="pointer" data-bs-toggle="dropdown" aria-expanded="false"><i :class="{'iconfont icon-more': true, 'invisible': !hover}"></i></div>
          <ul class="dropdown-menu ">
            <li><a href="#" class="dropdown-item" @click="editApi(data)"><i class="iconfont icon-edit"></i> {{t('common.edit')}}</a></li>
            <li><a href="#" class="dropdown-item" @click="copyApi(data)"><i class="iconfont icon-copy"></i> {{t('common.copy')}}</a></li>
            <li><a href="#" class="dropdown-item text-danger" @click="removeApi(data)"><i class="iconfont icon-remove"></i>{{ t('common.delete') }}</a></li>
          </ul>
        </div>
      </template>
      <template #trunk="{data, hover}">
        <div class="btn-group btn-group-sm pe-1">
          <div class="btn-group" @click.stop>
            <div type="button" class="pointer" data-bs-toggle="dropdown" aria-expanded="false"><i :class="{'iconfont icon-more': true, 'invisible': !hover}"></i></div>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a href="#" class="dropdown-item" @click="addApi"><i class="iconfont icon-plus"></i> {{t('common.addAPI')}}</a></li>
              <li><a href="#" class="dropdown-item" @click="addSubFolder(data)"><i class="iconfont icon-plus"></i> {{t('common.addFolder')}}</a></li>
              <li><a href="#" class="dropdown-item" @click="editFolder(data)"><i class="iconfont icon-edit"></i> {{t('common.edit')}}</a></li>
              <li><a href="#" class="dropdown-item text-danger" @click="removeFolder(data)"><i class="iconfont icon-remove"></i>{{ t('common.delete') }}</a></li>
            </ul>
          </div>
        </div>
      </template>
  </FolderTree>
    </ul>
    <div v-if="folders.length===0" class="text-center p-5 m-5">
      <i class="iconfont icon-wuneirong fs-1"></i>
    </div>
  </div>
  <AddFolder v-model="folder" @close="addFolderClose" v-if="showAddFolder" :projectId="project.id"></AddFolder>
</template>

<script lang="ts">
import FolderTree from '@/components/sidebar/FolderTree.vue'
import { useI18n } from 'vue-i18n'
import { computed, onMounted, ref } from 'vue'
import { APIFolder } from '@/store/model'
import ydhl from '@/lib/ydhl'
import { useStore } from 'vuex'
import { layer } from '@layui/layui-vue'
import AddFolder from '@/components/sidebar/AddFolder.vue'
export default {
  name: 'API',
  components: { AddFolder, FolderTree },
  setup (props: any, context: any) {
    const openState = ref(true)
    const apiIsOpen = ref(true)
    const showAddFolder = ref(false)
    const store = useStore()
    const project = computed(() => store.state.design.project)
    const openTabs = computed(() => store.state.design.tabs)
    const folder = ref<any>({})
    const folders = ref<Array<APIFolder>>([])

    const collapseAll = () => {
      openState.value = false
    }
    const expandAll = () => {
      openState.value = true
    }
    const { t } = useI18n()
    const loadFolder = () => {
      ydhl.get('/api/folder/all.json?projectId=' + project.value.id).then((rst: any) => {
        folders.value = rst || []
      })
    }
    const editApi = (edit) => {
      store.commit('putTab', { page: 'APIAdd', name: 'common.loading', query: { uuid: edit.id, action: 'edit' } })
    }
    const copyApi = (edit) => {
      store.commit('putTab', { page: 'APIAdd', name: 'common.loading', query: { uuid: edit.id, action: 'copy' } })
    }
    const removeApi = (edit) => {
      ydhl.confirm(t('api.removeApiConfirm'), t('common.delete'), t('common.cancel')).then((id) => {
        layer.close(id)
        ydhl.post('/api/api/delete.json', { uuid: edit.id }, [], true).then((rst: any) => {
          // 删除已经打开的标签
          const index = openTabs.value.findIndex((item) => {
            return item.query?.uuid === edit.id
          })
          store.commit('removeTab', index)
          loadFolder()
        })
      })
    }

    const editFolder = (edit) => {
      folder.value = JSON.parse(JSON.stringify(edit))
      showAddFolder.value = true
    }
    const addSubFolder = (edit) => {
      folder.value.parent = edit.id
      showAddFolder.value = true
    }
    const removeFolder = (edit) => {
      ydhl.confirm(t('api.removeFolderConfirm'), t('common.delete'), t('common.cancel')).then((id) => {
        layer.close(id)
        ydhl.post('/api/folder/delete.json', { uuid: edit.id }, [], true).then((rst: any) => {
          loadFolder()
        })
      })
    }
    const addFolderClose = () => {
      loadFolder()
      showAddFolder.value = false
    }
    const addApi = () => {
      store.commit('putTab', { page: 'APIAdd', name: 'common.loading' })
    }
    onMounted(() => {
      loadFolder()
    })
    return {
      folders,
      openState,
      apiIsOpen,
      project,
      addFolderClose,
      showAddFolder,
      folder,
      t,
      removeApi,
      editApi,
      copyApi,
      addApi,
      addSubFolder,
      editFolder,
      removeFolder,
      collapseAll,
      expandAll
    }
  }
}
</script>
