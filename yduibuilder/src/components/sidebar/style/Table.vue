<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.table') }}</div>
  <div class="style-body d-none">
    <div class="row">
      <div class="col-sm-3 text-end"> {{ t('style.table.header') }}</div>
      <div class="col-sm-9">
        <label class=" form-check-label text-truncate d-block">
          <input type="checkbox" v-model="headless" value="1"> {{ t('style.table.headless') }}
        </label>
      </div>
      <div class="col-sm-9 offset-sm-3" v-if="!headless">
        <div class="input-group input-group-sm">
          <label class="input-group-text">{{t("style.predefinedClass")}}</label>
          <select class="form-select" v-model="headerCss">
            <option :key="value" :value="value" v-for="value in cssMap['backgroundTheme']">{{value}}</option>
          </select>
          <ColorPicker css="form-control" v-model="headerColor"></ColorPicker>
        </div>
      </div>
    </div>
    <div class="row mt-2">
      <div class="col-sm-3 text-end"> {{ t('style.table.footer') }}</div>
      <div class="col-sm-9">
        <label class=" form-check-label text-truncate d-block">
          <input type="checkbox" v-model="footless" value="1"> {{ t('style.table.footless') }}</label>
      </div>
      <div class="col-sm-9 offset-sm-3" v-if="!footless">
        <div class="input-group input-group-sm">
          <label class="input-group-text">{{t("style.predefinedClass")}}</label>
          <select class="form-select" v-model="footerCss">
            <option :key="value" :value="value" v-for="value in cssMap['backgroundTheme']">{{value}}</option>
          </select>
          <ColorPicker css="form-control" v-model="footerColor"></ColorPicker>
        </div>
      </div>
    </div>
    <div class="row mt-2">
      <div class="col-sm-3 text-end"> {{ t('style.table.accented') }}</div>
      <div class="col-sm-9">
        <label class=" form-check-label text-truncate d-block">
          <input type="checkbox" v-model="stripedRow" value="1"> {{ t('style.table.stripedRow') }}
        </label>
        <label class=" form-check-label text-truncate d-block">
          <input type="checkbox" v-model="hoverableRow" value="1"> {{ t('style.table.hoverableRow') }}
        </label>
        <label class=" form-check-label text-truncate d-block">
          <input type="checkbox" v-model="small" value="1"> {{ t('style.table.small') }}
        </label>
      </div>
    </div>
    <div class="row mt-2">
      <div class="col-sm-3 text-end"> {{ t('style.table.verticalAlignment') }}</div>
      <div class="col-sm-9">
        <select class="mb-1 form-control form-control-sm" v-model="verticalAlignment">
          <option :value="value" v-for="(name,value) in cssMap['verticalAlignment']" :key="value">{{ name }}</option>
        </select>
      </div>
    </div>
    <div class="row mt-2">
      <div class="col-sm-3 text-end"> {{ t('style.table.horizontalAlignment') }}</div>
      <div class="col-sm-9">
        <select class="mb-1 form-control form-control-sm" v-model="horizontalAlignment">
          <option :value="value" v-for="(name,value) in cssMap['textAlignment']" :key="value">{{ name }}</option>
        </select>
      </div>
    </div>
    <div class="row mt-2">
      <div class="col-sm-3 text-end"> {{ t('style.table.grid') }}</div>
      <div class="col-sm-9">
        <select class="mb-1 form-control form-control-sm" v-model="grid">
          <option value="bordered">{{ t('style.table.bordered') }}</option>
          <option value="borderless">{{ t('style.table.borderless') }}</option>
          <option value="normal">{{ t('style.table.normal') }}</option>
        </select>
      </div>
    </div>
    <div class="row mt-2">
      <div class="col-sm-3 text-end"> {{ t('style.table.data') }}</div>
      <div class="col-sm-9">
        <Upload type="excel" width="100%" :project-id="projectId" v-model="currExcelFile"></Upload>
        <small class="text-muted">{{ t('style.table.import') }}</small>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'
import ColorPicker from '@/components/common/ColorPicker.vue'
import Upload from '@/components/common/Upload.vue'
import { useStore } from 'vuex'

export default {
  name: 'StyleTable',
  components: { Upload, ColorPicker },
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const store = useStore()
    const headless = computed({
      get: () => {
        return info.getMeta('headless', 'custom') || false
      },
      set: (v) => {
        info.setMeta('headless', v, 'custom')
      }
    })
    const stripedRow = computed({
      get: () => {
        return info.getMeta('stripedRow', 'custom')
      },
      set: (v) => {
        info.setMeta('stripedRow', v, 'custom')
      }
    })
    const hoverableRow = computed({
      get: () => {
        return info.getMeta('hoverableRow', 'custom')
      },
      set: (v) => {
        info.setMeta('hoverableRow', v, 'custom')
      }
    })

    const grid = computed({
      get () {
        return info.getMeta('grid', 'custom') || ''
      },
      set (v) {
        info.setMeta('grid', v, 'custom')
      }
    })
    const currExcelFile = computed({
      get () {
        const files = info.getMeta('datasource', 'files') || []
        return { id: files[0]?.id, name: files[0]?.name }
      },
      set (v: any) {
        info.setMeta('datasource', [{ id: v.id, name: v.name }], 'files')
      }
    })
    const footless = computed({
      get: () => {
        return info.getMeta('footless', 'custom') || false
      },
      set: (v) => {
        info.setMeta('footless', v, 'custom')
      }
    })

    const small = computed({
      get: () => {
        return info.getMeta('small', 'custom') || false
      },
      set: (v) => {
        info.setMeta('small', v, 'custom')
      }
    })
    const horizontalAlignment = computed({
      get: () => {
        return info.getMeta('textAlignment', 'css') || ''
      },
      set: (v) => {
        info.setMeta('textAlignment', v, 'css')
      }
    })
    const verticalAlignment = computed({
      get: () => {
        return info.getMeta('verticalAlignment', 'css') || ''
      },
      set: (v) => {
        info.setMeta('verticalAlignment', v, 'css')
      }
    })
    const headerCss = computed({
      get () {
        return info.getMeta('header', 'css')
      },
      set (v) {
        info.setMeta('header', v, 'css')
      }
    })
    const headerColor = computed({
      get () {
        return info.getMeta('header', 'custom')
      },
      set (v) {
        info.setMeta('header', v, 'custom')
      }
    })
    const footerCss = computed({
      get () {
        return info.getMeta('footer', 'css')
      },
      set (v) {
        info.setMeta('footer', v, 'css')
      }
    })
    const footerColor = computed({
      get () {
        return info.getMeta('footer', 'custom')
      },
      set (v) {
        info.setMeta('footer', v, 'custom')
      }
    })
    const projectId = computed(() => store.state.design.project.id)
    return {
      ...info,
      footless,
      headless,
      headerCss,
      footerCss,
      headerColor,
      footerColor,
      stripedRow,
      hoverableRow,
      currExcelFile,
      grid,
      t,
      small,
      verticalAlignment,
      horizontalAlignment,
      projectId
    }
  }
}
</script>
