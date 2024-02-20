import UIBase from '@/components/ui/js/UIBase'

export default class Holder extends UIBase {
  setup () {
    const superSetup = super.setup()

    return {
      ...superSetup
    }
  }
}
