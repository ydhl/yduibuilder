export default {
  apiBase: 'http://yangzie.localhost/',
  emit: (el: HTMLElement, eventType: string, ...args: any) => {
    const event = new CustomEvent(eventType, { detail: args })
    el.dispatchEvent(event)
  }
}
