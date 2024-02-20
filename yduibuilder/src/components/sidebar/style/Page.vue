<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.page') }}</div>
  <div class="style-body d-none">
    <template v-if="!isPopup">
      <div class="row">
        <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('page.file') }}</label>
        <div class="col-sm-9">
          <input class="form-control form-control-sm" type="text" :placeholder="t('page.fileTip')" v-model="myFile">
        </div>
      </div>
      <div class="row" v-if="project.rewrite">
        <label class="col-sm-3 col-form-label text-end">{{ t('page.url') }}</label>
        <div class="col-sm-9">
          <input class="form-control form-control-sm" type="text" :placeholder="t('page.urlTip')" v-model="myURL">
        </div>
      </div>
      <div class="row">
        <label class="col-sm-3 col-form-label text-end">{{ t('page.homePage') }}</label>
        <div class="col-sm-9 d-flex align-items-center">
          <label class=" form-check-label text-truncate d-block">
            <input type="checkbox" v-model="isHomePage" value="1">&nbsp;
          </label>
        </div>
      </div>
    </template>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'
import { computed } from 'vue'
import ydhl from '@/lib/ydhl'

export default {
  name: 'StylePage',
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const store = useStore()
    const currPage = computed(() => store.state.design.page)
    const project = computed(() => store.state.design.project)
    const myFile = info.computedWrap('file', 'custom')
    const myURL = info.computedWrap('url', 'custom')
    const isHomePage = computed({
      get () {
        return info.getMeta('isHomePage', 'custom')
      },
      set (v) {
        info.setMeta('isHomePage', v, 'custom')

        ydhl.post('api/update/homepage', { pageid: v ? currPage.value.meta?.id : '', projectid: project.value.id }, [], null, 'json')
      }
    })

    const isPopup = computed(() => store.state.design.page.pageType === 'popup' || store.state.design.page.pageType === 'subpage')

    return {
      ...info,
      t,
      currPage,
      project,
      isHomePage,
      myFile,
      isPopup,
      myURL
    }
  }
}
</script>
