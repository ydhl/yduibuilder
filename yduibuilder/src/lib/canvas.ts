let isDrawing = false
let isStartDrag = false
let ctx: any = null
let srcX: any = null
let srcY: any = null
let bodyRect: any = null
let drawFromId: any = null
function isDrawline () {
  return isDrawing
}
function mousemove (x, y) {
  // console.log(isDrawing, ctx)
  if (!isDrawing || !ctx) return
  ctx.clearRect(0, 0, bodyRect.width, bodyRect.height)
  ctx.beginPath()
  ctx.arc(srcX, srcY, 1, 0, Math.PI * 2, true) // 起点圆
  ctx.moveTo(srcX, srcY)
  ctx.fillStyle = '#0051ff'
  ctx.fill()
  ctx.lineTo(x, y)
  // 终点三角形
  // ctx.moveTo(mousemove.clientX, mousemove.clientY)
  // ctx.lineTo(100, 75)
  // ctx.lineTo(100, 25)
  // ctx.fill()
  ctx.stroke()
}
function mouseoverInIframe (x, y) {
  mousemove(x, y)
}
function getDrawFromId () {
  return drawFromId
}
function drawMousemove (event) {
  mousemove(event.clientX, event.clientY)
}
function stopDrawline () {
  isDrawing = false
  isStartDrag = false
  drawFromId = null
  const canvas = document.getElementById('canvas')
  if (canvas) canvas.remove()
}

function startDrawline (screenX: any, screenY: any, fromid: string) {
  if (isDrawing) return
  drawFromId = fromid
  const canvas = document.createElement('canvas')
  bodyRect = document.body.getBoundingClientRect()
  canvas.style.cssText = 'position:fixed;left: 0px;right: 0px;top: 0px;bottom: 0px;z-index: 99999;pointer-events: none'
  canvas.width = bodyRect.width
  canvas.id = 'canvas'
  canvas.height = bodyRect.height
  document.body.insertBefore(canvas, document.getElementById('app'))
  // 获取canvas的逆转转换矩阵
  ctx = canvas.getContext('2d')
  ctx.strokeStyle = '#0051ff'
  ctx.lineWidth = 1
  ctx.lineJoin = 'round'
  ctx.lineCap = 'round'
  isDrawing = true
  srcX = screenX
  srcY = screenY

  document.body.removeEventListener('mousemove', drawMousemove)
  document.body.removeEventListener('mouseup', stopDrawline)
  document.body.addEventListener('mousemove', drawMousemove)
  // setTimeout(function () {
  document.body.addEventListener('mouseup', stopDrawline)
  // }, 1000)
}
function setStartDrag (bool: boolean) {
  isStartDrag = bool
}
function isDrag () {
  return isStartDrag
}

export default {
  startDrawline,
  mouseoverInIframe,
  isDrawline,
  stopDrawline,
  setStartDrag,
  isDrag,
  getDrawFromId
}
