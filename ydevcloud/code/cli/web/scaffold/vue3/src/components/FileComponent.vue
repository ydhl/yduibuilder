<template>
  <div v-attr="attrs" :class="css" :style="style" data-root :data-index="iterateIndex">
    <input type="file" class="d-block input" :multiple="multiple" :data-changed="changedTime"
    :accept="accept" v-attr="formAttrs" @change="fileChanged"
      @click="y.emit($el, 'y-click', $event)"
      @dblclick="y.emit($el, 'y-dblclick', $event)"
      @mousedown="y.emit($el, 'y-mousedown', $event)"
      @mouseup="y.emit($el, 'y-mouseup', $event)"
      @mouseover="y.emit($el, 'y-mouseover', $event)"
      @mouseout="y.emit($el, 'y-mouseout', $event)"
      @mousemove="y.emit($el, 'y-mousemove', $event)"
      @mouseenter="y.emit($el, 'y-mouseenter', $event)"
      @mouseleave="y.emit($el, 'y-mouseleave', $event)">
  </div>
</template>
<script lang="ts" setup>
import {computed, onMounted, ref} from 'vue'
import axios, {type AxiosProgressEvent} from 'axios'
import y from '@/lib/ydecloud'
const { iterateIndex, attrs, formAttrs, css, multiple, accept, uploadUrl, isAutoUpload, maxFileSize, style } = defineProps({
// 该组件被迭代时的索引
iterateIndex: Number,
// 父组件被迭代时的索引
parentIterateIndex: Number,
attrs: Object,
formAttrs: Object,
multiple: Boolean,
isAutoUpload: Boolean,
maxFileSize: String,
uploadUrl: String,
accept: String,
css: {
  type: [Object , String]
},
style: String
})
const model = defineModel()
const emit = defineEmits(['click','dblclick','mousedown','mouseup','mouseover','mouseout','mousemove','mouseenter','mouseleave','onFileChange', 'onBeforeUpload', 'onUploadProgress', 'onFileUploaded','onUploadComplete'])
const exts = computed<Array<string>>(() => accept && accept.split(',') || [])
let modelIsArray = false
const changedTime = ref('')// 仅仅用了却不触发v-input自定义指令

function convertSizeToBytes(sizeStr: string) {
  let size = parseInt(sizeStr) || 0
  const suffix = sizeStr.slice(-2).toLowerCase()

  switch (suffix) {
    case 'kb':
      size *= 1024;
      break;
    case 'mb':
      size *= 1024 * 1024;
      break;
    case 'gb':
      size *= 1024 * 1024 * 1024;
      break;
    case 'tb':
      size *= 1024 * 1024 * 1024 * 1024;
      break;
    default:
      break;
  }

  return size;
}

function fileChanged(e: Event){
  changedTime.value = Date.now().toString()
  const files = (e.target as HTMLInputElement)?.files
  model.value = modelIsArray ? files : files?.[0]
  emit('onFileChange', files)
  if (!files) return
  const promises:Array<Promise<boolean>> = []
  for(let index=0; index < files.length; index++){
    const file = files[index]
    if (accept && exts.value.length > 0) {
      const ext = file.name.replace(/^.+\./, '');
      if(exts.value.indexOf(ext) === -1) {
          alert(`"accept file: ${accept}"`);
          return;
      }
    }
    if (maxFileSize) {
      if(file.size > convertSizeToBytes(maxFileSize)) {
        alert(`${file.name} exceeds the size limit ${maxFileSize}`);
        return;
      }
    }

    if (isAutoUpload) {
      promises.push(new Promise((resolve) => {
        emit('onBeforeUpload', index, file)
        const formData = new FormData();
        if(file !== undefined){
            formData.append("file", file);
        }
        axios({
          data: formData,
          responseType: 'json',
          onUploadProgress: (progressEvent: AxiosProgressEvent) => {
            const progress = progressEvent.total && progressEvent.total > 0 ? ~~(progressEvent.loaded / progressEvent.total * 100) : 0;
            emit('onUploadProgress', index, file, progress)
          },
          method: "post",
          withCredentials: true,
          url: y.apiBase + uploadUrl
        }).then((response) => {
          const rst = response.data;
          emit('onFileUploaded',index, file, rst)
          resolve(rst)
        }).catch((err) =>{
          alert(err)
        })
      }))
    }
  }

  if(isAutoUpload) {
    Promise.all(promises).then(()=>{
      if (files?.length > 0){
        emit('onUploadComplete')
      }
    })
  }
}

onMounted(() => {
  modelIsArray = model.value==undefined || Array.isArray(model.value)
})
</script>