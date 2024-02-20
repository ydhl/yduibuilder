<template>
  <div class="style-header">
    <i class="iconfont icon-tree-close"></i> {{t("style.borderOutline")}}
    <i class="iconfont icon-point text-danger" v-if="hasSet"></i>
    <i class="iconfont icon-point text-success" v-if="hasInherit"></i>
  </div>
  <div class="style-body d-none">
    <table class="_border">
      <tr>
        <td :class="{'_hover': hoverOnSide=='border-top-left-radius'}" @click="openRadiusSetting('border-top-left-radius')" @mouseover="hoverOnSide='border-top-left-radius'" @mouseleave="hoverOnSide=''">TL</td>
        <td :class="{'_border-top w-100': true, '_hover': hoverOnSide=='border-top'}" @click="openSetting('border-top')" @mouseover="hoverOnSide='border-top'" @mouseleave="hoverOnSide=''">T</td>
        <td :class="{'_hover': hoverOnSide=='border-top-right-radius'}" @click="openRadiusSetting('border-top-right-radius')" @mouseover="hoverOnSide='border-top-right-radius'" @mouseleave="hoverOnSide=''">TR</td>
      </tr>
      <tr>
        <td :class="{'_border-left': true, '_hover': hoverOnSide=='border-left'}" @click="openSetting('border-left')" @mouseover="hoverOnSide='border-left'" @mouseleave="hoverOnSide=''">L</td>
        <td :class="{'_content text-start': true, '_hover': hoverOnSide=='border'}" @click="openSetting('border')" @mouseover="hoverOnSide='border'" @mouseleave="hoverOnSide=''"><small v-html="attrs"></small></td>
        <td :class="{'_border-right': true, '_hover': hoverOnSide=='border-right'}" @click="openSetting('border-right')" @mouseover="hoverOnSide='border-right'" @mouseleave="hoverOnSide=''">R</td>
      </tr>
      <tr>
        <td :class="{'_hover': hoverOnSide=='border-bottom-left-radius'}" @click="openRadiusSetting('border-bottom-left-radius')" @mouseover="hoverOnSide='border-bottom-left-radius'" @mouseleave="hoverOnSide=''">BL</td>
        <td :class="{'_border-bottom': true, '_hover': hoverOnSide=='border-bottom'}" @click="openSetting('border-bottom')" @mouseover="hoverOnSide='border-bottom'" @mouseleave="hoverOnSide=''">B</td>
        <td :class="{'_hover': hoverOnSide=='border-bottom-right-radius'}" @click="openRadiusSetting('border-bottom-right-radius')" @mouseover="hoverOnSide='border-bottom-right-radius'" @mouseleave="hoverOnSide=''">BR</td>
      </tr>
    </table>
    <div class="row mt-3">
      <label class="col-sm-3 col-form-label text-end">{{t("style.outlineWidth")}}</label>
      <div class="col-sm-9 d-flex">
        <div class="input-group">
          <input type="text" class="form-control form-control-sm w-75" placeholder="" v-model="outlineWidth">
        </div>
      </div>
    </div>
    <div class="row mt-2">
      <label class="col-sm-3 col-form-label text-end">{{t("style.outlineStyle")}}</label>
      <div class="col-sm-9 d-flex">
        <select class="form-select" v-model="outlineStyle">
          <option :value="css" :selected="outlineStyle == css " :key="css" v-for="(name, css) in cssMap['outlineStyle']">{{ name }}</option>
        </select>
      </div>
    </div>
    <div class="row mt-2">
      <label class="col-sm-3 col-form-label text-end">{{t("style.outlineColor")}}</label>
      <div class="col-sm-9 d-flex">
        <ColorPicker v-model="outlineColor" css="w-100"></ColorPicker>
      </div>
    </div>
  </div>
  <div v-if="isOpenSetting"  style="z-index: 1041;position: absolute;top: 10%;left:0px;right: 0px">
      <div class="card m-3 shadow-lg">
        <div class="card-header d-flex justify-content-between align-items-center">{{t("style."+settingSide)}}
          <button type="button" class="btn btn-light btn-sm" @click="closeSetting()" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="card-body">
          <template v-if="settingSide==='border'">
            <div class="form-group" >
              <label>{{t("style.radiusSize")}}</label>
              <div class="input-group input-group-sm mb-3">
                <input type="text" class="form-control form-control-sm" placeholder="5px, 0.23em, 5%" v-model="borderRoundSize">
              </div>
            </div>
          </template>

          <div class="form-group mb-3">
            <label>{{t("style.width")}}</label>
            <div class="input-group">
              <input type="text" class="form-control form-control-sm w-75" placeholder="" v-model="size">
            </div>
          </div>
          <div class="form-group mb-3">
            <label>{{t("common.style")}}</label>
            <select class="form-select form-select-sm" v-model="borderStyle">
              <option value="none">None</option>
              <option value="solid">Solid</option>
              <option value="dotted">Dotted</option>
              <option value="dashed">Dashed</option>
            </select>
          </div>
          <div class="form-group">
            <label>{{t("style.borderColor")}}</label>
            <div class="input-group input-group-sm mb-3">
              <ColorPicker v-model="borderColor" css="form-control form-control-sm"></ColorPicker>
              <span class="input-group-text">{{t("style.predefinedClass")}}</span>
              <select class="form-select form-select-sm" v-model="borderColorClass">
                <option v-for="theme in cssMap.borderColorClass" :key="theme">{{theme}}</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
  <div v-if="isOpenRadiusSetting"  style="z-index: 1041;position: absolute;top: 30%;left:0px;right: 0px">
    <div class="card m-3 shadow-lg">
      <div class="card-header d-flex justify-content-between align-items-center">{{t("style."+settingSide)}}
        <button type="button" class="btn btn-light btn-sm" @click="closeSetting()" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="card-body">
        <div class="form-group mb-3">
          <label>{{t("style.radius")}}</label>
          <div class="input-group">
            <input type="text" class="form-control w-75" placeholder="5px 50%" v-model="radius">
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="right-backdrop" v-if="rightBackdropVisible"></div>
</template>

