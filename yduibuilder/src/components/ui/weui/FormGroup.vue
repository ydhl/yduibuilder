<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
    :data-pageid="pageid"
    :class="[dragableCss, uiCss]">
    <template v-if="isHorizontal">
      <div :class="singleLineCss">
        <div :class="labelCss" v-if="hasTitle">
          <span class="weui-label">{{uiconfig.meta.title}}<span v-if="uiconfig.meta?.form?.required" class="text-danger">*</span></span>
        </div>
        <div class="weui-cell__bd">
          <div :class="bodyCss">
            <slot></slot>
          </div>
          <small :id="uiconfig.meta.id+'Help'"  v-if="hasHelpTip" class="text-muted">{{uiconfig.meta?.form?.helpTip}}</small>
        </div>
      </div>
    </template>
    <template v-else>
      <div :class="labelCss" v-if="hasTitle">{{uiconfig.meta.title}}<span v-if="uiconfig.meta?.form?.required" class="text-danger">*</span></div>
      <div class="weui-cells weui-cells_form" :style="boxStyle">
        <div class="weui-cell ">
          <div class="weui-cell__bd">
            <div :class="bodyCss">
              <slot></slot>
            </div>
            <small :id="uiconfig.meta.id+'Help'"  v-if="hasHelpTip" class="text-muted">{{uiconfig.meta?.form?.helpTip}}</small>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script lang="ts">
import FormGroup from '@/components/ui/js/FormGroup'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Weui_FromGroup',
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
    const isHorizontal = computed(() => {
      const horizontal = props.uiconfig.meta?.form?.horizontal
      if (horizontal === undefined) return true
      return horizontal
    })
    const singleLineCss = computed(() => {
      const css: any = ['weui-cell']
      if (props.uiconfig.type === 'Select') {
        css.push('align-items-center')
      } else {
        css.push('align-items-start')
      }
      return css.join(' ')
    })
    const uiCss = computed(() => {
      const css = Object.values(formGroup.getUICss())
      if (isHorizontal.value) {
        css.push('weui-cells')
      } else {
        css.push('weui-cells__group')
      }
      if (props.uiconfig.meta?.form?.state === 'hidden') {
        css.push('hidden-preview')
      }

      if (props.uiconfig.meta?.form?.state === 'readonly' || props.uiconfig.meta?.form?.state === 'disabled') {
        css.push('weui-cell_readonly')
      }
      return css.join(' ')
    })
    const labelCss = computed(() => {
      const css: any = []
      if (isHorizontal.value) {
        css.push('weui-cell__hd pr-2')
      } else {
        css.push('weui-cells__title text-dark')
      }
      if (props.uiconfig.meta.titleAlign) {
        css.push('text-' + props.uiconfig.meta.titleAlign)
      }
      return css
    })
    const bodyCss = computed(() => {
      const css: any = ['weui-flex']
      if (props.uiconfig.type === 'Textarea') {
        css.push('align-items-start')
      } else {
        css.push('align-items-center')
      }
      return css
    })
    const uiStyle = computed(() => {
      const style = formGroup.getUIStyle()
      // 垂直排列时阴影交给内部元素处理
      if (!isHorizontal.value) {
        delete style['box-shadow']
      }
      return formGroup.appendImportant(style)
    })
    const boxStyle = computed(() => {
      const style = formGroup.getUIStyle()
      // 阴影交给内部元素处理
      return 'box-shadow:' + style['box-shadow']
    })
    return {
      ...setup,
      labelCss,
      bodyCss,
      uiStyle,
      uiCss,
      isHorizontal,
      singleLineCss,
      boxStyle
    }
  }
}

</script>
