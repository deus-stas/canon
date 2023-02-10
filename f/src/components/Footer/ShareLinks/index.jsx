import React, { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchShareLinks } from '@store'
import { inFinalState, inInitialState } from '@store/helpers'
import { useTemplateContext } from '@contexts/TemplateContext'

import './style.scss'

export const ShareLinks = () => {
  const templateSettings = useTemplateContext().templateSettings
  const lang = useTemplateContext().lang
  const dispatch = useDispatch()
  const shareLinksStoreChunk = useSelector(store => store['shareLinks'])

  useEffect(() => {
    if (!shareLinksStoreChunk || inInitialState(shareLinksStoreChunk)) {
      dispatch(fetchShareLinks(lang))
    }
  }, [dispatch, shareLinksStoreChunk])

  if (!inFinalState(shareLinksStoreChunk)) {
    return null
  }

  const { data: shareLinks } = shareLinksStoreChunk

  return shareLinks.length ? (
    <span className="flex-center social-link">
      <svg style={{ marginTop: '2px' }} width="16" height="24" viewBox="0 0 16 24" fill="none"
        xmlns="http://www.w3.org/2000/svg">
        <circle cx="12" cy="7" r="2" fill="white"/>
        <circle cx="12" cy="17" r="2" fill="white"/>
        <circle cx="3" cy="12" r="2" fill="white"/>
        <path d="M12 7L3 12L12 17" stroke="white"/>
      </svg>
      <span onClick={openShareLinks} dangerouslySetInnerHTML={{ __html: templateSettings.textShare }} />
      <span className="flex-column share-links">
        {
          shareLinks.map((linkEl, index) => {
            const link = linkEl.link
              .replace(/{URL}/, window.location.href)
              .replace(/{TITLE}/, encodeURI(document.title))
            return (
              <a dangerouslySetInnerHTML={{ __html: linkEl.svgIcon + '<span>' + linkEl.name + '</span>' }} key={index}
                className={`flex-center social-link social-link-${index}`} href={link}/>
            )
          })
        }
      </span>
    </span>
  ) : null
}

export const ShareLinksPopup = () => {
  const templateSettings = useTemplateContext().templateSettings
  const lang = useTemplateContext().lang
  const dispatch = useDispatch()
  const shareLinksStoreChunk = useSelector(store => store['shareLinks'])

  useEffect(() => {
    if (!shareLinksStoreChunk || inInitialState(shareLinksStoreChunk)) {
      dispatch(fetchShareLinks(lang))
    }
  }, [dispatch, shareLinksStoreChunk])

  if (!inFinalState(shareLinksStoreChunk)) {
    return null
  }

  const { data: shareLinks } = shareLinksStoreChunk
  return shareLinks.length ? (
    <div className="flex-column share-links-popup-container">
      <div onClick={closeShareLinks} className="share-links-overlay"> </div>
      <div className="container flex-column share-links-popup">
        <p className="flex share-links-title">
          <span dangerouslySetInnerHTML={{ __html: templateSettings.textShare }} />
          <button onClick={closeShareLinks} className="close-button close-share-links"> </button>
        </p>
        {
          shareLinks.map((linkEl, index) => {
            const link = linkEl.link
              .replace(/{URL}/, window.location.href)
              .replace(/{TITLE}/, encodeURI(document.title))
            return (
              <a
                dangerouslySetInnerHTML={{ __html: linkEl.svgIcon + '<span>' + linkEl.name + '</span>' }}
                key={index}
                className={`flex-center social-link social-link-${index}`} href={link}/>
            )
          })
        }
      </div>
    </div>
  ) : null
}

const closeShareLinks = () => {
  document.body.style.paddingRight = '0'
  document.body.classList.remove('share-links-popup-is-open')
}

export const openShareLinks = () => {
  document.body.style.paddingRight = (window.innerWidth - document.documentElement.clientWidth) + 'px'
  document.body.classList.add('share-links-popup-is-open')
}
