
import { computed } from 'vue'
import UIBase from '@/components/ui/js/UIBase'

export default class Nav extends UIBase {
  setup () {
    const store = this.store
    const setup = super.setup()
    const props = this.props

    const parentIsCard = computed(() => {
      const { parentConfig } = store.getters.getUIItemInPage(props.uiconfig.meta.id, props.pageid)
      return parentConfig.type.toLowerCase() === 'card'
    })
    const values = computed(() => {
      if (!props.uiconfig.meta.values || props.uiconfig.meta.values.length === 0) {
        return [{ text: 'Sample 1', value: '1' }, { text: 'Sample 2', value: '2', checked: true }]
      }
      return props.uiconfig.meta.values
    })
    return {
      ...setup,
      parentIsCard,
      values
    }
  }
}
