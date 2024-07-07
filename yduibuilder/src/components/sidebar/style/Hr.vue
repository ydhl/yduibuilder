<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.hr') }}</div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.form.title') }}</label>
      <div class="col-sm-9">
        <input class="form-control form-control-sm" type="text" v-model="text">
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('common.style') }}</label>
      <div class="col-sm-9">
        <div class="dropdown">
          <button style="height:30px" class="btn btn-light btn-sm d-flex align-items-center justify-content-between dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <span :style="hrStyle"></span>
          </button>
          <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
            <li v-for="(type, index) in ['solid','dotted','dashed','double']" :key="index">
              <a href="javascript:;" @click="style = type" class="dropdown-item p-3"><div :style="`border-top:3px ${type} #000 `"></div></a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'

export default {
  name: 'StyleButton',
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const text = computed({
      get () {
        return info.getMeta('value') || ''
      },
      set (v) {
        info.setMeta('value', v)
      }
    })
    const style = computed({
      get () {
        return info.getMeta('style', 'custom') || 'solid'
      },
      set (v) {
        info.setMeta('style', v, 'custom')
      }
    })
    const hrStyle = computed(() => {
      return `border-top:3px ${style.value} #000 ;width:100px;`
    })
    return {
      ...info,
      text,
      hrStyle,
      style,
      t
    }
  }
}
</script>
