<template>
  <template v-if="loading">
    <div>{{t('page.loading')}}</div>
  </template>
  <template v-else>
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
            <input v-if="!isSingle" type="checkbox" v-model="checked[data.id]" >
            <input v-if="isSingle" type="radio" :value="data.id" v-model="checkOne" >
          </label>
        </template>
      </APITree>
    </ul>
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
    modelValue: Array,
    isSingle: Boolean
  },
  emits: ['update:modelValue'],
  setup (props: any, context: any) {
    const { t } = useI18n()
    const checked = computed(() => props.modelValue)
    const checkOne = ref('')
    const store = useStore()
    const project = computed(() => store.state.design.project)
    const loading = ref(true)
    const openState = ref(true)
    const apis = ref<Array<APIFolder>>([])
    watch(checkOne, () => {
      context.emit('update:modelValue', checkOne.value)
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
      if (props.isSingle) checkOne.value = props.modelValue
      loadApi()
    })
    return {
      apis,
      openState,
      loading,
      t,
      checked,
      checkOne,
      collapseAll,
      expandAll
    }
  }
}
</script>
