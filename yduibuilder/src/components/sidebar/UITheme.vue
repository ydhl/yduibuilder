<template>
  <div class="p-3">
    <h3 class="fs-6 text-muted d-flex align-items-center">
      {{ t('theme.colorCustomize') }}
      <label class="ms-2 fs-7 flex-grow-1 d-flex align-items-center">
        <template v-if="canCustomStyle"><input type="checkbox" v-model="supportDarkMode">&nbsp;{{ t('theme.supportDarkMode') }}</template>
      </label>
      <i class="iconfont icon-plus pointer hover-primary" @click="addColor"></i>
    </h3>
    <table style="width: 100%" class="color-variable" v-if="customColors.length>0 || canCustomStyle">
      <tr>
        <th></th>
        <th><template v-if="supportDarkMode">{{ t('theme.lightMode') }}</template></th>
        <th v-if="supportDarkMode">{{ t('theme.darkMode') }}</th>
        <th></th>
      </tr>
      <template v-if="canCustomStyle">
      <tr>
        <td>Primary</td>
        <td>
          <ColorPicker :hide-remove="true" v-model="primaryColor"></ColorPicker>
        </td>
        <td v-if="supportDarkMode">
          <ColorPicker :hide-remove="true" v-model="primaryDarkColor"></ColorPicker>
        </td>
      </tr>
      <tr>
        <td>Secondary</td>
        <td>
          <ColorPicker :hide-remove="true" v-model="secondaryColor"></ColorPicker>
        </td>
        <td v-if="supportDarkMode">
          <ColorPicker :hide-remove="true" v-model="secondaryDarkColor"></ColorPicker>
        </td>
      </tr>
      <tr>
        <td>Success</td>
        <td><ColorPicker :hide-remove="true" v-model="successColor"></ColorPicker></td>
        <td v-if="supportDarkMode">
          <ColorPicker :hide-remove="true" v-model="successDarkColor"></ColorPicker>
        </td>
      </tr>
      <tr>
        <td>Danger</td>
        <td>
          <ColorPicker :hide-remove="true" v-model="dangerColor"></ColorPicker>
        </td>
        <td v-if="supportDarkMode">
          <ColorPicker :hide-remove="true" v-model="dangerDarkColor"></ColorPicker>
        </td>
      </tr>
      <tr>
        <td>Warning</td>
        <td>
          <ColorPicker :hide-remove="true" v-model="warningColor"></ColorPicker>
        </td>
        <td v-if="supportDarkMode">
          <ColorPicker :hide-remove="true" v-model="warningDarkColor"></ColorPicker>
        </td>
      </tr>
      <tr>
        <td>Info</td>
        <td>
          <ColorPicker :hide-remove="true" v-model="infoColor"></ColorPicker>
        </td>
        <td v-if="supportDarkMode">
          <ColorPicker :hide-remove="true" v-model="infoDarkColor"></ColorPicker>
        </td>
      </tr>
      <tr>
        <td>Light</td>
        <td>
          <ColorPicker :hide-remove="true" v-model="lightColor"></ColorPicker>
        </td>
        <td v-if="supportDarkMode">
          <ColorPicker :hide-remove="true" v-model="lightDarkColor"></ColorPicker>
        </td>
      </tr>
      <tr>
        <td>Dark</td>
        <td>
          <ColorPicker :hide-remove="true" v-model="darkColor"></ColorPicker>
        </td>
        <td v-if="supportDarkMode">
          <ColorPicker :hide-remove="true" v-model="darkDarkColor"></ColorPicker>
        </td>
      </tr>
      <tr>
        <td>{{ t('style.background.background') }}</td>
        <td>
          <ColorPicker :hide-remove="true" v-model="bgColor"></ColorPicker>
        </td>
        <td v-if="supportDarkMode">
          <ColorPicker :hide-remove="true" v-model="bgDarkColor"></ColorPicker>
        </td>
      </tr>
      <tr>
        <td>{{ t('style.background.foreground') }}</td>
        <td>
          <ColorPicker :hide-remove="true" v-model="fgColor"></ColorPicker>
        </td>
        <td v-if="supportDarkMode">
          <ColorPicker :hide-remove="true" v-model="fgDarkColor"></ColorPicker>
        </td>
      </tr>
    </template>
      <tr v-for="(customColor, index) in customColors" :key="index">
        <td class="pe-2"><input type="text" v-model.trim="customColor.name" :placeholder="t('theme.colorName')" style="width: 80px" class="form-control form-control-sm"></td>
        <td><ColorPicker :hide-remove="true" v-model="customColor.color"></ColorPicker></td>
        <td v-if="supportDarkMode"><ColorPicker :hide-remove="true" v-model="customColor.dark"></ColorPicker></td>
        <td><ConfirmRemove @remove="removeCustomColor(index)" icon="icon-remove"></ConfirmRemove></td>
      </tr>
    </table>

    <h3 class="fs-6 mt-4 text-muted justify-content-between d-flex">
      {{ t('theme.fontCustomize') }}
      <i class="iconfont icon-plus pointer hover-primary"></i>
    </h3>
    <table style="width: 100%">
      <tr v-for="(fontFace, index) in fontFaces" :key="index">
        <td><input type="text" v-model="fontFace.name" :placeholder="t('theme.fontName')" class="form-control form-control-sm"></td>
        <td>
          <div class="dropdown">
            <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">{{t('common.upload')}}</button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
              <li><a href="javascript:void(0)" class="dropdown-item" id="uploadwoff2">{{ fontFace.file?.woff2 ? t('common.uploaded') : t('common.upload') }} woff2</a></li>
              <li><a href="javascript:void(0)" class="dropdown-item" id="uploadwoff">{{ fontFace.file?.woff ? t('common.uploaded') : t('common.upload') }} woff</a></li>
              <li><a href="javascript:void(0)" class="dropdown-item" id="uploadttf">{{ fontFace.file?.ttf ? t('common.uploaded') : t('common.upload') }} ttf</a></li>
            </ul>
          </div>
        </td>
        <td>
          <ConfirmRemove v-if="fontFace.file" @remove="removeFontFace(index)" icon="icon-remove"></ConfirmRemove>
        </td>
      </tr>
    </table>

    <template v-if="canCustomStyle">
      <h3 class="fs-6 mt-4 text-muted">{{ t('theme.fontDefaultSize') }}</h3>
      <div class="input-group input-group-sm">
        <input type="number" v-model="defaultFontSize" class="form-control form-control-sm">
        <div class="input-group-text">rem</div>
      </div>
    </template>

    <template v-if="canCustomStyle">
      <h3 class="fs-6 mt-4 text-muted d-flex align-items-center">{{ t('theme.spaceCustomize') }}
      </h3>
      <div class="input-group input-group-sm">
        <input type="number" v-model="defaultSpacer" class="form-control form-control-sm">
        <div class="input-group-text">rem</div>
      </div>
      <div class="d-flex flex-wrap fs-7">
        <div class="m-1"><div class="d-flex spacer-preview"><div class="spacer-block" :style="`margin-left:${defaultSpacer*0.25}rem`">0.25 × </div></div></div>
        <div class="m-1"><div class="d-flex spacer-preview"><div class="spacer-block" :style="`margin-left:${defaultSpacer*0.5}rem`">0.5 × </div></div></div>
        <div class="m-1"><div class="d-flex spacer-preview"><div class="spacer-block" :style="`margin-left:${defaultSpacer*1}rem`">1 ×</div></div></div>
        <div class="m-1"><div class="d-flex spacer-preview"><div class="spacer-block" :style="`margin-left:${defaultSpacer*1.5}rem`">1.5 ×</div></div></div>
        <div class="m-1"><div class="d-flex spacer-preview"><div class="spacer-block" :style="`margin-left:${defaultSpacer*3}rem`">3 ×</div></div></div>
      </div>
    </template>
    <button class="btn btn-primary mt-4" id="custom-theme-save" type="button" @click="save">{{t('common.save')}}</button>
  </div>