<script lang="ts">
import { computed, nextTick, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import initUI from '@/components/Common'
import ColorPicker from '@/components/common/ColorPicker.vue'

export default {
  name: 'StyleBorder',
  components: { ColorPicker },
  props: {
    previewMode: Boolean
  },
  setup (props: any, context: any) {
    const info = initUI()
    const selectedUIItem = info.selectedUIItem
    const { t } = useI18n()
    const hoverOnSide = ref('')
    const settingSide = ref('')
    const isOpenSetting = ref(false)
    const isOpenRadiusSetting = ref(false)
    const mpbSettingDialog = ref()
    const rightBackdropVisible = ref(false)

    const attrs = computed(() => {
      const _css: any = []
      const _style: any = []
      const _attr: any = []
      const meta = selectedUIItem.value?.meta
      const cssMap = info.cssMap.value
      // console.log(meta)

      if (meta?.css) {
        for (const name in meta.css) {
          if (!name.match(/border/)) continue
          _css.push(cssMap?.[name]?.[meta.css[name]] || meta.css[name])
        }
      }
      if (meta?.style) {
        for (const name in meta.style) {
          if (!meta.style[name] || !name.match(/border/)) continue
          _style.push(name + ':' + meta.style[name])
        }
      }

      if (_css.length) {
        _attr.push(`css: <br/>${_css.join(',')}`)
      }

      if (_style.length) {
        _attr.push(`style: <br/>${_style.join('<br/>')}`)
      }
      return _attr.length === 0 ? '' : _attr.join('<br/><br/>', _attr)
    })

    const getStyle = (name) => {
      const style = info.getMeta(name, 'style', props.previewMode)
      return style || ''
    }
    const borderRoundSize = info.computedWrap('border-radius', 'style', '', false, props.previewMode)
    const outlineWidth = info.computedWrap('outline-width', 'style', false, props.previewMode)
    const outlineColor = info.computedWrap('outline-color', 'style', false, props.previewMode)
    const outlineStyle = info.computedWrap('outline-style', 'style', false, props.previewMode)

    const size = computed<string>({
      get () {
        return getStyle(`${settingSide.value}-width`)
      },
      set (v) {
        info.setMeta(`${settingSide.value}-width`, v || undefined, 'style', false, props.previewMode)
      }
    })
    const radius = computed<string>({
      get () {
        return getStyle(`${settingSide.value}`)
      },
      set (v) {
        info.setMeta(`${settingSide.value}`, v || undefined, 'style', false, props.previewMode)
      }
    })
    const borderColor = computed({
      get () {
        return getStyle(`${settingSide.value}-color`)
      },
      set (v) {
        info.setMeta(`${settingSide.value}-color`, v || undefined, 'style', false, props.previewMode)
      }
    })
    const borderColorClass = computed({
      get () {
        return (info.getMeta('borderColorClass', 'css') || '').replace(/border-/, '', props.previewMode)
      },
      set (v) {
        info.setMeta('borderColorClass', v, 'css', false, props.previewMode)
      }
    })
    const borderStyle = computed({
      get () {
        return getStyle(`${settingSide.value}-style`) || 'none'
      },
      set (v) {
        info.setMeta(`${settingSide.value}-style`, v || undefined, 'style', false, props.previewMode)
      }
    })
    const openRadiusSetting = (type: string) => {
      isOpenSetting.value = false
      isOpenRadiusSetting.value = true
      nextTick(() => {
        rightBackdropVisible.value = true
      })
      settingSide.value = type
    }
    const openSetting = (type: string) => {
      isOpenRadiusSetting.value = false
      isOpenSetting.value = true
      nextTick(() => {
        rightBackdropVisible.value = true
      })
      settingSide.value = type
    }
    const closeSetting = () => {
      isOpenSetting.value = false
      isOpenRadiusSetting.value = false
      rightBackdropVisible.value = false
      settingSide.value = ''
    }

    const hasInherit = computed(() => {
      return info.hasInheritStyle(
        'style',
        ['border-width', 'border-top-width', 'border-left-width', 'border-right-width',
          'border-bottom-width', 'border-color', 'border-top-color',
          'border-left-color', 'border-right-color',
          'border-bottom-color',
          'border-style',
          'border-top-style',
          'border-left-style',
          'border-right-style',
          'border-bottom-style',
          'border-top-left-radius',
          'border-top-right-radius',
          'border-bottom-left-radius',
          'border-bottom-right-radius',
          'border-radius',
          'outline-color',
          'outline-style',
          'outline-width'
        ]
      ) ||
        info.hasInheritStyle(
          'css',
          ['borderColorClass']
        )
    })

    const hasSet = computed(() => {
      return info.hasSetStyle(
        'style',
        ['border-width', 'border-top-width', 'border-left-width', 'border-right-width',
          'border-bottom-width', 'border-color', 'border-top-color',
          'border-left-color', 'border-right-color',
          'border-bottom-color',
          'border-style',
          'border-top-style',
          'border-left-style',
          'border-right-style',
          'border-bottom-style',
          'border-top-left-radius',
          'border-top-right-radius',
          'border-bottom-left-radius',
          'border-bottom-right-radius',
          'border-radius',
          'outline-color',
          'outline-style',
          'outline-width'
        ]
      ) ||
        info.hasSetStyle(
          'css',
          ['borderColorClass']
        )
    })
    return {
      ...info,
      hasInherit,
      hasSet,
      hoverOnSide,
      isOpenSetting,
      isOpenRadiusSetting,
      mpbSettingDialog,
      settingSide,
      borderRoundSize,
      size,
      outlineWidth,
      outlineStyle,
      outlineColor,
      radius,
      borderStyle,
      attrs,
      borderColor,
      rightBackdropVisible,
      openRadiusSetting,
      openSetting,
      closeSetting,
      borderColorClass,
      t
    }
  }
}
</script>
<style scoped lang="scss">
@import 'src/assets/bootstrap/bootstrap.scss';

  ._border{
    background-color: #fddc9f;
    border: 1px solid #444;
    cursor: pointer;
    td{
      padding: 5px;
      margin: 0px;
      font-size: $font-size-sm !important;
      text-align: center;
    }
    ._content{
      background-color: #fae4bc;
      border: 1px solid #4f676d;
      padding: 20px;
    }

    ._hover{
      background-color: #ffc556;
    }
  }
</style>
