<template>
  <div class="style-header">
    <i class="iconfont icon-tree-close"></i> {{ t('style.sizing') }}
    <i class="iconfont icon-point text-danger" v-if="hasSet"></i>
    <i class="iconfont icon-point text-success" v-if="hasInherit"></i>
  </div>
  <div class="style-body d-none">

    <div class="row mt-3">
      <div class="col-sm-9 offset-sm-3">
        <table class="table table-sm m-0">
          <tr class="text-muted">
            <td class="fs-7">{{ t('style.layout.width') }}:</td>
            <td class="fs-7">{{ t('style.layout.height') }}:</td>
          </tr>
        </table>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.layout.size') }}</label>
      <div class="col-sm-9">
        <table class="table table-sm m-0">
          <tr>
            <td><input class="form-control form-control-sm" :placeholder="t('style.layout.widthTip')" type="text" v-model="currWidth"></td>
            <td><input class="form-control form-control-sm" :placeholder="t('style.layout.widthTip')" type="text" v-model="currHeight"></td>
          </tr>
        </table>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.layout.minSize') }}</label>
      <div class="col-sm-9">
        <table class="table table-sm m-0">
          <tr>
            <td><input class="form-control form-control-sm" :placeholder="t('style.layout.widthTip')" type="text" v-model="minWidth"></td>
            <td><input class="form-control form-control-sm" :placeholder="t('style.layout.widthTip')" type="text" v-model="minHeight"></td>
          </tr>
        </table>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.layout.maxSize') }}</label>
      <div class="col-sm-9">
        <table class="table table-sm m-0">
          <tr>
            <td><input class="form-control form-control-sm" :placeholder="t('style.layout.widthTip')" type="text" v-model="maxWidth"></td>
            <td><input class="form-control form-control-sm" :placeholder="t('style.layout.widthTip')" type="text" v-model="maxHeight"></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

export default {
  name: 'StyleSize',
  props: {
    previewMode: Boolean
  },
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()

    const currWidth = info.computedWrap('width', 'style', '', false, props.previewMode)
    const currHeight = info.computedWrap('height', 'style', '', false, props.previewMode)
    const minWidth = info.computedWrap('min-width', 'style', '', false, props.previewMode)
    const minHeight = info.computedWrap('min-height', 'style', '', false, props.previewMode)
    const maxWidth = info.computedWrap('max-width', 'style', '', false, props.previewMode)
    const maxHeight = info.computedWrap('max-height', 'style', '', false, props.previewMode)

    const hasInherit = computed(() => {
      return info.hasInheritStyle(
        'style',
        ['width', 'height', 'min-width', 'min-height', 'max-width', 'max-height']
      )
    })

    const hasSet = computed(() => {
      return info.hasSetStyle(
        'style',
        ['width', 'height', 'min-width', 'min-height', 'max-width', 'max-height']
      )
    })
    return {
      ...info,
      hasInherit,
      hasSet,
      currWidth,
      currHeight,
      minWidth,
      minHeight,
      maxWidth,
      maxHeight,
      t
    }
  }
}
</script>
