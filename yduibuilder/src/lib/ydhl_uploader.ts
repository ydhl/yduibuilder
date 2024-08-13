import axios from 'axios'
import ydhl from '@/lib/ydhl'

export declare type MimeType = 'image' | 'excel' | 'font' | 'woff2' | 'woff' | 'ttf'

let input
const fileClick = (event) => {
  if (!input) return
  event.preventDefault()
  event.stopPropagation()

  const clickEvent = new PointerEvent('click', {
    bubbles: false,
    cancelable: false
  })
  input.dispatchEvent(clickEvent)
}
export default function (el: HTMLElement | null,
  mimeType: MimeType | null,
  url: string,
  imageAdded: ((file: File | null) => void) | null,
  imageUploaded: ((file: File, rst: any) => void) | null,
  uploadProgress: ((file: File, progress: number) => void) | null,
  uploadedError: ((file: File, error: any) => void) | null = null) {
  if (!el) return

  const mimeTypes = {
    image: 'image/*',
    excel: 'application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    font: 'font/*',
    woff2: 'font/woff2',
    woff: 'font/woff',
    ttf: 'font/ttf'
  }
  input = document.createElement('input')
  input.type = 'file'
  input.style.display = 'none'
  if (mimeType) input.accept = mimeTypes[mimeType]
  input.onchange = (event: any) => {
    const file = event.target?.files?.[0] || null
    if (!file) return
    if (imageAdded) imageAdded(file)
    const formData = new FormData()
    formData.append('file', file)

    axios({
      data: formData,
      responseType: 'json',
      onUploadProgress: (progressEvent: any) => {
        const progress = progressEvent.total > 0 ? ~~(progressEvent.loaded / progressEvent.total * 100) : 0
        if (uploadProgress) uploadProgress(file, progress)
      },
      method: 'post',
      headers: {
        token: ydhl.getJwt()
      },
      url
    }).then((response) => {
      const rst = response.data
      if (imageUploaded) imageUploaded(file, rst)
    }).catch((err) => {
      if (uploadedError) uploadedError(file, err?.message)
    })
  }
  el.after(input)

  el.removeEventListener('click', fileClick)
  el.addEventListener('click', fileClick)
}
