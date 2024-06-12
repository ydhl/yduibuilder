const keydownEvents: Record<string, Function> = {}
document.addEventListener('keydown', (keyEvent: KeyboardEvent) => {
  // if (event.key)
  // console.log(keyEvent.key, keyEvent.code, keyEvent.altKey, keyEvent.ctrlKey, keyEvent.shiftKey, keyEvent.metaKey)

  const keys: Array<string> = []
  if (keyEvent.key) {
    keys.push(keyEvent.key.toLowerCase())
  }
  if (keyEvent.altKey)keys.push('alt')
  if (keyEvent.ctrlKey)keys.push('ctrl')
  if (keyEvent.shiftKey)keys.push('shift')
  if (keyEvent.metaKey)keys.push('meta')
  const key = keys.sort().map((a) => { return a.toUpperCase() }).join(' ')
  // console.log(keydownEvents, key)
  if (keydownEvents[key]) {
    keydownEvents[key](keyEvent)
    return false
  }
  return true
})

export default {
  keydown (eventName: Array<string> | string, event: Function) {
    // console.log(eventName)
    let name: string = ''
    if (Array.isArray(eventName)) {
      name = eventName.sort().map((a) => { return a.toUpperCase() }).join(' ')
    } else {
      name = eventName.toUpperCase()
    }
    keydownEvents[name] = event
  }
}
