import React, { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchSocialLinks } from '@store'
import { inFinalState, inInitialState } from '@store/helpers'
import { useTemplateContext } from '@contexts/TemplateContext'
import { ShareLinks } from '../ShareLinks'

import './style.scss'

const Social = () => {
  const lang = useTemplateContext().lang
  const dispatch = useDispatch()
  const socialLinksStoreChunk = useSelector(store => store['socialLinks'])

  useEffect(() => {
    if (!socialLinksStoreChunk || inInitialState(socialLinksStoreChunk)) {
      dispatch(fetchSocialLinks(lang))
    }
  }, [dispatch, socialLinksStoreChunk])

  if (!inFinalState(socialLinksStoreChunk)) {
    return null
  }

  const { data: socialLinks } = socialLinksStoreChunk

  return socialLinks.length ?
    <>
      <div className="container social-links-container">
        <div className="flex wrapper">
          {
            socialLinks.map((linkEl, index) =>
              <a dangerouslySetInnerHTML={{ __html: linkEl.svgIcon + '<span>' + linkEl.name + '</span>' }} key={index}
                className={`flex-center social-link social-link-${index}`} href={linkEl.link}/>
            )
          }
          <ShareLinks />
        </div>
      </div>
    </> : null
}

export default Social
