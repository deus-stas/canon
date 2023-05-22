import React, { useEffect, useState } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchPage } from '@store'
import { inFinalState, inInitialState } from '@store/helpers'
import classNames from 'classnames'
import { pageClassName } from '../constants'
import { useTemplateContext } from '@contexts/TemplateContext'
import { usePageMeta } from '@hooks'
import './style.scss'
import NotFoundPage from '@components/Page/NotFoundPage'
import { getCurrentRegion } from '../../Header/Regions'
import WebinarsList from './WebinarsList'

const pageCode = 'education/webinars'

const WebinarPage = () => {
  usePageMeta({ pageCode })
  const lang = useTemplateContext().lang
  const region = getCurrentRegion()
  const dispatch = useDispatch()
  const pageStoreChunk = useSelector(store => store['page'][pageCode])
  const [webinars, setWebinars] = useState([]);
  const [themes, setThemes] = useState([]);
  const [curSection, setCurSection] = useState('All');
  const [shows, setShows] = useState([]);
  const [showsFiltred, setShowsFiltred] = useState([]);
  const [showsFiltredOld, setShowsFiltredOld] = useState([]);

  useEffect(() => {
    if (!pageStoreChunk || inInitialState(pageStoreChunk)) {
      dispatch(fetchPage(pageCode, lang))
    }
    document.documentElement.scrollTop = 0
  }, [dispatch, pageStoreChunk])

  useEffect(() => {
    const url = `/local/api/?action=webinars.getList&region=${region}&lang=${lang}`
    fetch(url, { mode: 'no-cors' })
      .then(response => response.json())
      .then(items => {
        let mainArr = [];
        for (const key in items) {
          mainArr.push(items[key]);
        }

        setWebinars(mainArr);
      })
  }, []);

  useEffect(() => {
    let showsTmp = webinars[0];
    let showsTmpOld = webinars[1];


    if (curSection !== 'All') {
      showsTmp = webinars[0].filter(item => item.sectionName.includes(curSection))
      showsTmpOld = webinars[1].filter(item => item.sectionName.includes(curSection))
    }

    setShowsFiltred(showsTmp);
    setShowsFiltredOld(showsTmpOld);

  }, [curSection]);

  const buildThemes = items => {
    const themesArr = {};
    themesArr['All'] = 0;
    items.forEach(item => {
      themesArr['All'] += item.length;
      item.forEach(elem => {
        elem.sectionName.forEach(itemSect => {
          themesArr[itemSect] = themesArr[itemSect] ? themesArr[itemSect] + 1 : 1;
        })
      });
    });

    setThemes(Object.entries(themesArr));
  }

  const handleSectionSelect = event => {
    setCurSection(event.target.getAttribute('data-code'))
  }

  if (inFinalState(pageStoreChunk)) {
    if (!shows.length) {
      if (webinars.length) {
        buildThemes(webinars);
        setShows(webinars);
        setShowsFiltred(webinars[0])
        setShowsFiltredOld(webinars[1])
      }
    }
  } else {
    return null
  }

  const { data: pageData } = pageStoreChunk

  const blocks = pageData.detailText.split('<!--block-->')

  return pageData ? (
    <div className={classNames(`${pageClassName} ${pageCode}-${pageClassName}`)}>
      <div className="container webinar-content">
        <div className="wrapper">

          <div>
            {pageData.detailImage && <img src={pageData.detailImage?.src} width={pageData.detailImage?.w} height={pageData.detailImage?.h} alt={pageData.name} className="innerImage" />}

            {themes.length > 0 &&
              <div className="tabs-sections">
                {themes.map((item, index) => (
                  <div key={index} onClick={handleSectionSelect}
                    data-code={item[0]}
                    className={classNames(['tabs-sections-item',
                      { 'active': item[0] === curSection }])}>
                    {item[0] === 'All' && lang === 'ru' ? 'Все' : item[0]} &nbsp;
                    <span>{item[1]}</span>
                  </div>
                ))}
              </div>
            }

            <div className="webinar__text" dangerouslySetInnerHTML={{ __html: blocks[0] }} />

            <WebinarsList items={showsFiltred} />

            {showsFiltredOld && <div>
              <br />
              <h2>{lang === 'en' ? 'Past webinars' : 'Прошедшие вебинары'}</h2>
              <WebinarsList old="true" items={showsFiltredOld} />
            </div>}

            <div dangerouslySetInnerHTML={{ __html: blocks[1] }} />

          </div>

        </div>
      </div>
    </div>
  ) : <NotFoundPage />
}

export default WebinarPage
