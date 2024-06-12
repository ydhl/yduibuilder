<template>
  <div
    :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
    :data-pageid="pageid"
    :class="['layui-d-flex layui-align-items-center',dragableCss, uiCss,{'layui-form-item': uiconfig.meta?.form?.horizontal, 'hidden-preview':uiconfig.meta?.form?.state==='hidden'}]">
    <label :for="uiconfig.meta.id+uiconfig.type" :class="labelCss" :style="labelStyle"
           v-if="hasTitle">{{uiconfig.meta.title}}</label>
    <div :class="bodyCss">
      <slot></slot>
      <small :id="uiconfig.meta.id+'Help'"  v-if="hasHelpTip && uiconfig.meta?.form?.horizontal" class="layui-form-mid layui-word-aux">{{uiconfig.meta?.form?.helpTip}}</small>
    </div>
    <small :id="uiconfig.meta.id+'Help'"  v-if="hasHelpTip && !uiconfig.meta?.form?.horizontal" class="layui-form-mid layui-word-aux">{{uiconfig.meta?.form?.helpTip}}</small>
  </div>
</template>

<script lang="ts">
import FormGroup from '@/components/ui/js/FormGroup'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Layui_FromGroup',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const formGroup = new FormGroup(props, context, useStore())
    const setup = formGroup.setup()
    const uiCss = computed(() => {
      const css = formGroup.getUICss()
      delete css.formSizing

      if (props.uiconfig.meta?.form?.state === 'disabled') {
        css.disabled = 'layui-disabled'
      }
      // console.log(css)
      return Object.values(css).join(' ')
    })
    const labelCss = computed(() => {
      const css: any = []

      if (props.uiconfig.meta?.form?.horizontal) {
        css.push('layui-form-mid')
        css.push('layui-col-xs' + props.uiconfig.meta?.form?.horizontalCol)
      } else {
        css.push('layui-form-label-col')
      }
      return css
    })

    const labelStyle = computed(() => {
      const styles: any = []
      if (props.uiconfig.meta?.form?.horizontal) {
        styles.push('margin-right: 0px !important;padding-right: 15px !important;')
      }

      if (props.uiconfig.meta.titleAlign) {
        styles.push('text-align:' + props.uiconfig.meta.titleAlign)
      }
      return styles.join(';')
    })

    const bodyCss = computed(() => {
      const css: any = []
      if (props.uiconfig.meta?.form?.horizontal) {
        css.push('layui-col-xs' + (12 - (props.uiconfig.meta?.form?.horizontalCol || 2)))
      }
      // console.log(setup.hasTitle)
      if (!setup.hasTitle.value && props.uiconfig.meta?.form?.horizontal) {
        css.push('layui-col-xs-offset' + (props.uiconfig.meta?.form?.horizontalCol || 2))
      }
      return css
    })
    const uiStyle = computed(() => {
      // 部分style由包含的控件处理
      const style = formGroup.getUIStyle()
      const newStyle = {}
      for (const styleKey in style) {
        if (!styleKey.match(/height/)) {
          newStyle[styleKey] = style[styleKey]
        }
      }
      return formGroup.appendImportant(newStyle)
    })
    return {
      ...setup,
      labelCss,
      labelStyle,
      bodyCss,
      uiStyle,
      uiCss
    }
  }
}

</script>
