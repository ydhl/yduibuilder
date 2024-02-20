
import { computed } from 'vue'
import UIBase from '@/components/ui/js/UIBase'

export default class Radio extends UIBase {
  setup () {
    const props = this.props
    const values = computed(() => {
      if (!props.uiconfig.meta.values || props.uiconfig.meta.values.length === 0) {
        return [{ text: 'sample', value: '1' }]
      }
      return props.uiconfig.meta.values
    })

    return {
      values,
      ...super.setup()
    }
  }
}
