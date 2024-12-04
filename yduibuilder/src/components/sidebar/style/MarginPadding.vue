<template>
  <div class="style-header">
    <i class="iconfont icon-tree-close"></i> {{t("style.margin")}} & {{t("style.padding")}}
    <i class="iconfont icon-point text-danger" v-if="hasSet"></i>
    <i class="iconfont icon-point text-success" v-if="hasInherit"></i>
  </div>
  <div class="style-body d-none">
    <div  class="fs-7" :style="`border: 1px dashed #444;background-color: ${hoverOnSide==''||hoverOnSide=='margin'?'#f7cba1':'#fff'}`"
         @mouseover.stop="hoverOnSide='margin'" @mouseleave.stop="hoverOnSide=''">
      <div class="d-flex p-1" v-if="hasMargin">
        <div style="width: 40px" class="text-muted">margin</div>
        <div class="flex-grow-1 d-flex align-items-center justify-content-center pointer" @click="openSetting('margin-top')"
             v-html="attrs['margin-top'] || '-'"></div>
        <div style="width: 40px" class="fs-7"></div>
      </div>
      <div class="d-flex">
        <div v-if="hasMargin" class="d-flex align-items-center justify-content-center p-2 flex-shrink-0 pointer" @click="openSetting('margin-left')"
        v-html="attrs['margin-left'] || '-'"></div>
        <div class="flex-grow-1" :style="`border: 1px solid #444;background-color: ${hoverOnSide==''||hoverOnSide=='border'?'#fcdb9f':'#fff'}`"
             @mouseover.stop="hoverOnSide='border'" @mouseleave.stop="hoverOnSide=''">
          <div class="d-flex p-1">
            <div style="width: 40px" class="text-muted">border</div>
            <div class="flex-grow-1 d-flex align-items-center justify-content-center pointer" @click="openSetting('border-top-width')"
                 v-html="attrs['border-top-width'] || '-'"></div>
            <div style="width: 40px"></div>
          </div>
          <div class="d-flex">
            <div class="d-flex align-items-center justify-content-center p-2 flex-shrink-0 pointer" @click="openSetting('border-left-width')"
              v-html="attrs['border-left-width'] || '-'"></div>
            <div class="flex-grow-1" style="width: 20px">
              <div class="flex-grow-1" :style="`border: 1px dashed #444;background-color: ${hoverOnSide==''||hoverOnSide=='padding'?'#c4ce8e':'#fff'}`"
                   @mouseover.stop="hoverOnSide='padding'" @mouseleave.stop="hoverOnSide=''">
                <div class="d-flex p-1">
                  <div style="width: 40px" class="text-muted">padding</div>
                  <div class="flex-grow-1 d-flex align-items-center justify-content-center pointer" @click="openSetting('padding-top')"
                       v-html="attrs['padding-top'] || '-'"></div>
                  <div style="width: 40px"></div>
                </div>
                <div class="d-flex p-1">
                  <div class="d-flex align-items-center justify-content-center p-2 flex-shrink-0 pointer" @click="openSetting('padding-left')"
                    v-html="attrs['padding-left'] || '-'"></div>
                  <div class="flex-grow-1 d-flex align-items-center text-nowrap justify-content-center pointer"  @click="openSetting('sizing')"
                       :style="`border: 1px solid #444;background-color:  ${hoverOnSide==''||hoverOnSide=='sizing'?'#8eb5c0':'#fff'}`"
                       @mouseover.stop="hoverOnSide='sizing'" @mouseleave.stop="hoverOnSide=''"
                  >{{currWidth || 'auto'}} <span class="text-muted">&nbsp;x&nbsp;</span> {{currHeight || 'auto'}}</div>
                  <div class="d-flex align-items-center justify-content-center p-2 flex-shrink-0 pointer" @click="openSetting('padding-right')"
                       v-html="attrs['padding-right'] || '-'"></div>
                </div>
                <div class="d-flex align-items-center justify-content-center p-1 pointer" @click="openSetting('padding-bottom')"
                v-html="attrs['padding-bottom'] || '-'"></div>
              </div>
            </div>
            <div class="d-flex align-items-center justify-content-center p-2 flex-shrink-0 pointer" @click="openSetting('border-right-width')"
            v-html="attrs['border-right-width'] || '-'"></div>
          </div>
          <div class="flex-grow-1 p-1 d-flex align-items-center justify-content-center pointer" @click="openSetting('border-bottom-width')"
               v-html="attrs['border-bottom-width'] || '-'"></div>
        </div>
        <div v-if="hasMargin" class="d-flex align-items-center justify-content-center p-2 flex-shrink-0 pointer" @click="openSetting('margin-right')"
        v-html="attrs['margin-right'] || '-'"></div>
      </div>
      <div v-if="hasMargin" class="flex-grow-1 p-1 d-flex align-items-center justify-content-center pointer" @click="openSetting('margin-bottom')"
      v-html="attrs['margin-bottom'] || '-'"></div>
    </div>
  </div>
  <lay-layer v-model="isOpenSetting" :title="t('style.'+settingSide)" :shade="true" :area="['500px', '300px']">
    <div class="p-3">
      <template v-if="settingSide=='sizing'">
        <StyleSize :auto-open="true"></StyleSize>
      </template>
      <template v-else>
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
        <div class="form-group mb-3">
          <label><input type="checkbox" v-model="syncOtherSide">{{t("style.syncOtherSide")}}</label>
        </div>
      </template>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { computed, ref, toRef, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import initUI from '@/components/Common'
import StyleSize from '@/components/sidebar/style/Size.vue'

export default {
  name: 'StyleMarginPadding',
  components: { StyleSize },
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
    const syncOtherSide = ref(false)
    const previewMode = toRef(props, 'previewMode')

    const pickCssStyle = (meta: any, _css: any, _style: any) => {
      if (meta.css) {
        for (const name in meta.css) {
          if (name.match(/margin/)) {
            _css.margin[name] = meta.css[name]
          } else if (name.match(/padding/)) {
            _css.padding[name] = meta.css[name]
          } else if (name.match(/border/)) {
            _css.border[name] = meta.css[name]
          }
        }
      }
      if (meta.style) {
        for (const name in meta.style) {
          if (name.match(/margin/)) {
            _style.margin[name] = meta.style[name]
          } else if (name.match(/padding/)) {
            _style.padding[name] = meta.style[name]
          } else if (name.match(/border/)) {
            _style.border[name] = meta.style[name]
          }
        }
      }
    }

    const attrs = computed(() => {
      if (!selectedUIItem.value) return
      const _attr: any = {}
      const _css: any = { margin: {}, padding: {}, border: {} }
      const _style: any = { margin: {}, padding: {}, border: {} }

      const meta = selectedUIItem.value.meta
      const selector = selectedUIItem.value.meta.selector
      // console.log(meta)
      if (selector) pickCssStyle(selector, _css, _style)
      pickCssStyle(meta, _css, _style)
      console.log(_attr)
      for (const key in _css.margin) {
        _attr[key] = _css.margin[key]
      }
      for (const key in _css.padding) {
        _attr[key] = _css.padding[key]
      }
      for (const key in _css.border) {
        _attr[key] = _css.border[key]
      }
      for (const key in _style.margin) {
        _attr[key] = _attr[key] ? _attr[key] + '<br/>' + _style.margin[key] : _style.margin[key]
      }
      for (const key in _style.padding) {
        _attr[key] = _attr[key] ? _attr[key] + '<br/>' + _style.padding[key] : _style.padding[key]
      }
      for (const key in _style.border) {
        _attr[key] = _attr[key] ? _attr[key] + '<br/>' + _style.border[key] : _style.border[key]
      }

      return _attr
    })
    watch(syncOtherSide, (v) => {
      if (v) {
        sync(sizeClass.value, 'css')
        sync(size.value, 'style')
      }
    })
    const sizeClass = computed<string>({
      get () {
        return info.getMeta(settingSide.value, 'css', previewMode)
      },
      set (v) {
        sync(v, 'css')
      }
    })
    const size = computed<string>({
      get () {
        return info.getMeta(settingSide.value, 'style', previewMode)
      },
      set (v) {
        sync(v, 'style')
      }
    })
    function sync (v, type) {
      if (syncOtherSide.value) {
        if (settingSide.value.match(/^margin/)) {
          info.setMeta('margin-top', v === 'inherit' ? undefined : v, type, false, previewMode)
          info.setMeta('margin-right', v === 'inherit' ? undefined : v, type, false, previewMode)
          info.setMeta('margin-bottom', v === 'inherit' ? undefined : v, type, false, previewMode)
          info.setMeta('margin-left', v === 'inherit' ? undefined : v, type, false, previewMode)
        } else if (settingSide.value.match(/^border/)) {
          info.setMeta('border-top-width', v === 'inherit' ? undefined : v, type, false, previewMode)
          info.setMeta('border-right-width', v === 'inherit' ? undefined : v, type, false, previewMode)
          info.setMeta('border-bottom-width', v === 'inherit' ? undefined : v, type, false, previewMode)
          info.setMeta('border-left-width', v === 'inherit' ? undefined : v, type, false, previewMode)
        } else if (settingSide.value.match(/^padding/)) {
          info.setMeta('padding-top', v === 'inherit' ? undefined : v, type, false, previewMode)
          info.setMeta('padding-right', v === 'inherit' ? undefined : v, type, false, previewMode)
          info.setMeta('padding-bottom', v === 'inherit' ? undefined : v, type, false, previewMode)
          info.setMeta('padding-left', v === 'inherit' ? undefined : v, type, false, previewMode)
        }
      } else {
        info.setMeta(settingSide.value, v || undefined, type, false, previewMode)
      }
    }
    const currWidth = info.computedWrap('width', 'style', '', false, previewMode)
    const currHeight = info.computedWrap('height', 'style', '', false, previewMode)

    const openSetting = (type: string) => {
      isOpenSetting.value = true
      syncOtherSide.value = false
      settingSide.value = type
    }
    const closeSetting = () => {
      isOpenSetting.value = false
      settingSide.value = ''
    }
    const hasInherit = computed(() => {
      return info.hasInheritStyle(
        'style',
        ['margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left', 'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left'], previewMode
      ) ||
        info.hasInheritStyle(
          'css',
          ['margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left', 'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left'], previewMode
        )
    })

    const hasSet = computed(() => {
      return info.hasSetStyle(
        'style',
        ['margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left', 'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left'], previewMode
      ) ||
        info.hasSetStyle(
          'css',
          ['margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left', 'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left'], previewMode
        )
    })
    const hasMargin = computed(() => {
      // 最顶层组件没有外边距
      return info.selectedUIItemId.value !== info.selectedPageId.value
    })
    return {
      ...info,
      attrs,
      hasInherit,
      hasSet,
      hoverOnSide,
      hasMargin,
      isOpenSetting,
      settingSide,
      sizeClass,
      size,
      openSetting,
      closeSetting,
      currWidth,
      currHeight,
      t,
      syncOtherSide
    }
  }
}
</script>
