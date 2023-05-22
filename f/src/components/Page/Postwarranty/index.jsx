import React, { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchPage } from '@store'
import { inFinalState, inInitialState } from '@store/helpers'
import { useTemplateContext } from '@contexts/TemplateContext'
import { usePageMeta } from '@hooks'
import classNames from 'classnames'
import { pageClassName } from '../constants'
import PostwarrantyForm from './PostwarrantyForm'

import './style.scss'
import NotFoundPage from '@components/Page/NotFoundPage'

const pageCode = 'service-support/post-warranty'

const FeedbackPage = () => {
  usePageMeta({ pageCode })
  const lang = useTemplateContext().lang
  const dispatch = useDispatch()
  const pageStoreChunk = useSelector(store => store['page'][pageCode])
  const templateSettings = useTemplateContext().templateSettings

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
      <div className="container contacts-content">
        <div className="wrapper">

          <PostwarrantyForm />
        </div>
      </div>
    </div>
  ) : <NotFoundPage />
}

export default FeedbackPage
