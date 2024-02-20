<template>
  <div class="style-header">
    <i class="iconfont icon-tree-close"></i> {{t("style.margin")}} & {{t("style.padding")}}
    <i class="iconfont icon-point text-danger" v-if="hasSet"></i>
    <i class="iconfont icon-point text-success" v-if="hasInherit"></i>
  </div>
  <div class="style-body d-none">
      <div class="text-muted text-center">{{t("style.margin")}}</div>
      <!--[margin-->
      <table class="_margin">
      <tr>
        <td></td>
        <td :class="{'_margin-top': true, '_hover': hoverOnSide=='margin-top'}" @click="openSetting('margin-top')" @mouseover="hoverOnSide='margin-top'" @mouseleave="hoverOnSide=''">MT</td>
        <td></td>
      </tr>
      <tr>
        <td :class="{'_margin-left': true, '_hover': hoverOnSide=='margin-left'}" @click="openSetting('margin-left')" @mouseover="hoverOnSide='margin-left'" @mouseleave="hoverOnSide=''">ML</td>
        <td :class="{'_content w-100 text-start': true, '_hover': hoverOnSide=='margin'}" @click="openSetting('margin')" @mouseover="hoverOnSide='margin'" @mouseleave="hoverOnSide=''"> <small v-html="marginAttrs"></small></td>
        <td :class="{'_margin-right': true, '_hover': hoverOnSide=='margin-right'}" @click="openSetting('margin-right')" @mouseover="hoverOnSide='margin-right'" @mouseleave="hoverOnSide=''">MR</td>
      </tr>
      <tr>
        <td></td>
        <td :class="{'_margin-bottom': true, '_hover': hoverOnSide=='margin-bottom'}" @click="openSetting('margin-bottom')" @mouseover="hoverOnSide='margin-bottom'" @mouseleave="hoverOnSide=''">MB</td>
        <td></td>
      </tr>
    </table>

    <div class="text-center text-muted mt-2">{{t("style.padding")}}</div>
    <table class="_padding">
      <tr>
        <td></td>
        <td :class="{'_padding-top': true, '_hover': hoverOnSide=='padding-top'}" @click="openSetting('padding-top')" @mouseover="hoverOnSide='padding-top'" @mouseleave="hoverOnSide=''">PT</td>
        <td></td>
      </tr>
      <tr>
        <td :class="{'_padding-left': true, '_hover': hoverOnSide=='padding-left'}" @click="openSetting('padding-left')" @mouseover="hoverOnSide='padding-left'" @mouseleave="hoverOnSide=''">PL</td>
        <td :class="{'_content w-100 text-start': true, '_hover': hoverOnSide=='padding'}" @click="openSetting('padding')" @mouseover="hoverOnSide='padding'" @mouseleave="hoverOnSide=''"> <small v-html="paddingAttrs"></small></td>
        <td :class="{'_padding-right': true, '_hover': hoverOnSide=='padding-right'}" @click="openSetting('padding-right')" @mouseover="hoverOnSide='padding-right'" @mouseleave="hoverOnSide=''">PR</td>
      </tr>
      <tr>
        <td></td>
        <td :class="{'_padding-bottom': true, '_hover': hoverOnSide=='padding-bottom'}" @click="openSetting('padding-bottom')" @mouseover="hoverOnSide='padding-bottom'" @mouseleave="hoverOnSide=''">PB</td>
        <td></td>
      </tr>
    </table>
  </div>
  <div v-if="isOpenSetting" style="z-index: 1041;position: absolute;top: 30%;left:0px;right: 0px">
    <div class="card m-3 shadow-lg">
        <div class="card-header d-flex justify-content-between align-items-center">{{t("style."+settingSide)}}
          <button type="button" class="btn btn-light btn-sm" @click="closeSetting()" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="card-body">
          <div class="form-group mb-3" v-if="cssMap[settingSide]">
            <label>{{t("style.predefinedClass")}} <small class="text-muted">{{ui}} {{uiVersion}}</small></label>
            <select class="form-select" v-model="sizeClass">
              <option :value="css" :selected="sizeClass == css" :key="css" v-for="(name, css) in cssMap[settingSide]">{{ name }}</option>
            </select>
          </div>

          <div class="form-group mb-3">
            <label>{{t("style.value")}}</label>
            <div class="input-group">
              <input type="text" class="form-control w-75" placeholder="" v-model="size">
            </div>
          </div>
        </div>
      </div>
  </div>
  <div class="right-backdrop" v-if="rightBackdropVisible"></div>
</template>

<script lang="ts">
import { computed, nextTick, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import initUI from '@/components/Common'

export default {
  name: 'StyleMarginPadding',
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

    const pickCssStyle = (meta: any, _css: any, _style: any) => {
      if (meta.css) {
        for (const name in meta.css) {
          if (name.match(/margin/)) {
            _css.margin.push(meta.css[name])
          } else if (name.match(/padding/)) {
            _css.padding.push(meta.css[name])
          }
        }
      }
      if (meta.style) {
        for (const name in meta.style) {
          if (name.match(/margin/)) {
            _style.margin.push(name + ':' + meta.style[name])
          } else if (name.match(/padding/)) {
            _style.padding.push(name + ':' + meta.style[name])
          }
        }
      }
    }

    const attrs = computed(() => {
      if (!selectedUIItem.value) return
      const _css: any = { margin: [], padding: [] }
      const _style: any = { margin: [], padding: [] }
      const _attr: any = { margin: '', padding: '' }

      const meta = selectedUIItem.value.meta
      const selector = selectedUIItem.value.meta.selector
      // console.log(meta)
      if (selector) pickCssStyle(selector, _css, _style)
      pickCssStyle(meta, _css, _style)

      if (_css.margin.length) {
        _attr.margin = `css: ${_css.margin.join(' ')}`
      }
      if (_style.margin.length) {
        _attr.margin += `<br/>style: ${_style.margin.join(';')}`
      }
      if (_css.padding.length) {
        _attr.padding = `css: ${_css.padding.join(' ')}`
      }
      if (_style.padding.length) {
        _attr.padding += `<br/>style: ${_style.padding.join(';')}`
      }
      return _attr
    })

    const marginAttrs = ref<string>('')
    const paddingAttrs = ref<string>('')

    watch(attrs, (attr) => {
      if (!attr) {
        marginAttrs.value = ''
        paddingAttrs.value = ''
        return
      }
      marginAttrs.value = attr.margin
      paddingAttrs.value = attr.padding
    }, { immediate: true })
    const sizeClass = computed<string>({
      get () {
        return info.getMeta(settingSide.value, 'css', props.previewMode)
      },
      set (v) {
        info.setMeta(settingSide.value, v === 'inherit' ? undefined : v, 'css', false, props.previewMode)
      }
    })
    const size = computed<string>({
      get () {
        return info.getMeta(settingSide.value, 'style', props.previewMode)
      },
      set (v) {
        info.setMeta(settingSide.value, v || undefined, 'style', false, props.previewMode)
      }
    })

    const rightBackdropVisible = ref(false)

    const openSetting = (type: string) => {
      isOpenSetting.value = true
      nextTick(() => {
        rightBackdropVisible.value = true
      })
      settingSide.value = type
    }
    const closeSetting = () => {
      isOpenSetting.value = false
      rightBackdropVisible.value = false
      settingSide.value = ''
    }
    const hasInherit = computed(() => {
      return info.hasInheritStyle(
        'style',
        ['margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left', 'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left']
      ) ||
        info.hasInheritStyle(
          'css',
          ['margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left', 'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left']
        )
    })

    const hasSet = computed(() => {
      return info.hasSetStyle(
        'style',
        ['margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left', 'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left']
      ) ||
        info.hasSetStyle(
          'css',
          ['margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left', 'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left']
        )
    })
    return {
      ...info,
      attrs,
      hasInherit,
      hasSet,
      hoverOnSide,
      isOpenSetting,
      settingSide,
      sizeClass,
      size,
      marginAttrs,
      paddingAttrs,
      rightBackdropVisible,
      openSetting,
      closeSetting,
      t
    }
  }
}
</script>
<style scoped lang="scss">
@import 'src/assets/bootstrap/bootstrap.scss';

  ._margin{
    cursor: pointer;
    background-color: #f8cca1;
    border: 1px solid #444;
    td{
      padding: 5px;
      margin: 0px;
      font-size: $font-size-sm !important;
      text-align: center;
    }
    ._content{
      background-color: #fae0c7;
      border: 1px solid #4f676d;
      padding: 20px;
    }
    ._hover{
      background-color: #fdbd85;
    }
  }
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
  ._padding{
    background-color: #c5cf8e;
    border: 1px solid #7e7e7e;
    cursor: pointer;
    td{
      padding: 5px;
      margin: 0px;
      font-size: $font-size-sm !important;
      text-align: center;
    }
    ._content{
      background-color: #e8efc1;
      border: 1px solid #4f676d;
      padding: 20px;
    }
    ._hover{
      background-color: #99a74e;
    }
  }
</style>