</template>

<script lang="ts" setup>
import { useI18n } from 'vue-i18n'
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useStore } from 'vuex'
import ydhl from '@/lib/ydhl'
import { YDJSStatic } from '@/lib/ydjs'
import Uploader from '@/lib/ydhl_uploader'
import ConfirmRemove from '@/components/common/ConfirmRemove.vue'
import $ from 'jquery'
import ColorPicker from '@/components/common/ColorPicker.vue'
declare const YDJS: YDJSStatic

const emit = defineEmits(['save'])
const { t } = useI18n()
const store = useStore()
const project = computed(() => store.state.design.project)
const canCustomStyle = computed(() => project.value?.canCustomStyle)

const primaryColor = ref(project.value.color?.primary || store.getters.translate('themeColor', 'primary'))
const secondaryColor = ref(project.value.color?.secondary || store.getters.translate('themeColor', 'secondary'))
const successColor = ref(project.value.color?.success || store.getters.translate('themeColor', 'success'))
const dangerColor = ref(project.value.color?.danger || store.getters.translate('themeColor', 'danger'))
const warningColor = ref(project.value.color?.warning || store.getters.translate('themeColor', 'warning'))
const lightColor = ref(project.value.color?.light || store.getters.translate('themeColor', 'light'))
const darkColor = ref(project.value.color?.dark || store.getters.translate('themeColor', 'dark'))
const infoColor = ref(project.value.color?.info || store.getters.translate('themeColor', 'info'))
const bgColor = ref(project.value.color?.bg || '#ffffff')
const fgColor = ref(project.value.color?.fg || '#000000')

