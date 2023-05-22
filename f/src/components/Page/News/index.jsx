import React, { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchPage } from '@store'
import { inFinalState, inInitialState } from '@store/helpers'
import { useTemplateContext } from '@contexts/TemplateContext'
import { usePageMeta } from '@hooks'
import classNames from 'classnames'
import { pageClassName } from '../constants'
import NewsItems from './NewsItems'

import './style.scss'
import NotFoundPage from '@components/Page/NotFoundPage'

const pageCode = 'news'

const NewsPage = () => {
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
      <NewsItems />
    </div>
  ) : <NotFoundPage />
}

export default NewsPage
