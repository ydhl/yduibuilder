<template>
  <div v-if="loading" class="vh-100 d-flex align-items-center justify-content-center">
    <div>{{t('page.loading')}}</div>
  </div>
  <template v-else-if="apis?.length > 0">
    <div class="justify-content-between d-flex btn btn-sm btn-white w-100 align-items-center">
      <div><i class="iconfont icon-api"></i> {{t('common.api')}}</div>
      <div class="btn-group btn-group-sm">
        <button class="btn btn-white btn-xs" type="button" v-if="!openState" @click.stop="expandAll"><i class="iconfont icon-expandall"></i></button>
        <button class="btn btn-white btn-xs" type="button" v-if="openState" @click.stop="collapseAll"><i class="iconfont icon-collapseall"></i></button>
      </div>
    </div>
    <ul class="tree">
      <APITree :tree="tree" v-for="(tree, index) in apis" :open="openState" :indent="1" :key="index">
        <template #leaf="{data}">
          <label class="d-flex justify-content-center align-items-center pe-3">
            <input v-if="!isSingle" @click="checkedOneAPI=data" type="checkbox" v-model="checked[data.id]" >
            <input v-if="isSingle" @click="checkedOneAPI=data" type="radio" :value="data.id" v-model="checkAPI" >
          </label>
        </template>
      </APITree>
    </ul>
  </template>
  <template v-else>
    <div class="text-muted">{{t('event.bindAPIEmptyTip')}}</div>
  </template>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, onMounted, ref, watch } from 'vue'
import APITree from '@/components/common/APITree.vue'
import { APIFolder } from '@/store/model'
import ydhl from '@/lib/ydhl'
import { useStore } from 'vuex'

export default {
  name: 'ImportAPI',
  components: { APITree },
  props: {
    modelValue: Object,
    isSingle: Boolean
  },
  emits: ['checkAPI'],
  setup (props: any, context: any) {
    const { t } = useI18n()
    const checked = computed(() => props.modelValue) // 这部分是由于modelValue就是一个对象引用，所以直接更改了，没有update事件update:modelValue
    const checkAPI = ref('')
    const store = useStore()
    const project = computed(() => store.state.design.project)
    const checkedOneAPI = ref({})
    const loading = ref(true)
    const openState = ref(true)
    const apis = ref<Array<APIFolder>>([])
    watch(checkAPI, () => {
      context.emit('checkAPI', checkedOneAPI.value)
    })

    const collapseAll = () => {
      openState.value = false
    }
    const expandAll = () => {
      openState.value = true
    }
    const loadApi = () => {
      ydhl.get('api/folder/all.json', { projectId: project.value.id }, (rst: any) => {
        loading.value = false
        apis.value = rst.data || []
      }, 'json')
    }
    onMounted(() => {
      if (props.isSingle) checkAPI.value = props.modelValue
      loadApi()
    })
    return {
      apis,
      openState,
      loading,
      t,
      checked,
      checkAPI,
      checkedOneAPI,
      collapseAll,
      expandAll
    }
  }
}
</script>
