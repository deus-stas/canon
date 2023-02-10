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
import Filter from './Filter'
import CareerTabs from '../../Header/CareerTabs'

const pageCode = 'contacts/vacancy'

const VacanciesPage = () => {
  usePageMeta({ pageCode })
  const lang = useTemplateContext().lang
  const region = getCurrentRegion()
  const dispatch = useDispatch()
  const pageStoreChunk = useSelector(store => store['page'][pageCode])
  const [vacancies, setVacancies] = useState([])
  const [vacanciesFiltred, setVacanciesFiltred] = useState([])
  const [showCountryCityInLIst, setShowCountryCityInLIst] = useState(true)

  useEffect(() => {
    if (!pageStoreChunk || inInitialState(pageStoreChunk)) {
      dispatch(fetchPage(pageCode, lang))
    }
    document.documentElement.scrollTop = 0
  }, [dispatch, pageStoreChunk])

  useEffect(() => {
    const url = `/local/api/?action=vacancies.getList&lang=${lang}`
    fetch(url, { mode: 'no-cors' })
      .then(response => response.json()).then(items => {
        if (items.length) {
          setVacancies(items)
          setVacanciesFiltred(items)
        }
      })
  }, [])

  if (!inFinalState(pageStoreChunk)) {
    return null
  }
  const handleChangeFilter = value => {
    let tmpItems = vacancies
    if (value.country) {
      tmpItems = tmpItems.filter(item => item.country === value.country)
    }
    if (value.city) {
      tmpItems = tmpItems.filter(item => item.city === value.city)
    }

    setShowCountryCityInLIst(!(value.country && value.city))

    setVacanciesFiltred(tmpItems)
  }

  const { data: pageData } = pageStoreChunk

  const blocks = pageData.detailText.split('<!--block-->')

  return pageData ? (
    <div className={classNames(`${pageClassName} ${pageCode}-${pageClassName}`)}>
      <div className="container vacancies-content">
        <div className="wrapper">

          <CareerTabs/>

          {pageData.detailImage && <img src={pageData.detailImage?.src} width={pageData.detailImage?.w}  height={pageData.detailImage?.h} alt={pageData.name} className="innerImage"/>}

          <div dangerouslySetInnerHTML={{ __html: blocks[0] }}/>

          <Filter items={vacancies} change={handleChangeFilter}/>

          <div className="list">
            { vacanciesFiltred && vacanciesFiltred.map((vacancy, index) => (
              <a href={vacancy.externalLink} key={index} target="_blank" rel="noreferrer">
                <h3 dangerouslySetInnerHTML={{ __html: vacancy.name }}/>
                <p dangerouslySetInnerHTML={{ __html: vacancy.previewText }}/>
                {showCountryCityInLIst && <div>{vacancy.country} &nbsp;&nbsp;&nbsp;{vacancy.city}</div>}
              </a>
            ))}
          </div>
          <div dangerouslySetInnerHTML={{ __html: blocks[1] }}/>
        </div>
      </div>
    </div>
  ) : <NotFoundPage />
}

export default VacanciesPage
