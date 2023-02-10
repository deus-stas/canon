import React, { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchPage } from '@store'
import { inFinalState, inInitialState } from '@store/helpers'
import classNames from 'classnames'
import { pageClassName } from '../constants'
import { useTemplateContext } from '@contexts/TemplateContext'
import { usePageMeta } from '@hooks'

import './style.scss'
import NotFoundPage from '@components/Page/NotFoundPage'

const pageCode = 'privacy'

const PrivacyPage = () => {
  usePageMeta({ pageCode })
  const lang = useTemplateContext().lang
  const dispatch = useDispatch()
  const pageStoreChunk = useSelector(store => store['page'][pageCode])

  useEffect(() => {
    if (!pageStoreChunk || inInitialState(pageStoreChunk)) {
      dispatch(fetchPage(pageCode, lang))
    }
    document.documentElement.scrollTop = 0
  }, [dispatch, pageStoreChunk])

  if (!inFinalState(pageStoreChunk)) {
    return null
  }

  const { data: pageData } = pageStoreChunk

  return pageData ? (
    <div className={classNames(`${pageClassName} ${pageCode}-${pageClassName}`)}>
      <div className="container">
        <h1 className="title" dangerouslySetInnerHTML={{ __html: pageData.name }}/>
      </div>
      <div className="container privacy-content">
        <div className="wrapper" dangerouslySetInnerHTML={{ __html: pageData.detailText }} />
      </div>
    </div>
  ) : <NotFoundPage />
}

export default PrivacyPage
