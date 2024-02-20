<template>
  <div class="style-header">
    <i class="iconfont icon-tree-close"></i> {{t('style.text.font')}}
    <i class="iconfont icon-point text-danger" v-if="hasSet"></i>
    <i class="iconfont icon-point text-success" v-if="hasInherit"></i>
  </div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.text.lineHeight') }}</label>
      <div class="col-sm-9 d-flex align-items-center">
        <input type="text" class="form-control form-control-sm" :placeholder="t('style.layout.widthTip')" v-model="lineHeight">
        <div class="input-group input-group-sm ms-1 flex-nowrap">
          <span class="input-group-text">{{ t('style.text.weight') }}</span>
          <div class="dropdown">
          <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
            {{ bold }}
          </button>
          <div class="dropdown-menu">
            <a class="dropdown-item font-weight-bold" href="javascript:void(0)" @click="bold = 'normal'">normal</a>
            <a class="dropdown-item font-weight-bold" href="javascript:void(0)" @click="bold = '100'">100.</a>
            <a class="dropdown-item font-weight-bold" href="javascript:void(0)" @click="bold = '200'">200.</a>
            <a class="dropdown-item font-weight-bold" href="javascript:void(0)" @click="bold = '300'">300.</a>
            <a class="dropdown-item font-weight-bold" href="javascript:void(0)" @click="bold = '400'">400.</a>
            <a class="dropdown-item font-weight-bold" href="javascript:void(0)" @click="bold = '500'">500.</a>
            <a class="dropdown-item font-weight-bold" href="javascript:void(0)" @click="bold = '600'">600.</a>
            <a class="dropdown-item font-weight-bold" href="javascript:void(0)" @click="bold = '700'">700.</a>
            <a class="dropdown-item font-weight-bold" href="javascript:void(0)" @click="bold = '800'">800.</a>
            <a class="dropdown-item font-weight-bold" href="javascript:void(0)" @click="bold = '900'">900.</a>
            <a class="dropdown-item font-weight-bold" href="javascript:void(0)" @click="bold = 'bold'">bold</a>
            <a class="dropdown-item font-weight-bold" href="javascript:void(0)" @click="bold = 'bolder'">bolder</a>
            <a class="dropdown-item font-weight-bold" href="javascript:void(0)" @click="bold = 'lighter'">lighter</a>
          </div>
        </div>
        </div>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.text.textStroke') }}</label>
      <div class="col-sm-9 d-flex align-items-center">
        <div class="input-group input-group-sm flex-grow-1">
          <span class="input-group-text">{{ t('style.text.textStrokeLength') }}</span>
          <input type="text" v-model="textStrokeLength" placeholder="px" class="form-control form-control-sm p-1">
        </div>
        <div class="ms-1 flex-shrink-0">
          <ColorPicker css="w-100 border-0" v-model="textStrokeColor"></ColorPicker>
        </div>
      </div>
    </div>
    <div class="row mt-1">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.utilities.shadow') }}</label>
      <div class="col-sm-9">
        <div class="input-group input-group-sm">
          <span class="input-group-text">{{ t('style.utilities.hShadow') }}</span>
          <input type="text" v-model="hshadow" class="form-control form-control-sm">
          <span class="input-group-text">{{ t('style.utilities.vShadow') }}</span>
          <input type="text" v-model="vshadow" class="form-control form-control-sm">
        </div>
        <div class="input-group input-group-sm">
          <span class="input-group-text">{{ t('style.utilities.blur') }}</span>
          <input type="text" v-model="blur" class="form-control form-control-sm">
          <div class="p-0 flex-shrink-0 flex-grow-1" style="min-width: 120px">
            <ColorPicker css="w-100" v-model="color"></ColorPicker>
          </div>
        </div>
      </div>
    </div>
    <div class="row mt-1">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('common.align') }} & {{ t('common.style') }}</label>
      <div class="col-sm-9">
        <div class="btn-group btn-group-sm">
          <button type="button" @click="textAlign = 'left'" :class="{'btn btn-outline-secondary':true, 'active':textAlign == 'left'}"><i class="iconfont icon-align-left"></i></button>
          <button type="button" @click="textAlign = 'center'" :class="{'btn btn-outline-secondary':true, 'active':textAlign == 'center'}"><i class="iconfont icon-align-center"></i></button>
          <button type="button" @click="textAlign = 'right'" :class="{'btn btn-outline-secondary':true, 'active':textAlign == 'right'}"><i class="iconfont icon-align-right"></i></button>
        </div>
        <div class="btn-group btn-group-sm ms-1">
          <button type="button" @click="italic = !italic" :class="{'btn btn-outline-secondary':true, 'active':italic}" :title="t('style.text.italic')"><i class="iconfont icon-italic"></i></button>
          <button type="button" @click="underline = !underline" :class="{'btn btn-outline-secondary':true, 'active':underline}" :title="t('style.text.underline')"><i class="iconfont icon-underline"></i></button>
          <button type="button" @click="linethrough = !linethrough" :class="{'btn btn-outline-secondary':true, 'active':linethrough}" :title="t('style.text.lineThrough')"><i class="iconfont icon-through"></i></button>
        </div>
      </div>
    </div>
    <div class="row mt-1">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.text.fontSize') }}</label>
      <div class="col-sm-9">
        <div class="input-group input-group-sm">
          <input type="text" class="form-control form-control-sm" placeholder="px,em,rem" v-model="fontSize">
          <span class="input-group-text">{{ t('style.text.letterSpacing') }}</span>
          <input type="text" placeholder="px,em,rem" class="form-control form-control-sm" v-model="letterSpacing">
        </div>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.text.break') }}</label>
      <div class="col-sm-9">
        <table style="width: 100%">
          <tr>
            <td class="fs-7">{{ t('style.text.wordBreak') }}</td>
            <td class="fs-7">{{ t('style.text.whiteSpace') }}</td>
          </tr>
          <tr>
            <td>
              <select class="form-select form-select-sm" v-model="wordBreak">
                <option value="normal">normal</option>
                <option value="break-all">break-all</option>
                <option value="keep-all">keep-all</option>
              </select>
            </td>
            <td>
              <select class="form-select form-select-sm" v-model="whiteSpace">
                <option value="normal">normal</option>
                <option value="pre">pre</option>
                <option value="nowrap">nowrap</option>
                <option value="pre-wrap">pre-wrap</option>
                <option value="pre-line">pre-line</option>
              </select>
            </td>
          </tr>
        </table>
      </div>
    </div>
    <div class="row mt-1">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.text.wordWrap') }}</label>
      <div class="col-sm-9">
        <select class="form-select form-select-sm" v-model="wordWrap">
          <option value="normal">normal</option>
          <option value="break-word">break word</option>
        </select>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.text.fontFamily') }}</label>
      <div class="col-sm-9">
        <div class="dropdown">
          <a class="btn btn-light w-100 btn-sm dropdown-toggle" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            {{ fontFamily?.name || 'Default' }}
          </a>

          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="javascript:;" @click="removeFont">Default</a></li>
            <li><a class="dropdown-item" href="javascript:;" :style="`font-family: '${font.uuid}'`" @click="changeFont(font)" :key="index" v-for="(font, index) in fonts">{{font.name}}</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import { computed, ref, watch } from 'vue'