const primaryDarkColor = ref(project.value.colorDark?.primary || store.getters.translate('themeColor', 'primary'))
const secondaryDarkColor = ref(project.value.colorDark?.secondary || store.getters.translate('themeColor', 'secondary'))
const successDarkColor = ref(project.value.colorDark?.success || store.getters.translate('themeColor', 'success'))
const dangerDarkColor = ref(project.value.colorDark?.danger || store.getters.translate('themeColor', 'danger'))
const warningDarkColor = ref(project.value.colorDark?.warning || store.getters.translate('themeColor', 'warning'))
const lightDarkColor = ref(project.value.colorDark?.light || store.getters.translate('themeColor', 'light'))
const darkDarkColor = ref(project.value.colorDark?.dark || store.getters.translate('themeColor', 'dark'))
const infoDarkColor = ref(project.value.colorDark?.info || store.getters.translate('themeColor', 'info'))
const bgDarkColor = ref(project.value.colorDark?.bg || '#ffffff')
const fgDarkColor = ref(project.value.colorDark?.fg || '#000000')
const customColors = ref<Array<any>>(project.value.customColors || [])

const supportDarkMode = ref(project.value.supportDarkMode === '1' || false)
const defaultFontSize = ref(project.value.defaultFontSize || store.getters.translate('fontSize', 'default'))
const defaultSpacer = ref(project.value.defaultSpacer || store.getters.translate('spacer', 'default'))
const fontFaces = ref(project.value.fontFace || [{ name: '' }])

watch(primaryColor, (v) => {
  store.commit('updatePageState', { themeColorPreview: { primaryColor: primaryColor.value } })
})

