<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.image') }}</div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.image.objectFit') }}</label>
      <div class="col-sm-9">
        <select class="form-select form-select-sm" v-model="fit">
          <option :value="item" v-for="(item) in fits" :key="item" :selected="item===fit">{{item}}</option>
        </select>
        <small>{{ t('style.image.objectFitTip') }}</small>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.image.objectPosition') }}</label>
      <div class="col-sm-9">
        <div class="mt-2 d-flex">
          <Position v-model="position"></Position>
          <div class="ms-1 flex-grow-1">
            <div class="input-group input-group-sm mb-1">
              <span class="input-group-text">X</span>
              <input type="text" class="form-control" placeholder="X Position" v-model="positionX">
            </div>
            <div class="input-group input-group-sm mb-1">
              <span class="input-group-text">Y</span>
              <input type="text" class="form-control" placeholder="Y Position" v-model="positionY">
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.image.src') }}</label>
      <div class="col-sm-9">
        <Upload v-model="image" :project-id="projectId" width="50px" height="50px"></Upload>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import Upload from '@/components/common/Upload.vue'
import { computed, ref } from 'vue'
import Position from '@/components/common/Position.vue'
import { useStore } from 'vuex'

export default {
  name: 'StyleImage',
  components: { Position, Upload },
  setup (props: any, context: any) {
    const info = initUI()
    // const selectedUIItem = info.selectedUIItem
    const { t } = useI18n()
    const store = useStore()
    const fits = ref(['fill', 'contain', 'cover', 'none', 'scale-down', 'initial', 'inherit'])
    const fit = info.computedWrap('object-fit', 'style', 'fill')

    const position = computed<Array<string>>({
      get () {
        const size = info.getMeta('object-position', 'style')
        return size?.split(' ') || []
      },
      set (v) {
        info.setMeta('object-position', v.join(' '), 'style')
      }
    })
    const positionX = computed<Array<string>>({
      get () {
        const size = info.getMeta('object-position', 'style')
        return size?.split(' ')[0] || ''
      },
      set (v) {
        const size = info.getMeta('object-position', 'style')
        const old = size?.split(' ') || []
        old[0] = v
        info.setMeta('object-position', old.join(' '), 'style')
      }
    })
    const positionY = computed<Array<string>>({
      get () {
        const size = info.getMeta('object-position', 'style')
        return size?.split(' ')[1] || ''
      },
      set (v) {
        const size = info.getMeta('object-position', 'style')
        const old = size?.split(' ') || []
        if (old.length < 2) {
          old[0] = '0px'
        }
        old[1] = v
        info.setMeta('object-position', old.join(' '), 'style')
      }
    })
    const image = computed({
      get () {
        const files = info.getMeta('value', 'files') || []
        return { url: info.getMeta('value'), id: files[0]?.id, name: files[0]?.name }
      },
      set (v: any) {
        info.setMeta('value', v.url)
        info.setMeta('value', [{ id: v.id, name: v.name }], 'files')
      }
    })
    const projectId = computed(() => store.state.design.project.id)
    return {
      ...info,
      image,
      projectId,
      t,
      fits,
      fit,
      position,
      positionX,
      positionY
    }
  }
}
</script>
