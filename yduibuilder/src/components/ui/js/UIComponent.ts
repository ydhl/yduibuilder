import UIBase from '@/components/ui/js/UIBase'

export default class UIComponent extends UIBase {
  setup () {
    const superSetup = super.setup()
    return {
      ...superSetup
    }
  }
}
