<template>
  <div class="mt-1 d-flex">
    <div class="d-flex flex-column align-items-center justify-content-center">
      <div class="btn-group" role="group" aria-label="Basic example">
        <button type="button" @click="backgroundImageType='image'" :class="{'btn btn-xs': true, 'btn-primary': backgroundImageType=='image', 'btn-light': backgroundImageType!='image'}">{{t('common.image')}}</button>
        <button type="button" @click="backgroundImageType='gradient'" :class="{'btn btn-xs': true, 'btn-primary': backgroundImageType=='gradient', 'btn-light': backgroundImageType!='gradient'}">{{t('style.background.gradient')}}</button>
      </div>
      <div class="text-center mt-1">
        <Upload v-if="backgroundImageType=='image'" v-model="localImage" :project-id="projectId" width="73px" height="73px"></Upload>
        <template v-if="backgroundImageType=='gradient'">
          <Gradient custom-style="width:73px;height:73px" v-model="gradient"></Gradient>
        </template>
        <div class="cursor text-danger" @click="removeImage"><i class="iconfont icon-remove"></i><small>{{t('style.background.deleteImage')}}</small></div>
      </div>
    </div>
    <div class="ms-1 flex-grow-1 align-items-center align-content-center">
      <div class="input-group input-group-sm mb-1">
        <span class="input-group-text" style="width: 60px">{{ t('style.background.repeat') }}</span>
        <select class="form-select form-select-sm" v-model="repeat">
          <option value="repeat">{{ t('style.background.repeat') }}</option>
          <option value="repeat-x">{{ t('style.background.repeatX') }}</option>
          <option value="repeat-y">{{ t('style.background.repeatY') }}</option>
          <option value="no-repeat">{{ t('style.background.noRepeat') }}</option>
        </select>
      </div>
      <div class="input-group input-group-sm mb-1">
        <span class="input-group-text" style="width: 60px">{{ t('style.background.clip') }}</span>
        <select class="form-select form-select-sm" v-model="clip">
          <option value="border-box">{{ t('style.background.borderbox') }}</option>
          <option value="padding-box">{{ t('style.background.paddingbox') }}</option>
          <option value="content-box">{{ t('style.background.cententbox') }}</option>
          <option value="text">{{ t('style.background.text') }}</option>
        </select>
      </div>
      <div class="input-group input-group-sm">
        <span class="input-group-text" style="width: 60px">{{ t('style.background.origin') }}</span>
        <select class="form-select form-select-sm" v-model="origin">
          <option value="border-box">{{ t('style.background.borderbox') }}</option>
          <option value="padding-box">{{ t('style.background.paddingbox') }}</option>
          <option value="content-box">{{ t('style.background.cententbox') }}</option>
        </select>
      </div>
    </div>
  </div>
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
      <div class="input-group input-group-sm">
        <span class="input-group-text text-truncate">{{ t('style.background.attachment') }}</span>
        <select class="form-select form-select-sm" v-model="attachment">
          <option value="scroll">{{ t('style.background.attachmentScroll') }}</option>
          <option value="fixed">{{ t('style.background.attachmentFixed') }}</option>
          <option value="local">{{ t('style.background.attachmentLocal') }}</option>
        </select>
      </div>
    </div>
  </div>
  <div class="mt-2 d-flex align-items-center align-content-center">
    <div class="input-group input-group-sm">
      <span class="input-group-text">{{ t('style.background.size') }}</span>
      <select class="form-select form-select-sm" v-model="size">
        <option value="cover">{{ t('style.background.sizeCover') }}</option>
        <option value="contain">{{ t('style.background.sizeContain') }}</option>
        <option value="length">{{ t('style.background.sizeLength') }}</option>
      </select>
      <template v-if="size=='length'">
        <span class="input-group-text">W</span>
        <input type="text" placeholder="auto" class="form-control form-control-sm" v-model="sizeW">
        <span class="input-group-text">H</span>
        <input type="text" placeholder="auto" class="form-control form-control-sm" v-model="sizeH">
      </template>
    </div>
  </div>
</template>

<script lang="ts">
import Upload from '../../common/Upload.vue'
import Position from '../../common/Position.vue'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import initUI from '../../Common'
import { useStore } from 'vuex'
import Gradient from '@/components/common/Gradient.vue'

