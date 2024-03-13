import UIBase from '@/components/ui/js/UIBase'
import { computed, toRaw } from 'vue'

export default class Container extends UIBase {
  setup () {
    const superSetup = super.setup()
    const store = this.store
    /**
     * 是否是Row 容器:PC 端上层容器是container或则移动端的最上层的container都是row
     */
    const isRow = computed<boolean>(() => {
      const { parentConfig } = store.getters.getUIItemInPage(this.props.uiconfig.meta.id, this.props.pageid)
      const rawParentConfig = toRaw(parentConfig)
      if (!rawParentConfig) return false
      if (superSetup.endKind.value === 'mobile') {
        if (rawParentConfig.type !== 'Container') return true
        return false
      }
      if (rawParentConfig.type === 'Container') return true
      return false
    })
    /**
     * 是否是Col 容器:PC 端是container>row>col, 移动端是row > col
     */
    const isCol = computed<boolean>(() => {
      const { parentConfig } = store.getters.getUIItemInPage(this.props.uiconfig.meta.id, this.props.pageid)
      const rawParentConfig = toRaw(parentConfig)
      if (!rawParentConfig) return false
      const parentOfParent = store.getters.getUIItemInPage(rawParentConfig.meta.id, this.props.pageid)
      if (superSetup.endKind.value === 'mobile') {
        if (rawParentConfig.type === 'Container' && parentOfParent.parentConfig.type !== 'Container') return true
        return false
      }
      if (rawParentConfig.type !== 'Container') return false
      if (parentOfParent.parentConfig.type === 'Container') return true
      return false
    })
    return {
      ...superSetup,
      isRow,
      isCol
    }
  }
}
