import React, { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { useTemplateContext } from '@contexts/TemplateContext'
import { inFinalState, inInitialState } from '@store/helpers'
import { fetchNewsItem } from '@store'
import NotFoundPage from '@components/Page/NotFoundPage'

import './style.scss'
import { updatePageMeta } from '../../../../hooks/usePageMeta'

const NewsItemPage = props => {
  const lang = useTemplateContext().lang
  const { newsCode } = props.match.params
  const dispatch = useDispatch()
  const newsStoreChunk = useSelector(store => store['newsItem'][newsCode])

  useEffect(() => {
    if (!newsStoreChunk || inInitialState(newsStoreChunk)) {
      dispatch(fetchNewsItem(newsCode, lang))
    }
    document.documentElement.scrollTop = 0
  }, [dispatch, newsStoreChunk])

  if (!inFinalState(newsStoreChunk)) {
    return null
  }

  const { data: newsItem } = newsStoreChunk

  if (newsItem) {
    let content = '<div class="container"><div class="wrapper">'
    content += newsItem.detailText.replace(
      /(<img.*><img.*>)/,
      '</div></div><div class="flex news-images">$1</div><div class="container"><div class="wrapper">'
    )
    content += '</div></div>'

    updatePageMeta(newsItem.seo)

    return (
      <div className="page news-item-page">
        <div className="container">
          <h1 dangerouslySetInnerHTML={{ __html: newsItem.name }}/>
          <p className="wrapper news-date" dangerouslySetInnerHTML={{ __html: newsItem.date }} />
        </div>
        <div className="news-content" dangerouslySetInnerHTML={{ __html: content }}/>
      </div>
    )
  } else {
    return <NotFoundPage />
  }
}

export default NewsItemPage
