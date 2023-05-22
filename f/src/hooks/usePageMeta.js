import { useEffect } from 'react'
import { usePageContextValue } from '@contexts/PageContext'

const titleElement = document.head.querySelector('title')
const descriptionMetaElement = document.head.querySelector('meta[name="description"]')
const keywordsMetaElement = document.head.querySelector('meta[name="keywords"]')
const prerenderStatusCode = document.head.querySelector('meta[name="prerender-status-code"]')

const ogTitleElement = document.head.querySelector('meta[property="og:title"]')
const ogDescriptionElement = document.head.querySelector('meta[property="og:url"]')
const ogUrlElement = document.head.querySelector('meta[property="og:url"]')
const ogImageElement = document.head.querySelector('meta[property="og:image"]')
const ogImageWidthElement = document.head.querySelector('meta[property="og:image:width"]')
const ogImageHeightElement = document.head.querySelector('meta[property="og:image:height"]')

const updateMetaContentValue = (targetElement, value) => {
  if (!targetElement.dataset.defaultContent) {
    targetElement.dataset.defaultContent = targetElement.getAttribute('content')
  }

  targetElement.setAttribute('content', value || targetElement.dataset.defaultContent)
}

export const updatePageMeta =
    ({ description, keywords, statusCode, title, ogTitle, ogDescription, ogImage, ogUrl }) => {
      titleElement.innerHTML = title

      if (description) {
        descriptionMetaElement.setAttribute('content', description)
        titleElement.parentElement.insertBefore(descriptionMetaElement, titleElement)
      } else if (descriptionMetaElement.parentElement) {
        descriptionMetaElement.parentElement.removeChild(descriptionMetaElement)
      }

      if (keywords) {
        keywordsMetaElement.setAttribute('content', keywords)
        titleElement.parentElement.insertBefore(keywordsMetaElement, titleElement)
      } else if (keywordsMetaElement.parentElement) {
        keywordsMetaElement.parentElement.removeChild(keywordsMetaElement)
      }

      ogUrl = ogUrl || window.location.href
      ogUrl = ogUrl.split('#')[0]

      updateMetaContentValue(ogTitleElement, ogTitle || title)
      updateMetaContentValue(ogDescriptionElement, ogDescription || description)
      updateMetaContentValue(ogUrlElement, ogUrl)

      if (ogImage?.src) {
        updateMetaContentValue(ogImageElement, ogImage.src)
        updateMetaContentValue(ogImageWidthElement, ogImage.width)
        updateMetaContentValue(ogImageHeightElement, ogImage.height)
      } else {
        updateMetaContentValue(ogImageElement, false)
        updateMetaContentValue(ogImageWidthElement, false)
        updateMetaContentValue(ogImageHeightElement, false)
      }

      prerenderStatusCode.setAttribute('content', statusCode ? statusCode : 200)
    }

export default ({ pageCode }) => {
  const page = usePageContextValue(pageCode)

  useEffect(() => {
    if (page) {
      const { seo: meta = {} } = page
      meta.statusCode = pageCode === 'not-found' ? 404 : 200

      if (meta.statusCode === 404) {
        document.documentElement.classList.add('page-not-found')
      } else {
        document.documentElement.classList.remove('page-not-found')
      }

      if (meta) {
        updatePageMeta(meta)
      }
    }
  }, [pageCode, page])
}