let loadingId = ''
const fileAdded = (file) => {
  loadingId = YDJS.loading(t('common.pleaseWait'))
}
const fileUploaded = (file, rst: any) => {
  YDJS.hide_dialog(loadingId)
  // console.log(response)
  if (!rst || !rst.success) {
    YDJS.alert(rst?.msg || 'Oops, Upload failed', 'Oops')
    return
  }
  if (!fontFaces.value[0].file) fontFaces.value[0].file = {} // 目前仅支持一个字体上传
  fontFaces.value[0].file[rst.data.ext] = rst.data.id
}
const fileUploadError = (file, error) => {
  YDJS.hide_dialog(loadingId)
  ydhl.alert(error)
}
const fileProgress = (file: File, progress: number) => {
  YDJS.update_loading(loadingId, t('common.pleaseWait') + ' ' + progress + '%')
}
const removeFontFace = (index: number) => {
  // fontFaces.value.splice(index, 1)
  fontFaces.value = [{ name: '' }]// 目前仅支持一个字体上传
}
onMounted(() => {
  nextTick(() => {
    Uploader(document.getElementById('uploadwoff2'), 'woff2', ydhl.api + 'api/' + project.value.id + '/upload.json', fileAdded, fileUploaded, fileProgress, fileUploadError)
    Uploader(document.getElementById('uploadwoff'), 'woff', ydhl.api + 'api/' + project.value.id + '/upload.json', fileAdded, fileUploaded, fileProgress, fileUploadError)
    Uploader(document.getElementById('uploadttf'), 'ttf', ydhl.api + 'api/' + project.value.id + '/upload.json', fileAdded, fileUploaded, fileProgress, fileUploadError)
  })
})
const refreshCustomColor = (v) => {
  const node = document.getElementById('custom-color')
  if (node) {
    node.remove()
  }
  const style = [':root{']
  for (const customColor of v) {
    style.push(`--${customColor.uuid}: ${customColor.color};`)
    if (customColor.dark) {
      style.push(`--${customColor.uuid}-dark: ${customColor.dark};`)
    }
  }
  style.push('}')
  $(window.document.head).append(`<style id="custom-color">${style.join('\r\n')}</style>`)
}
const save = () => {
  const data = {
    project_uuid: project.value.id,
    'color[primary]': primaryColor.value,
    'color[secondary]': secondaryColor.value,
    'color[success]': successColor.value,
    'color[danger]': dangerColor.value,
    'color[warning]': warningColor.value,
    'color[info]': infoColor.value,
    'color[light]': lightColor.value,
    'color[dark]': darkColor.value,
    'color[bg]': bgColor.value,
    'color[fg]': fgColor.value,
    'colorDark[primary]': primaryDarkColor.value,
    'colorDark[secondary]': secondaryDarkColor.value,
    'colorDark[success]': successDarkColor.value,
    'colorDark[danger]': dangerDarkColor.value,
    'colorDark[warning]': warningDarkColor.value,
    'colorDark[info]': infoDarkColor.value,
    'colorDark[light]': lightDarkColor.value,
    'colorDark[dark]': darkDarkColor.value,
    'colorDark[bg]': bgDarkColor.value,
    'colorDark[fg]': fgDarkColor.value,
    supportDarkMode: supportDarkMode.value ? 1 : 0,
    defaultFontSize: defaultFontSize.value,
    defaultSpacer: defaultSpacer.value
  }
  for (const index in customColors.value) {
    if (!customColors.value[index].name) {
      ydhl.alert(t('theme.colorNameIsEmpty'))
      return
    }
    if (!customColors.value[index].color) {
      ydhl.alert(t('theme.colorIsEmpty'))
      return
    }
    data[`customColors[${index}]`] = JSON.stringify(customColors.value[index])
  }
  refreshCustomColor(customColors.value)
  store.commit('updateProjectState', { name: 'customColors', value: customColors.value, save: false })
  for (const index in fontFaces.value) {
    if (!fontFaces.value[index].name) continue
    data[`fontFace[${index}][name]`] = fontFaces.value[index].name
    if (!fontFaces.value[index]?.file?.woff2 && !fontFaces.value[index]?.file?.woff && !fontFaces.value[index]?.file?.ttf) {
      YDJS.alert('Please upload font file', 'Oops')
      return
    }
    data[`fontFace[${index}][file][woff2]`] = fontFaces.value[index]?.file?.woff2 || ''
    data[`fontFace[${index}][file][woff]`] = fontFaces.value[index]?.file?.woff || ''
    data[`fontFace[${index}][file][ttf]`] = fontFaces.value[index]?.file?.ttf || ''
  }
  ydhl.post('api/theme.json', data, [], (rst) => {
    if (!rst || !rst.success) {
      YDJS.alert(rst?.msg || 'Save Fail', 'Oops')
      return
    }
    emit('save')
  })
}
const addColor = () => {
  customColors.value.push({
    name: '',
    color: '',
    uuid: ydhl.uuid(10, 16, 'cc'),
    dark: ''
  })
}
const removeCustomColor = (index) => {
  customColors.value.splice(index, 1)
}
</script>
<style scoped lang="scss">
.color-variable{
  font-size: 14px !important;
  td,th{
    padding-top: 2px;
    padding-bottom: 2px;
  }
}
.color-input{
  font-size: 0.175rem;
  height: 31px;
  border-width: 0px;
  padding: 0px;
}
.spacer-preview{
  background-color: #62696e; color:#fff;padding: 5px 0px;
}
.spacer-block{
  background-color: #fff;
  color: #444;
}
</style>
