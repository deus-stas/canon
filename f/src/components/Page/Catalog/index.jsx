import React, { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchPage } from '@store'
import { inFinalState, inInitialState } from '@store/helpers'
import { useTemplateContext } from '@contexts/TemplateContext'
import { usePageMeta } from '@hooks'
import classNames from 'classnames'
import { pageClassName } from '../constants'
import CatalogSections from './CatalogSections'

import './style.scss'
import NotFoundPage from '@components/Page/NotFoundPage'
import { updatePageMeta } from '../../../hooks/usePageMeta'

const pageCode = 'products'

const CatalogPage = props => {
  usePageMeta({ pageCode })
  const lang = useTemplateContext().lang
  const anchor = props.location.hash
  const dispatch = useDispatch()
  const pageStoreChunk = useSelector(store => store['page'][pageCode])

  useEffect(() => {
    if (!pageStoreChunk || inInitialState(pageStoreChunk)) {
      dispatch(fetchPage(pageCode, lang))
    }
  }, [dispatch, pageStoreChunk])

  if (!inFinalState(pageStoreChunk)) {
    return null
  }

  const { data: pageData } = pageStoreChunk

  return pageData ? (
    <div className={classNames(`${pageClassName} ${pageCode}-${pageClassName}`)}>
      <CatalogSections hash={anchor} />
    </div>
  ) : <NotFoundPage />
}

export default CatalogPage
