import React, { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { inFinalState, inInitialState } from '@/store/helpers'
import { useTemplateContext } from '@/contexts/TemplateContext'
import { getCurrentRegion } from '@/components/Header/Regions'
import { fetchCatalogItem } from '@store'
import { Fancybox } from "@fancyapps/ui";

import './style.scss'
import NotFoundPage from '@components/Page/NotFoundPage'
import { Link } from 'react-router-dom'
import ClinicalGallery from './ClinicalGallery'
import videoModals from '../../../../hooks/videoModals'
import { updatePageMeta } from '../../../../hooks/usePageMeta'

const catalogClassName = 'catalog-detail'

const CatalogItemPage = props => {
  const lang = useTemplateContext().lang;
  let catalogItemCode = props.match.params.path1
  if (props.match.params.path2) {
    catalogItemCode += '/' + props.match.params.path2
  }
  if (props.match.params.path3) {
    catalogItemCode += '/' + props.match.params.path3
  }
  if (props.match.params.path4) {
    catalogItemCode += '/' + props.match.params.path4
  }
  const region = getCurrentRegion()
  const dispatch = useDispatch()
  const catalogItemsStoreChunk = useSelector(store => store['catalogItem'][catalogItemCode])
  videoModals(catalogItemCode)

  useEffect(() => {
    if (!catalogItemsStoreChunk || inInitialState(catalogItemsStoreChunk)) {
      dispatch(fetchCatalogItem(catalogItemCode, region, lang))
    }
    document.documentElement.scrollTop = 0
    Fancybox.bind("[data-fancybox]", {});
  }, [dispatch, catalogItemsStoreChunk])

  if (!inFinalState(catalogItemsStoreChunk)) {
    return null
  }
  const { data: catalogItem } = catalogItemsStoreChunk

  if (catalogItem) {
    catalogItem.menu.forEach(item => {
      if (location.pathname === item.link) {
        item.active = true
      }
    })

    updatePageMeta(catalogItem.seo)

    console.log(catalogItem);

    const flagTabs = catalogItem.disable_tabs;
    const twoBanner = catalogItem.two_banner;

    return (
      <div className={`flex-column ${catalogClassName} ${twoBanner ? '--no-pb' : ' '}`}>
        <div className="container">
          <h1 dangerouslySetInnerHTML={{ __html: catalogItem.name }} />

          <div className="wrapper">
            {catalogItem.menu &&
              <div className="tabs">
                {catalogItem.menu.map((item, index) => (
                  <Link key={index} to={item.link}>
                    <div className={'tab ' + (item.active ? 'active' : '')}>
                      {item.image?.src ? <img alt="" src={item.image?.src} /> : ''}
                      {item.name}
                    </div>
                  </Link>
                ))}
              </div>}
          </div>

          {catalogItem.previewImage?.src ?
            <div className="wrapper">
              <img alt={catalogItem.name}
                className="topImage"
                src={catalogItem.previewImage.src}
                width={catalogItem.previewImage.w}
                height={catalogItem.previewImage.h} />
            </div> :
            ''}

          {twoBanner ?
            <div className="container catalog-detail__extra --top">
              {catalogItem.detailText && <div className="wrapper" dangerouslySetInnerHTML={{ __html: catalogItem.detailText }} />}
            </div>
            : null
          }


        </div>
        {catalogItem.medialibrary && <ClinicalGallery flagTabs={flagTabs} items={catalogItem.medialibrary} />}

        {!twoBanner ?
          <div className="container catalog-detail__extra">
            {catalogItem.detailText && <div className="wrapper" dangerouslySetInnerHTML={{ __html: catalogItem.detailText }} />}
          </div>
          : null
        }


      </div>
    )
  }

  return <NotFoundPage />
}

export default CatalogItemPage