export default {
  name: 'BackgroundImage',
  components: { Gradient, Position, Upload },
  props: {
    index: Number,
    previewMode: Boolean
  },
  setup (props: any, context: any) {
    const info = initUI()
    const store = useStore()
    const backgroundImageType = computed({
      get () {
        const type = arrayGet('background-image', 'style') || null
        return type?.type || 'image'
      },
      set (v: any) {
        let type = arrayGet('background-image', 'style') || null
        if (!type) type = { }
        type.type = v
        arraySet('background-image', type, 'custom')
      }
    })
    const projectId = computed(() => store.state.design.project.id)
    const localImage = computed({
      get () {
        const style = arrayGet('background-image', 'style') || null
        return { url: style?.url, id: style?.file?.id, name: style?.file?.name }
      },
      set (v: any) {
        if (!v) {
          arrayRemove('background-image', 'style')
          return
        }
        // console.log(v)
        const g = arrayGet('background-image', 'style') || {}
        g.type = 'image'
        g.url = v.url
        g.file = { id: v.id, name: v.name }
        arraySet('background-image', g, 'style')
      }
    })
    const gradient = computed({
      get () {
        const g = arrayGet('background-image', 'style') || null
        return g?.gradient || {}
      },
      set (v: any) {
        if (!v) {
          arrayRemove('background-image', 'style')
          return
        }
        const g = arrayGet('background-image', 'style') || {}
        g.type = 'gradient'
        g.gradient = v
        // console.log(v)
        arraySet('background-image', g, 'style')
      }
    })
    const arraySet = (name, v, section) => {
      const arr = JSON.parse(JSON.stringify(info.getMeta(name, section, props.previewMode) || []))
      arr[props.index] = v
      info.setMeta(name, arr, section, false, props.previewMode)
    }
    const arrayGet = (name, section) => {
      const style = info.getMeta(name, section, props.previewMode) || []
      if (style.length === 0) return ''
      return style[props.index]
    }
    const arrayRemove = (name, section) => {
      const arr = JSON.parse(JSON.stringify(info.getMeta(name, section, props.previewMode) || []))
      arr.splice(props.index, 1)
      info.setMeta(name, arr, section, false, props.previewMode)
    }
    const repeat = computed<string>({
      get () {
        return arrayGet('background-repeat', 'style')
      },
      set (v) {
        return arraySet('background-repeat', v, 'style')
      }
    })
    const size = computed({
      get () {
        const size = arrayGet('background-size', 'style')
        if (size !== 'contain' && size !== 'cover' && size) return 'length'
        return size
      },
      set (v) {
        arraySet('background-size', v === 'length' ? ' ' : v, 'style')
      }
    })
    const position = computed<Array<string>>({
      get () {
        const size = arrayGet('background-position', 'style') || ''
        return size.split(' ')
      },
      set (v) {
        // console.log(v)
        arraySet('background-position', v.join(' '), 'style')
      }
    })
    const positionX = computed<string>({
      get () {
        const size = arrayGet('background-position', 'style') || ''
        // console.log(size)
        return size.split(' ')[0] || ''
      },
      set (v) {
        const old = JSON.parse(JSON.stringify(info.getMeta('background-position', 'style', props.previewMode) || []))
        const arr = old[props.index].split(' ') || []
        arr[0] = v
        old[props.index] = arr.join(' ')
        info.setMeta('background-position', old, 'style', false, props.previewMode)
      }
    })
    const positionY = computed<string>({
      get () {
        const size = arrayGet('background-position', 'style') || ''
        return size.split(' ')[1] || ''
      },
      set (v) {
        const old = JSON.parse(JSON.stringify(info.getMeta('background-position', 'style', props.previewMode) || []))
        const arr = old[props.index].split(' ') || []
        arr[1] = v
        old[props.index] = arr.join(' ')
        info.setMeta('background-position', old, 'style', false, props.previewMode)
      }
    })
    const sizeW = computed<string>({
      get () {
        const _sizeValue = size.value
        if (_sizeValue !== 'length') return ''
        const _size = arrayGet('background-size', 'style')
        return _size.split(' ')[0] || ''
      },
      set (v) {
        const old = JSON.parse(JSON.stringify(info.getMeta('background-size', 'style', props.previewMode) || []))
        const arr = old[props.index].split(' ') || []
        arr[0] = v
        old[props.index] = arr.join(' ')
        info.setMeta('background-size', old, 'style', false, props.previewMode)
      }
    })
    const sizeH = computed<string>({
      get () {
        const _sizeValue = size.value
        if (_sizeValue !== 'length') return ''
        const _size = arrayGet('background-size', 'style')
        return _size.split(' ')[1] || ''
      },
      set (v) {
        const old = JSON.parse(JSON.stringify(info.getMeta('background-size', 'style', props.previewMode) || []))
        const arr = old[props.index].split(' ') || []
        arr[1] = v
        old[props.index] = arr.join(' ')
        info.setMeta('background-size', old, 'style', false, props.previewMode)
      }
    })
    const clip = computed<string>({
      get () {
        return arrayGet('background-clip', 'style')
      },
      set (v) {
        if (v === 'text') { // 文本剪裁需要把前景色设置为透明
          info.setMeta('color', '#00000000', 'style', false, props.previewMode)
        }
        arraySet('background-clip', v, 'style')
      }
    })

    const origin = computed<string>({
      get () {
        return arrayGet('background-origin', 'style')
      },
      set (v) {
        arraySet('background-origin', v, 'style')
      }
    })
    const attachment = computed<string>({
      get () {
        return arrayGet('background-attachment', 'style')
      },
      set (v) {
        arraySet('background-attachment', v, 'style')
      }
    })

    const removeImage = () => {
      arrayRemove('background-image', 'style')
      // 对应的其他Background属性也删除
      arrayRemove('background-position', 'style')
      arrayRemove('background-size', 'style')
      arrayRemove('background-repeat', 'style')
      arrayRemove('background-clip', 'style')
      arrayRemove('background-origin', 'style')
      arrayRemove('background-attachment', 'style')
    }

    const { t } = useI18n()
    return {
      backgroundImageType,
      t,
      removeImage,
      localImage,
      gradient,
      projectId,
      repeat,
      size,
      sizeW,
      sizeH,
      origin,
      clip,
      attachment,
      positionX,
      positionY,
      position
    }
  }
}
</script>
