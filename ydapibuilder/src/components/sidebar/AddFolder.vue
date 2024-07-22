<template>
  <lay-layer :title="folder.id ? t('api.editFolder') : t('api.addFolder')" v-model="showAddFolder" :shade="true" :btn="buttons">
    <div class="p-3">
      <div class="mb-3">
        <label>{{t("api.folderParent")}}</label>
        <lay-tree-select v-model="folder.parent" allow-clear :data="folderSelector"></lay-tree-select>
      </div>
      <div class="mb-3">
        <label>{{t("api.folderName")}}</label>
        <input type="text" class="form-control" v-model="folder.title">
      </div>
      <div>
        <label>{{t("common.desc")}}</label>
        <textarea type="text" class="form-control" v-model="folder.comment"></textarea>
      </div>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { onMounted, ref, watch } from 'vue'
import ydhl from '@/lib/ydhl'

export default {
  name: 'AddFolder',
  props: {
    modelValue: Object,
    projectId: String
  },
  emits: ['close'],
  setup (props: any, context: any) {
    const { t } = useI18n()
    const showAddFolder = ref(true)
    const folder = ref(props.modelValue)
    const folderSelector = ref([])
    watch(showAddFolder, (v) => {
      if (!v) context.emit('close', null)
    })

    const loadFolder = () => {
      ydhl.get('/api/folder.json?projectId=' + props.projectId).then((rst: any) => {
        folderSelector.value = rst || []
      })
    }
    onMounted(() => {
      loadFolder()
    })
    const buttons = ref([
      {
        text: folder.value.id ? t('common.save') : t('common.addFolder'),
        callback: () => {
          ydhl.post('/api/folder/add.json',
            { project_uuid: props.projectId, name: folder.value.name || '', comment: folder.value.comment || '', uuid: folder.value.id || '', parent: folder.value.parent || '' },
            [])
            .then((rst: any) => {
              context.emit('update:modelValue', rst)
              context.emit('close', rst)
            })
        }
      },
      {
        text: t('common.cancel'),
        callback: () => {
          context.emit('close', null)
        }
      }
    ])
    return {
      t,
      folder,
      folderSelector,
      showAddFolder,
      buttons
    }
  }
}
</script>
