import React, { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchPage } from '@store'
import { inFinalState, inInitialState } from '@store/helpers'
import { useTemplateContext } from '@contexts/TemplateContext'
import { usePageMeta } from '@hooks'
import classNames from 'classnames'
import { pageClassName } from '../constants'
import FeedbackForm from '../Contacts/FeedbackForm'

import './style.scss'
import NotFoundPage from '@components/Page/NotFoundPage'

const pageCode = 'service-support/warranty'

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
          <h2 dangerouslySetInnerHTML={{ __html: templateSettings.name.replace(/&nbsp;/, ' ') }} />
          <p className="contacts-address" dangerouslySetInnerHTML={{ __html: templateSettings.address }} />
          <p className="contacts-phone">
            <span className="grey" dangerouslySetInnerHTML={{ __html: templateSettings.textPhone + ':' }} />
            <a
              href={`tel:${templateSettings.phone.replace(/[-\s)(]/g, '')}`}
              dangerouslySetInnerHTML={{ __html: templateSettings.phone }}
            />
          </p>

          <FeedbackForm title={'Оставить заявку на сервис'} />
        </div>
      </div>
    </div>
  ) : <NotFoundPage />
}

export default FeedbackPage
