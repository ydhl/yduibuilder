<template>
  <div class="dropdown">
    <button class="btn btn-primary d-flex align-items-center text-truncate justify-content-center"
         data-bs-toggle="dropdown" ref="dropdownToggle" :style="style">{{!modelValue.id ? t('common.upload') : modelValue.name}}</button>
    <ul class="dropdown-menu dropdown-menu-end">
      <li><div class="dropdown-item" id="upload" ref="uploadBtn">{{t('common.upload')}}</div></li>
      <li><div class="dropdown-item" id="select" @click="openSelectFileDialog">{{t('common.selectFile')}}</div></li>
    </ul>
  </div>
  <lay-layer v-model="isFileSelectorOpen" teleport="body" :shade="true" :area="['520px', '500px']" :btn="buttons">
    <div class="p-2">
      <div>
        <div class="input-group">
          <input type="text" name="q" v-model="fileSearchWord" autocomplete="off" class="form-control" :placeholder="t('common.search') + '...'">
          <button type="submit" class="btn btn-primary" @click="searchFile">{{t('common.search')}}</button>
        </div>
      </div>
      <div v-if="files.length===0" class="text-muted text-center p-5"><i class="iconfont icon-empty fs-1"></i></div>
      <div class="d-flex mt-2 flex-wrap align-items-start" v-if="type==='image'">
        <div @click="select($event, file)" :key="index" v-for="(file, index) in files"
             :class="{'d-flex flex-column btn btn-light cursor align-items-center align-content-center m-1 file': true, 'text-primary border-primary': file.id == modelValue.id}">
          <div class="file-preview" :style="`${file.url ? 'background-image: url(' + file.url + ')' : ''}`"></div>
          <small class="text-truncate" style="width: 4rem;">{{file.name}}</small>
        </div>
      </div>
      <div class="mt-2" v-if="type!=='image'">
        <table class="table table-sm">
          <tr :key="index" v-for="(file, index) in files">
            <td @click="select($event, file)" :class="['file cursor', {'bg-light text-primary': file.id == selectFile.id}]">{{file.name}}</td>
            <td @click="select($event, file)" style="text-align: right" :class="['file cursor text-muted', {'bg-light text-primary': file.id == selectFile.id}]">{{file.upload_date}} {{file.file_size}} Byte</td>
          </tr>
        </table>
      </div>
      <div v-if="hasMore" class="d-flex align-items-center justify-content-center p-1 pointer text-" @click="searchFile(page + 1)">{{t('common.loadMore')}}</div>
    </div>
  </lay-layer>
</template>
<style scoped>
.file-preview{
  width: 4rem;height: 4rem;
  background-size:contain;background-repeat: no-repeat;background-position: center;font-size:11px;
}
</style>
<script lang="ts">
import { computed, nextTick, onMounted, ref } from 'vue'
import ydhl from '@/lib/ydhl'
import { useI18n } from 'vue-i18n'
import Uploader, { MimeType } from '@/lib/ydhl_uploader'
import { YDJSStatic } from '@/lib/ydjs'
declare const YDJS: YDJSStatic

export default {
  name: 'Upload',
  emits: ['update:modelValue'],
  props: {
    width: String,
    projectId: String,
    type: {
      type: String,
      default: 'image'
    },
    height: String,
    /**
     * {id, url, name}
     */
    modelValue: Object
  },
  setup (props: any, context: any) {
    // const store = useStore()
    const uploadBtn = ref()
    const { t } = useI18n()
    const dropdownToggle = ref()
    const fileSearchWord = ref('')
    const page = ref(1)
    const isFileSelectorOpen = ref(false)
    const hasMore = ref(false)

    const files = ref<Array<Record<string, string>>>([])
    const selectFile = ref(props.modelValue)

    const style = computed(() => {
      let str = `width:${props.width};height:${props.height}`
      if (props.modelValue && props.type === 'image' && props.modelValue.url) {
        str += `;background-image:url(${props.modelValue.url});background-size: cover;background-repeat: no-repeat;background-position: center;`
      }
      return str
    })

    let loadingId = ''
    const imageAdded = (file) => {
      loadingId = YDJS.loading(t('common.pleaseWait'))
    }
    const imageUploaded = (file: File, rst: any) => {
      YDJS.hide_dialog(loadingId)
      if (!rst || !rst.success) {
        ydhl.alert(rst?.msg || 'upload error')
        return
      }
      // console.log(response)
      context.emit('update:modelValue', rst.data)
    }
    const imageProgress = (file: File, progress: number) => {
      YDJS.update_loading(loadingId, t('common.pleaseWait') + ' ' + progress + '%')
    }
    onMounted(() => {
      const uploadApi = ydhl.api + 'api/' + props.projectId + '/upload.json'
      const mime: MimeType | undefined = props.type as MimeType
      nextTick(() => {
        if (uploadBtn.value) {
          Uploader(uploadBtn.value, mime, uploadApi, imageAdded, imageUploaded, imageProgress, (file, error) => {
            YDJS.hide_dialog(loadingId)
            ydhl.alert(error)
          })
        }
      })
    })
    const select = (event, file: any) => {
      selectFile.value = file
      // const parentEl = event.target.closest('.file')
      // $('.file').removeClass('text-primary border-primary')
      // $(parentEl).addClass('text-primary border-primary')
    }
    const searchFile = (p = 1) => {
      if (p === 1) files.value = []
      const loadingId = YDJS.loading(t('page.loading'))
      page.value = p
      ydhl.get(`api/${props.projectId}/file`, { type: props.type, q: fileSearchWord.value, page: p }, (rst) => {
        YDJS.hide_dialog(loadingId)
        if (!rst || !rst.success) {
          return
        }
        files.value?.push(...rst.data.list)
        hasMore.value = files.value?.length < rst.data.total
      }, 'json')
    }
    const openSelectFileDialog = () => {
      isFileSelectorOpen.value = true
      searchFile()
    }
    const hideFileDialog = () => {
      isFileSelectorOpen.value = false
    }

    const buttons = ref([
      {
        text: t('common.ok'),
        callback: () => {
          hideFileDialog()
          context.emit('update:modelValue', selectFile.value)
        }
      },
      {
        text: t('common.cancel'),
        callback: () => {
          hideFileDialog()
        }
      }
    ])
    return {
      style,
      t,
      uploadBtn,
      dropdownToggle,
      isFileSelectorOpen,
      fileSearchWord,
      hideFileDialog,
      openSelectFileDialog,
      imageUploaded,
      imageAdded,
      hasMore,
      searchFile,
      page,
      selectFile,
      files,
      buttons,
      imgSite: ydhl.api,
      select
    }
  }
}
</script>
