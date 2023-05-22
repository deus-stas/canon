import { useEffect } from 'react'

const openModal = url => {
  const el = document.querySelector('.modal_overlay')
  if (el) {
    return
  }
  // console.log('openModal')
  let html = '<div class="modal_overlay" onclick="window.event.stopPropagation();closeVideoModal();">' +
      '<div class="modal_row"><div class="modal">'
  html += `<iframe src="${url}"></iframe>`
  html += '<div class="close" onclick="window.event.stopPropagation();closeVideoModal();"></div></div></div></div>'
  document.querySelector('body').innerHTML += html
}
const init = () => {
  const links = document.querySelectorAll('.btn.btn-video[data-url]')
  const clickHandler = event => {
    const item = event.target
    openModal(item.getAttribute('data-url'))
  }
  links.forEach(item => {
    item.removeEventListener('click', clickHandler, false)
    item.addEventListener('click', clickHandler, false)
  })
  // console.log('init video', links)
}

window.closeVideoModal = () => {
  const el = document.querySelector('.modal_overlay')
  el?.remove()
  init()
}

export default pageCode => {
  useEffect(() => {
    if (pageCode) {
      setTimeout(() => init(), 1000)
    }
  }, [pageCode])
}
