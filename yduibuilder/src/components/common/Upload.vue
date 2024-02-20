<template>
  <div class="dropdown">
    <button class="btn btn-primary d-flex align-items-center text-truncate justify-content-center"
         data-bs-toggle="dropdown" ref="dropdownToggle" :style="style">{{!modelValue.id ? t('common.upload') : modelValue.name}}</button>
    <ul class="dropdown-menu dropdown-menu-end">
      <li><a class="dropdown-item" id="upload" href="javascript:;" ref="uploadBtn">{{t('common.upload')}}</a></li>
      <li><a class="dropdown-item" id="select" href="javascript:;" @click="openSelectFileDialog">{{t('common.selectFile')}}</a></li>
    </ul>
  </div>
  <template v-if="isFileSelectorOpen" >
    <teleport to="body">
      <div id="openFileDialog" class="modal" tabindex="-1">
        <div class="modal-dialog modal-lg" style="max-width: 800px !important;margin: 1.75rem auto !important;">
          <div class="modal-content">
            <div class="modal-body" style="height: 500px">
              <div>
                <div class="input-group">
                  <input type="text" name="q" v-model="fileSearchWord" autocomplete="off" class="form-control" :placeholder="t('common.search') + '...'">
                  <button type="submit" class="btn btn-primary" @click="searchFile">{{t('common.search')}}</button>
                </div>
              </div>
              <div v-if="files.length===0" class="text-muted text-center p-5"><i class="iconfont icon-empty fs-1"></i></div>
              <div class="d-flex mt-2 flex-wrap" v-if="type==='image'">
                <div @click="select($event, file)" :key="index" v-for="(file, index) in files"
                     :class="{'d-flex flex-column btn btn-light cursor align-items-center align-content-center m-1 file': true, 'text-primary border-primary': file.id == modelValue.id}">
                  <div class="file-preview" :style="`${file.url ? 'background-image: url(' + file.url + ')' : ''}`"></div>
                  <small class="text-truncate" style="width: 4rem;">{{file.name}}</small>
                </div>
              </div>
              <div class="d-flex mt-2" v-if="type!=='image'">
                <table class="table table-striped table-hover table-bordered">
                  <tr @click="select($event, file)" :key="index" v-for="(file, index) in files">
                    <td :class="['file cursor', {'table-primary': file.id == modelValue.id}]">{{file.name}}</td>
                  </tr>
                </table>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light me-3" @click="hideFileDialog()">{{t('common.cancel')}}</button>
              <button type="button" class="btn btn-primary" @click="hideFileDialog()">{{t('common.ok')}}</button>
            </div>
          </div>
        </div>
      </div>
    </teleport>
  </template>
</template>
<style scoped>
.file-preview{
  width: 4rem;height: 4rem;
  background-size:contain;background-repeat: no-repeat;background-position: top;font-size:11px;
}
</style>
<script lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import ydhl from '@/lib/ydhl'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import { YDJSStatic } from '@/lib/ydjs'
import $ from 'jquery'
declare const bootstrap: any
declare const YDJS: YDJSStatic
// eslint-disable-next-line camelcase
declare const yd_upload_render: Function

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
    const store = useStore()
    const uploadBtn = ref()
    const { t } = useI18n()
    const mimeType = {
      image: 'image/*',
      excel: 'xls,xlsx'
    }
    const dropdownToggle = ref()
    const fileSearchWord = ref('')
    const page = ref(1)
    const isFileSelectorOpen = ref(false)
    watch(isFileSelectorOpen, function (v) {
      store.commit('updateState', { backdropVisible: v })
    })
    const files = ref<Array<Record<string, string>>>()
    const selectFile = ref()

    const style = computed(() => {
      let str = `width:${props.width};height:${props.height}`
      if (props.modelValue && props.type === 'image' && props.modelValue.url) {
        str += `;background-image:url(${props.modelValue.url});background-size: cover;background-repeat: no-repeat;background-position: center;`
      }
      return str
    })

    let loadingId = ''
    const imageAdded = (up, files) => {
      up.setOption('url', ydhl.api + 'api/' + props.projectId + '/upload.json')
      loadingId = YDJS.loading(t('common.pleaseWait'))
    }
    const imageUploaded = (up: any, files: Array<any>, response) => {
      YDJS.hide_dialog(loadingId)
      if (!response || !response.success) return
      // console.log(response)
      context.emit('update:modelValue', response.data)
    }
    onMounted(() => {
      // const uploadApi = ydhl.api + 'api/' + props.projectId + '/upload.json'
      yd_upload_render(uploadBtn.value, '#', mimeType[props.type], imageAdded, imageUploaded)
    })
    const select = (event, file: any) => {
      selectFile.value = file
      const parentEl = $(event.target).has('.file').length ? event.target : $(event.target).parents('.file')
      $('.file').removeClass('text-primary border-primary')
      $(parentEl).addClass('text-primary border-primary')
    }
    const searchFile = (currPage: number = 0) => {
      if (currPage > 0) {
        page.value = currPage
      } else {
        page.value = 1
      }
      files.value = []
      ydhl.get(`api/${props.projectId}/file`, { type: props.type, q: fileSearchWord.value, page: page.value }, (rst) => {
        if (!rst || rst.success) {
          return
        }
        files.value = rst.data
      }, 'json')
    }
    const openSelectFileDialog = () => {
      isFileSelectorOpen.value = true
      files.value = []

      const loadingId = YDJS.loading(t('page.loading'))
      ydhl.get(`api/${props.projectId}/file`, { type: props.type, q: fileSearchWord.value }, (rst) => {
        YDJS.hide_dialog(loadingId)
        // console.log(rst)
        files.value = rst?.data || []
        const myModalEl = document.getElementById('openFileDialog') as HTMLElement
        const myModal = new bootstrap.Modal(myModalEl, { backdrop: 'static', keyboard: false })
        myModal.show()
      }, 'json')
    }
    const hideFileDialog = () => {
      // console.log(selectFile.value)
      if (selectFile.value) context.emit('update:modelValue', selectFile.value)
      $('#openFileDialog').remove()
      isFileSelectorOpen.value = false
    }
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
      searchFile,
      selectFile,
      files,
      imgSite: ydhl.api,
      select
    }
  }
}
</script>
