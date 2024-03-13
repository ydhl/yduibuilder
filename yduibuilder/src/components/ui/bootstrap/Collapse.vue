<template>
  <div :draggable='draggable'
       :class="[dragableCss, myCss]" :style="myStyleText" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid">
    <template v-if="!uiconfig.items?.length">
      <div class="card">
        <div class="card-header" :id="uiconfig.meta.id+'heading0'">
          <h2 class="mb-0">
            <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse"
                    :data-target="`#${uiconfig.meta.id}collapse0`" aria-expanded="true" :aria-controls="`${uiconfig.meta.id}collapse0`">
              According Header
            </button>
          </h2>
        </div>

        <div :id="`${uiconfig.meta.id}collapse0`" class="collapse show" :aria-labelledby="uiconfig.meta.id+'heading0'" :data-parent="'#'+uiconfig.meta.id">
          <div class="card-body">
            According body, you can add item from Style Panel
          </div>
        </div>
      </div>
    </template>
    <template v-else>
      <template v-for="(subpage, index) in uiconfig.items" :key="index">
        <div class="card">
          <div class="card-header" :id="uiconfig.meta.id+'heading'+index">
            <h2 class="mb-0">
              <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse"
                      :data-target="`#${uiconfig.meta.id}collapse${index}`" aria-expanded="true" :aria-controls="`${uiconfig.meta.id}collapse${index}`">
                {{subpage.meta.title}}
              </button>
            </h2>
          </div>

          <div :id="`${subpage.meta.id}collapse${index}`"
               :class="{'collapse': true, 'show': (!uiconfig.meta.custom?.activeItem && index ==0) || uiconfig.meta.custom?.activeItem==index}" :aria-labelledby="subpage.meta.id+'heading'+index" :data-parent="'#'+subpage.meta.id">
            <div class="card-body">
              <UIBase v-for="(item, index) in subpage.items" :key="index" :is-readonly="true" :is-lock="myIsLock" :uiconfig="item" :pageid="pageid"></UIBase>
            </div>
          </div>
        </div>
      </template>
    </template>
  </div>
</template>

<script lang="ts">
import Collapse from '@/components/ui/js/Collapse'
import { computed } from 'vue'
import UIBase from '@/components/ui/UIBase.vue'
import { useStore } from 'vuex'

export default {
  name: 'Bootstrap_Collapse',
  components: { UIBase },
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const store = useStore()
    const collapse = new Collapse(props, context, store)
    const setup = collapse.setup()
    const myCss = computed(() => {
      const uicss = collapse.getUICss()
      const arr: any = Object.values(uicss)
      arr.push('accordion')
      return arr
    })
    const myStyleText = computed(() => {
      const style = collapse.appendImportant(collapse.getUIStyle())
      return style
    })
    return {
      myCss,
      myStyleText,
      ...setup
    }
  }
}

</script>
