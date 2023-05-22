import React, { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchNewsItems } from '@/store'
import { inFinalState, inInitialState } from '@/store/helpers'
import { Link } from 'react-router-dom'
import { useTemplateContext } from '@/contexts/TemplateContext'
import { getCurrentRegion } from '@/components/Header/Regions'

import './style.scss'

const newsItemsClassName = 'news'

const NewsItems = () => {
  const lang = useTemplateContext().lang
  const langPrefix = lang === 'ru' ? '' : '/' + lang
  const region = getCurrentRegion()
  const dispatch = useDispatch()
  const newsItemsStoreChunk = useSelector(store => store['news'])

  useEffect(() => {
    if (!newsItemsStoreChunk || inInitialState(newsItemsStoreChunk)) {
      dispatch(fetchNewsItems(region, lang))
    }
  }, [dispatch, newsItemsStoreChunk])

  if (!inFinalState(newsItemsStoreChunk)) {
    return null
  }

  const { data: { items } } = newsItemsStoreChunk

  return items.length ?
    <div className={`flex-column ${newsItemsClassName}`}>
      {
        items.map((news, index) => <div  key={index}>
          {index !== 0 ?
            <div className="wrapper">
              <hr />
            </div> : null}
          <div className={`container ${newsItemsClassName}-item`}>
            <div className="news-item__wrap wrapper">
              <Link to={`${langPrefix}/news/${news.code}`} className={`flex-center ${newsItemsClassName}-item-image ntd`}>
                {news.previewImage?.src ? <img alt="" src={news.previewImage.src} /> : ''}
              </Link>
              <div className={`flex-column ${newsItemsClassName}-item-content`}>
                <p className={`${newsItemsClassName}-item-date`} dangerouslySetInnerHTML={{ __html: news.date }} />
                <h2>
                  <Link to={`${langPrefix}/news/${news.code}`} className={`${newsItemsClassName}-item-link`}>
                    <span dangerouslySetInnerHTML={{ __html: news.name }} />
                  </Link>
                </h2>
                <div className={`${newsItemsClassName}-item-text`}
                  dangerouslySetInnerHTML={{ __html: news.previewText }}/>
              </div>
            </div>
          </div>
        </div>
        )
      }
    </div> : null
}

export default NewsItems
