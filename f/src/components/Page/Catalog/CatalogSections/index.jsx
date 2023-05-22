import React, { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { inFinalState, inInitialState } from '@/store/helpers'
import { useTemplateContext } from '@/contexts/TemplateContext'
import { getCurrentRegion } from '@/components/Header/Regions'
import { fetchSections } from '@/store'

import './style.scss'
import { Link } from 'react-router-dom'

const catalogClassName = 'catalog'

const Catalog = props => {
  const lang = useTemplateContext().lang
  const region = getCurrentRegion()
  const dispatch = useDispatch()
  const sectionsStoreChunk = useSelector(store => store['catalogSections'])

  useEffect(() => {
    if (!sectionsStoreChunk || inInitialState(sectionsStoreChunk)) {
      dispatch(fetchSections(region, lang))
    }
  }, [dispatch, sectionsStoreChunk])

  useEffect(() => {
    if (inFinalState(sectionsStoreChunk)) {
      if (props.hash) {
        const el = document.querySelector(props.hash)
        if (el) {
          const scrollTo = el.getBoundingClientRect().top + pageYOffset
          document.documentElement.scrollTop = scrollTo - 40
        }
      } else {
        document.documentElement.scrollTop = 0
      }
    }
  }, [sectionsStoreChunk, props.hash])

  if (!inFinalState(sectionsStoreChunk)) {
    return null
  }

  const { data: { items } } = sectionsStoreChunk

  return items.length ?
    <div className={`flex-column ${catalogClassName}`}>
      {
        items.map((product, index) =>
          <div  key={index}>
            {index !== 0 ?
              <div className="wrapper">
                <hr />
              </div> : null}
            <div id={product.code} className={`container ${catalogClassName}-item`}>
              <div className="flex wrapper">
                {
                  product.image ? (
                    <div className={`${catalogClassName}-item-image`}>
                      {!product.children && (
                        <img alt="" src={product.image.src} />
                      )}
                      {product.children && (
                        <Link to = {product.link} className="ntd" >
                          <img alt="" src={product.image.src} />
                        </Link>
                      )}
                    </div>
                  ) : null
                }
                <div className={`flex-column ${catalogClassName}-item-content`}>
                  {!product.children && (
                    <h2 dangerouslySetInnerHTML={{ __html: product.name }}/>
                  )}
                  {product.children && (
                    <Link to = {product.link} className="h2Link" >
                      < h2 dangerouslySetInnerHTML={{ __html: product.name }} />
                    </Link>
                  )}

                  <div className={`${catalogClassName}-item-text`}>
                    <p dangerouslySetInnerHTML={{ __html: product.description }} />

                    {product.children && (
                      <div className="products">
                        <div className="products-label">{lang === 'en' ? 'Products' : 'Продукты'}</div>
                        <div className="products-list">
                          {product.children.map((item, index) =>
                            <Link key={index} to={item.link}>{item.name}</Link>
                          )}
                        </div>
                      </div>
                    )}
                  </div>

                </div>
              </div>
            </div>
          </div>
        )
      }
    </div> : null
}

export default Catalog
