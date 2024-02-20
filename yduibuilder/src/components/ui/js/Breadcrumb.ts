import { computed } from 'vue'
import UIBase from '@/components/ui/js/UIBase'

export default class Breadcrumb extends UIBase {
  setup () {
    const props = this.props
    const superSetup = super.setup()
    const values = computed(() => {
      if (!props.uiconfig.meta.values || props.uiconfig.meta.values.length === 0) {
        return [{ text: 'Page A', value: '#1' }, { text: 'Page B', value: '#2' }]
      }
      return props.uiconfig.meta.values
    })
    return {
      ...superSetup,
      values
    }
  }
}
