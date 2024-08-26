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
      <template v-if="!headless">
        <div class="col-sm-9 offset-sm-3">
          <div class="input-group input-group-sm">
            <label class="input-group-text">{{t("style.predefinedClass")}}</label>
            <select class="form-select form-select-sm" v-model="headerCss">
              <option :key="value" :value="value" v-for="value in cssMap['backgroundTheme']">{{value}}</option>
            </select>
          </div>
          <ColorPicker css="form-control" v-model="headerColor"></ColorPicker>
        </div>
        <div class="col-sm-9 offset-sm-3 mt-1">
          <div class="input-group input-group-sm">
            <label class="input-group-text">{{t("style.table.headerRow")}}</label>
            <input type="number" min="1" max="10" class="form-control-sm form-control" v-model="headerRow">
          </div>
        </div>
      </template>
    </div>

    <div class="row mt-2">
      <div class="col-sm-3 text-end"> {{ t('style.table.body') }}</div>
      <template v-if="hasBondValueList">
        <div class="col-sm-9">
          {{t('style.table.noBodyWhenBoundValueList')}}
        </div>
      </template>
      <template v-else>
        <div class="col-sm-9">
          <div class="input-group input-group-sm">
            <label class="input-group-text">{{t("style.table.columnCount")}}</label>
            <input type="number" min="1" class="form-control form-control-sm" v-model="columnCount">
          </div>
        </div>
        <div class="col-sm-9 offset-sm-3 mt-1">
          <div class="input-group input-group-sm">
            <label class="input-group-text">{{t("style.table.bodyRow")}}</label>
            <input type="number" min="1" class="form-control-sm form-control" v-model="bodyRow">
          </div>
        </div>
      </template>
    </div>
    <div class="row mt-2">
      <div class="col-sm-3 text-end"> {{ t('style.table.footer') }}</div>
      <div class="col-sm-9">
        <label class=" form-check-label text-truncate d-block">
          <input type="checkbox" v-model="footless" value="1"> {{ t('style.table.footless') }}</label>
      </div>
      <template v-if="!footless">
        <div class="col-sm-9 offset-sm-3">
          <div class="input-group input-group-sm">
            <label class="input-group-text">{{t("style.predefinedClass")}}</label>
            <select class="form-select form-select-sm" v-model="footerCss">
              <option :key="value" :value="value" v-for="value in cssMap['backgroundTheme']">{{value}}</option>
            </select>
          </div>
          <ColorPicker css="form-control" v-model="footerColor"></ColorPicker>
        </div>
        <div class="col-sm-9 offset-sm-3 mt-1">
          <div class="input-group input-group-sm">
            <label class="input-group-text">{{t("style.table.footerRow")}}</label>
            <input type="number" min="1" max="10" class="form-control-sm form-control" v-model="footerRow">
          </div>
        </div>
      </template>
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
        <select class="mb-1 form-select form-select-sm" v-model="verticalAlignment">
          <option :value="value" v-for="(name,value) in cssMap['verticalAlignment']" :key="value">{{ name }}</option>
        </select>
      </div>
    </div>
    <div class="row mt-2">
      <div class="col-sm-3 text-end"> {{ t('style.table.horizontalAlignment') }}</div>
      <div class="col-sm-9">
        <select class="mb-1 form-select form-select-sm" v-model="horizontalAlignment">
          <option :value="value" v-for="(name,value) in cssMap['textAlignment']" :key="value">{{ name }}</option>
        </select>
      </div>
    </div>
    <div class="row mt-2">
      <div class="col-sm-3 text-end"> {{ t('style.table.grid') }}</div>
      <div class="col-sm-9">
        <select class="mb-1 form-select form-select-sm" v-model="grid">
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
import { computed, watch } from 'vue'
import ColorPicker from '@/components/common/ColorPicker.vue'
import Upload from '@/components/common/Upload.vue'
import { useStore } from 'vuex'
import ydhl from '@/lib/ydhl'
import { YDJSStatic } from '@/lib/ydjs'
declare const YDJS: YDJSStatic

export default {
  name: 'StyleTable',
  components: { Upload, ColorPicker },
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const store = useStore()

    const headless = info.computedWrap('headless', 'custom', false)
    const stripedRow = info.computedWrap('stripedRow', 'custom', false)
    const hoverableRow = info.computedWrap('hoverableRow', 'custom', false)
    const grid = info.computedWrap('grid', 'custom', 'normal')
    const footless = info.computedWrap('footless', 'custom', false)
    const small = info.computedWrap('small', 'custom', false)
    const horizontalAlignment = info.computedWrap('textAlignment', 'css', '')
    const verticalAlignment = info.computedWrap('verticalAlignment', 'css', '')
    const headerCss = info.computedWrap('header', 'css')
    const headerColor = info.computedWrap('header', 'custom')
    const footerCss = info.computedWrap('footer', 'css')
    const footerColor = info.computedWrap('footer', 'custom')
    const columnCount = info.computedWrap('columnCount', 'custom', 2)
    const footerRow = info.computedWrap('footerRow', 'custom', 1)
    const headerRow = info.computedWrap('headerRow', 'custom', 1)
    const bodyRow = info.computedWrap('bodyRow', 'custom', 1)
    const project = computed(() => store.state.design.project)
    const hasBondValueList = computed(() => {
      return !!info.selectedUIItem.value?.dataOut?.VALUELIST
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
    const currExcelID = computed(() => {
      const files = info.getMeta('datasource', 'files') || []
      return files[0]?.id
    })
    watch(currExcelID, (newV, oldV) => {
      if (oldV !== newV) parseExcel(newV)
    })

    const parseExcel = (fid) => {
      if (!fid) return
      const loadingid = YDJS.loading(t('page.loading'))
      ydhl.get('api/parseexcel', { pid: project.value.id, fid }, (rst) => {
        YDJS.hide_dialog(loadingid)
        if (!rst || !rst.success) {
          YDJS.alert(rst?.msg || t('table.canNotParseExcel'), 'Oops')
          return
        }
        const data = rst.data.data || []
        const config = rst.data.config || {}
        // 导入excel后默认是无头无脚的
        headless.value = true
        footless.value = true
        bodyRow.value = data.length
        columnCount.value = data?.[0].length
        info.setMeta('data', data, 'custom')
        info.setMeta('columnConfig', config, 'custom')
      }, 'json')
    }
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
      bodyRow,
      footerRow,
      headerRow,
      columnCount,
      hoverableRow,
      currExcelFile,
      hasBondValueList,
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
