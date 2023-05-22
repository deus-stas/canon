import React, { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { inFinalState, inInitialState } from '@/store/helpers'
import { useTemplateContext } from '@/contexts/TemplateContext'
import { getCurrentRegion } from '@/components/Header/Regions'
import { fetchSpecialtiesItem } from '@store'
import './style.scss'
import NotFoundPage from '@components/Page/NotFoundPage'
import { Link } from 'react-router-dom'
import ClinicalGallery from '../Catalog/CatalogItem/ClinicalGallery'
import videoModals from '../../../hooks/videoModals'
import { updatePageMeta } from '../../../hooks/usePageMeta'

const className = 'specialties-detail'

const SpecialtiesPage = props => {
  const lang = useTemplateContext().lang
  let ItemCode = props.match.params.path1
  if (props.match.params.path2) {
    ItemCode += '/' + props.match.params.path2
  }

  const region = getCurrentRegion()
  const dispatch = useDispatch()
  const ItemsStoreChunk = useSelector(store => store['specialties'][ItemCode])
  videoModals(ItemCode)

  useEffect(() => {
    if (!ItemsStoreChunk || inInitialState(ItemsStoreChunk)) {
      dispatch(fetchSpecialtiesItem(ItemCode, region, lang))
    }
    document.documentElement.scrollTop = 0
  }, [dispatch, ItemsStoreChunk])

  if (!inFinalState(ItemsStoreChunk)) {
    return null
  }
  const { data: item } = ItemsStoreChunk

  if (item) {
    item.menu.forEach(item => {
      if (location.pathname === item.link) {
        item.active = true
      }
    })

    updatePageMeta(item.seo)

    return (
      <div className={`flex-column  ${className}`}>
        <div className="container">
          <h1>{item.name}</h1>

          <div className="wrapper">
            {item.menu &&
          <div className="tabs">
            {item.menu.map((item, index) => (
              <Link key={index} to={item.link}>
                <div className={'tab ' + (item.active ? 'active' : '')}>
                  {item.image?.src ? <img alt="" src={item.image?.src}/> : ''}
                  {item.name}
                </div>
              </Link>
            ))}
          </div>}
          </div>

          {item.previewImage?.src ?
            <div className="wrapper">
              <img alt={item.name}
                className="topImage"
                src={item.previewImage.src}
                width={item.previewImage.w}
                height={item.previewImage.h}/>
            </div> :
            ''}

          {item.detailText && <div className="wrapper page" dangerouslySetInnerHTML={{ __html: item.detailText }}/>}
        </div>
        {item.medialibrary && <ClinicalGallery items={item.medialibrary}/>}

      </div>
    )
  }

  return <NotFoundPage/>
}

export default SpecialtiesPage
