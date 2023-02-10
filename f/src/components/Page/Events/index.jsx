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
import { Link } from 'react-router-dom'
import { fetchEvents } from '../../../store'
import { getCurrentRegion } from '../../Header/Regions'

const pageCode = 'events'

const EventsPage = () => {
  usePageMeta({ pageCode })
  const lang = useTemplateContext().lang
  const region = getCurrentRegion()
  const dispatch = useDispatch()
  const pageStoreChunk = useSelector(store => store['page'][pageCode])
  const eventsChunk = useSelector(store => store['events'])
  const [shows, setShows] = useState([])
  const [showsEmpty, setShowsEmpty] = useState(false)
  const [showsFiltred, setShowsFiltred] = useState([])
  const [sections, setSections] = useState([])
  const [curSection, setCurSection] = useState('All')
  const [prevShowed, setPrevShowed] = useState(false)
  const [prevIds, setPrevIds] = useState([])

  const buildSections = showItems => {
    const sections = {}
    sections['All'] = showItems.length
    showItems.forEach(item => {
      item.sectionName.forEach(itemSect => {
        sections[itemSect] = sections[itemSect] ? sections[itemSect] + 1 : 1;
      })
    })
    setSections(Object.entries(sections))
  }

  useEffect(() => {
    if (!pageStoreChunk || inInitialState(pageStoreChunk)) {
      dispatch(fetchPage(pageCode, lang))
    }
    document.documentElement.scrollTop = 0
  }, [dispatch, pageStoreChunk])

  useEffect(() => {
    if (!eventsChunk || inInitialState(eventsChunk)) {
      dispatch(fetchEvents(region, lang, false))
    }
  }, [dispatch, eventsChunk])
  useEffect(() => {
    let showsTmp = shows
    if (curSection !== 'All') {
      showsTmp = shows.filter(item => item.sectionName.includes(curSection))
    }
    setShowsFiltred(showsTmp)
  }, [curSection])

  if (inFinalState(eventsChunk) && !shows.length && !showsEmpty) {
    const showItems = eventsChunk.data
    if (showItems.length) {
      setShows(showItems)
      setShowsFiltred(showItems)
      buildSections(showItems)
    } else {
      setShowsEmpty(true)
    }
  }

  if (!inFinalState(pageStoreChunk) || !inFinalState(eventsChunk)) {
    return null
  }

  const handleSectionSelect = event => {
    setCurSection(event.target.getAttribute('data-code'))
  }

  const { data: pageData } = pageStoreChunk

  const fetchOld = () => {
    const url = `/local/api/?action=events.getList&region=${region}&lang=${lang}&old=Y`
    return  fetch(url, { mode: 'no-cors' })
      .then(response => response.json())
  }

  const handlePreviousShows = event => {
    fetchOld().then(items => {
      setPrevIds(items.map(item => item.id))
      const itemsAll = shows.concat(items)
      setShows(itemsAll)
      setShowsFiltred(itemsAll)
      buildSections(itemsAll)
      setPrevShowed(true)
    })
  }

  const handlePreviousShowsHide = event => {
    const futureItems = shows.filter(item => !prevIds.includes(item.id))
    setShows(futureItems)
    setShowsFiltred(futureItems)
    buildSections(futureItems)
    setPrevShowed(false)
  }

  console.log(shows);

  return pageData ? (
    <div className={classNames(`${pageClassName} ${pageCode}-${pageClassName}`)}>
      <div className="container events-content">
        <div className="wrapper">

          <div>
            {pageData.previewImage && <img src={pageData.previewImage?.src} width={pageData.previewImage?.w}  height={pageData.previewImage?.h} alt={pageData.name} className="innerImage"/>}

            <div className="wrapper-inner">

              {sections.length > 0 &&
                <div className="tabs-sections">
                  {   sections.map((item, index) => (
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
              {!showsFiltred.length  &&
                <div>
                  {lang === 'en' ? 'We announce new events soon' : 'Мы обьявим о новых мероприятиях в ближайшее время'}
                </div>
              }

              {!prevShowed && <a onClick={handlePreviousShows}>{lang === 'en' ? 'Show previous events' : 'Показать прошедшие мероприятия'}</a>}
              {prevShowed && <a onClick={handlePreviousShowsHide}>{lang === 'en' ? 'Hide previous events' : 'Скрыть прошедшие мероприятия'}</a>}

              {showsFiltred.length > 0 &&
                  <div>
                    <table className="webinar-table">
                      <tbody>
                        {   showsFiltred.map((show, index) => (
                          <tr key={index} className={show.externalLink ? 'hasExternalLInk' : ''} onClick={event => {
                            if (show.externalLink) {
                              window.open(show.externalLink, '_blank')
                            }
                          }}>
                            <td dangerouslySetInnerHTML={{ __html: show.name }}/>
                            <td dangerouslySetInnerHTML={{ __html: show.date }} className="wsn"/>
                            <td>{show.previewText}</td>
                            <td>
                              {show.externalLink && <a className="external" href={show.externalLink} target="_blank" rel="noreferrer"/>}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>

                    <div className="webinar-table-xs">
                      {   showsFiltred.map((show, index) => (
                        <div key={index} className={show.externalLink ? 'hasExternalLInk item' : ' item'} onClick={event => {
                          if (show.externalLink) {
                            window.open(show.externalLink, '_blank')
                          }
                        }}>
                          <div className="line1">
                            <div className="line1-name">
                              <b dangerouslySetInnerHTML={{ __html: show.name }}/>
                              {show.externalLink && <a className="external" href={show.externalLink} target="_blank" rel="noreferrer"/>}
                            </div>
                            <div dangerouslySetInnerHTML={{ __html: show.date }}  className="wsn"/>
                          </div>
                          <div className="line2">{show.previewText}</div>
                        </div>
                      ))}
                    </div>
                  </div>
              }
            </div>

          </div>

        </div>
      </div>
    </div>
  ) : <NotFoundPage />
}

export default EventsPage
