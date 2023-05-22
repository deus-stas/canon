import React, { useEffect } from 'react'
import { Link } from 'react-router-dom'
import { useDispatch, useSelector } from 'react-redux'
import { inFinalState, inInitialState } from '@store/helpers'
import { fetchPage } from '@store'
import classNames from 'classnames'
import { useTemplateContext } from '@contexts/TemplateContext'
import { pageClassName } from '@components/Page/constants'
import { usePageMeta } from '@hooks'

import './style.scss'

const pageCode = 'not-found'

const NotFoundPage = () => {
  usePageMeta({ pageCode })
  const dispatch = useDispatch()
  const pageStoreChunk = useSelector(store => store['page'][pageCode])

  const lang = useTemplateContext().lang
  const langPrefix = (lang === 'ru') ? '' : '/' + lang
  const templateSettings = useTemplateContext().templateSettings

  useEffect(() => {
    document.documentElement.scrollTop = 0
  })

  useEffect(() => {
    if (!pageStoreChunk || inInitialState(pageStoreChunk)) {
      dispatch(fetchPage(pageCode, lang))
    }
  }, [dispatch, pageStoreChunk])

  if (!inFinalState(pageStoreChunk)) {
    return null
  }

  return (
    <div className={classNames(`flex-column ${pageClassName} ${pageCode}-${pageClassName}`)}>
      <div className="container">
        <div className="flex-column wrapper">
          <h1>404</h1>
          <p>
            <span dangerouslySetInnerHTML={{ __html: templateSettings.text404Description }} /><br/>
            <span>
              <Link to={`${langPrefix}/`} dangerouslySetInnerHTML={{ __html: templateSettings.text404Home }} />
            </span>
          </p>
        </div>
      </div>
    </div>
  )
}

export default NotFoundPage