import { useStore } from 'vuex'
import ColorPicker from '@/components/common/ColorPicker.vue'

export default {
  name: 'Typography',
  components: { ColorPicker },
  props: {
    previewMode: Boolean
  },
  setup (props: any, context: any) {
    const info = initUI()
    // const selectedUIItem = info.selectedUIItem
    const { t } = useI18n()
    const uploadBtn = ref()
    const store = useStore()
    const fonts = computed(() => store.state.design.page.meta?.custom?.fontFace)

    const textAlign = info.computedWrap('align', 'custom', 'left', false, props.previewMode)
    const bold = info.computedWrap('bold', 'custom', 'Normal', false, props.previewMode)
    const italic = info.computedWrap('italic', 'custom', false, false, props.previewMode)
    const underline = info.computedWrap('underline', 'custom', false, false, props.previewMode)
    const linethrough = info.computedWrap('through', 'custom', false, false, props.previewMode)
    const hshadow = info.computedWrap('textShadowH', 'custom', '', false, props.previewMode)
    const vshadow = info.computedWrap('textShadowV', 'custom', '', false, props.previewMode)
    const blur = info.computedWrap('textShadowBlur', 'custom', '', false, props.previewMode)
    const color = info.computedWrap('textShadowColor', 'custom', '', false, props.previewMode)
    const textStrokeLength = info.computedWrap('textStrokeLength', 'custom', '', false, props.previewMode)
    const textStrokeColor = info.computedWrap('textStrokeColor', 'custom', '', false, props.previewMode)
    const fontSize = info.computedWrap('font-size', 'style', '', false, props.previewMode)
    const letterSpacing = info.computedWrap('letter-spacing', 'style', '', false, props.previewMode)
    const fontFamily = info.computedWrap('font-family', 'style', '', false, props.previewMode)
    const lineHeight = info.computedWrap('line-height', 'style', '', false, props.previewMode)
    const wordWrap = info.computedWrap('word-wrap', 'style', 'normal', false, props.previewMode)
    const wordBreak = info.computedWrap('word-break', 'style', 'normal', false, props.previewMode)
    const whiteSpace = info.computedWrap('white-space', 'style', 'normal', false, props.previewMode)

    const hasInherit = computed(() => {
      return info.hasInheritStyle('custom', ['align', 'bold', 'italic', 'underline', 'through', 'textShadowH', 'textShadowV', 'textShadowBlur', 'textShadowColor', 'textStrokeLength', 'textStrokeColor']) ||
        info.hasInheritStyle('style', ['font-size', 'letter-spacing', 'font-family', 'line-height', 'word-wrap', 'word-break', 'white-space'])
    })

    const hasSet = computed(() => {
      return info.hasSetStyle('custom', ['align', 'bold', 'italic', 'underline', 'through', 'textShadowH', 'textShadowV', 'textShadowBlur', 'textShadowColor', 'textStrokeLength', 'textStrokeColor']) ||
        info.hasSetStyle('style', ['font-size', 'letter-spacing', 'font-family', 'line-height', 'word-wrap', 'word-break', 'white-space'])
    })

    watch([hshadow, vshadow, blur, color], (v) => {
      info.setMeta('text-shadow', `${hshadow.value} ${vshadow.value} ${blur.value} ${color.value}`, 'style', false, props.previewMode)
    })
    watch([textStrokeLength, textStrokeColor], (v) => {
      info.setMeta('text-stroke', `${textStrokeLength.value} ${textStrokeColor.value}`, 'style', false, props.previewMode)
    })
    const removeFont = () => {
      fontFamily.value = undefined
    }
    const changeFont = (font) => {
      fontFamily.value = font
    }

    return {
      ...info,
      hasSet,
      hasInherit,
      t,
      fonts,
      uploadBtn,
      fontFamily,
      textAlign,
      linethrough,
      italic,
      underline,
      bold,
      fontSize,
      letterSpacing,
      lineHeight,
      hshadow,
      vshadow,
      blur,
      textStrokeLength,
      textStrokeColor,
      color,
      wordWrap,
      wordBreak,
      whiteSpace,
      removeFont,
      changeFont
    }
  }
}
</script>
