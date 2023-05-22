import React, { useEffect, useState } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchPage } from '@store'
import { inFinalState, inInitialState, inLoadedState } from '@store/helpers'
import { useTemplateContext } from '@contexts/TemplateContext'
import { usePageMeta } from '@hooks'
import classNames from 'classnames'
import { pageClassName } from '../constants'
import { getCurrentRegion } from '@/components/Header/Regions'
import './style.scss'
import NotFoundPage from '@components/Page/NotFoundPage'
import PublicationFilter from './PublicationFilter'
import PublicationItems from './PublicationItems'
import { fetchPublications } from '@store'

const pageCode = 'publications'

const PublicationsPage = () => {
  usePageMeta({ pageCode })
  const lang = useTemplateContext().lang
  const dispatch = useDispatch()
  const pageStoreChunk = useSelector(store => store['page'][pageCode])
  const publicationsStoreChunk = useSelector(store => store['publications'])
  const [searchString, setSearchString] = useState('')
  const [selectedSection, setSelectedSection] = useState('All')
  const [itemsCount, setItemsCount] = useState(0)
  const [itemsToRender, setItemsToRender] = useState()
  const [sort, setSort] = useState('date-desc')
  const region = getCurrentRegion()
  const [sections, setSections] = useState([])

  useEffect(() => {
    if (!pageStoreChunk || inInitialState(pageStoreChunk)) {
      dispatch(fetchPage(pageCode, lang))
    }
    document.documentElement.scrollTop = 0
  }, [dispatch, pageStoreChunk])

  useEffect(() => {
    if (!publicationsStoreChunk || inInitialState(publicationsStoreChunk)) {
      dispatch(fetchPublications(region, lang))
    }
  }, [dispatch, publicationsStoreChunk])

  useEffect(() => {

    if (!inLoadedState(publicationsStoreChunk)) return
    let items = publicationsStoreChunk.data
    if (!itemsToRender) {
      setItemsCount(items.length)

      if (items) {
        const sections = {}
        sections['All'] = items.length
        items.forEach(item => {
          if (!sections[item.sectionName]) {
            sections[item.sectionName] = 0
          }
          sections[item.sectionName]++
        })
        setSections(Object.entries(sections))
      }

      setItemsToRender(items.sort((a, b) => b.timestamp - a.timestamp))
      return
    }
    if (selectedSection !== 'All') {
      items = items.filter(item => item.sectionName === selectedSection)
    }
    if (searchString) {
      items = items.filter(item => item.name.toLowerCase().indexOf(searchString.toLowerCase()) !== -1)
    }
    switch (sort) {
      case 'date-desc':
        items = items.sort((a, b) => b.timestamp - a.timestamp)
        break
      case 'date-asc':
        items = items.sort((a, b) => a.timestamp - b.timestamp)
        break
      case 'title-desc':
        items = items.sort((a, b) => {
          if (a.name < b.name) {
            return -1
          }
          if (a.name > b.name) {
            return 1
          }
          return 0
        })
        break
      case 'title-asc':
        items = items.sort((a, b) => {
          if (a.name > b.name) {
            return -1
          }
          if (a.name < b.name) {
            return 1
          }
          return 0
        })
        break
    }

    setItemsToRender(items)

  }, [selectedSection, sort, searchString, publicationsStoreChunk])

  if (!inFinalState(publicationsStoreChunk) || !inFinalState(pageStoreChunk)) {
    return null
  }

  const { data: pageData } = pageStoreChunk
  return pageData ? (
    <div className={classNames(`${pageClassName} ${pageCode}-${pageClassName} container`)}>

      {pageData.previewImage?.src ?
        <div className="wrapper">
          <img alt={pageData.name}
            className="topImage"
            src={pageData.previewImage.src}
            width={pageData.previewImage.w}
            height={pageData.previewImage.h}
          />
        </div> : ''}

      <div className="h1-wrap"> <h1>{pageData.name}</h1> <span className="publications-count">{itemsCount}</span></div>
      <PublicationFilter
        setSearchString={setSearchString}
        selectedSection={selectedSection}
        setSelectedSection={setSelectedSection}
        sections={sections}
        sort={sort}
        setSort={setSort}
      />
      <PublicationItems
        items={itemsToRender}
      />
    </div>
  ) : <NotFoundPage />
}

export default PublicationsPage
