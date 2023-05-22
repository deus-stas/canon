import React, { useEffect } from 'react'
import { useTemplateContext } from '@/contexts/TemplateContext'
import { useDispatch, useSelector } from 'react-redux'
import { inFinalState, inInitialState } from '@/store/helpers'
import { fetchRegions } from '@/store'

import './style.scss'

export const Regions = () => {
  const lang = useTemplateContext().lang
  const dispatch = useDispatch()
  const regionsStoreChunk = useSelector(store => store['regions'])
  useEffect(() => {
    if (!regionsStoreChunk || inInitialState(regionsStoreChunk)) {
      dispatch(fetchRegions(lang))
    }
  }, [dispatch, regionsStoreChunk])

  if (!inFinalState(regionsStoreChunk)) {
    return null
  }

  const { data: regions } = regionsStoreChunk
  const regionsList = Object.values(regions)
  const currentRegion = getCurrentRegion() ?
    regionsList.filter(region => region.code === getCurrentRegion())[0] :
    regionsList.filter(region => region.CURRENT === true)[0]

  return regionsList.length ?
    (
      <div className="flex-center region-items-container">
        <div className="flex-center current-region-item">
          <svg width="9" height="18" viewBox="0 0 9 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M8.57031 8.68094C8.57031 11.266 6.82589 13.5638 4.5 15C2.17411 13.5638 0.429688 11.266 0.429688 8.68094C0.429688 6.46009 2.25203 4.65973 4.5 4.65973C6.74797 4.65973 8.57031 6.46009 8.57031 8.68094Z"
            />
            <circle cx="4.49998" cy="8.64286" r="1.64878" />
          </svg>
          <span className="current-region-name">{currentRegion.name}</span>
        </div>
        <ul className="flex-column region-items">
          {
            regionsList.map((region, index) => <li
              className={(currentRegion.code === region.code) ? 'region-item active' : 'region-item'}
              data-code={region.code}
              data-name={region.name}
              key={index}
              onClick={changeRegion}
            >
              <span className="region-name">{region.name}</span>
            </li>)
          }
        </ul>
      </div>
    ) : null
}

export const NavRegions = props => {
  const lang = useTemplateContext().lang
  const dispatch = useDispatch()
  const regionsStoreChunk = useSelector(store => store['regions'])
  const templateSettings = useTemplateContext().templateSettings

  useEffect(() => {
    if (!regionsStoreChunk || inInitialState(regionsStoreChunk)) {
      dispatch(fetchRegions(lang))
    }
  }, [dispatch, regionsStoreChunk])

  if (!inFinalState(regionsStoreChunk)) {
    return null
  }

  const { data: regions } = regionsStoreChunk
  const regionsList = Object.values(regions)
  const currentRegion = getCurrentRegion() ?
    regionsList.filter(region => region.code === getCurrentRegion())[0] :
    regionsList.filter(region => region.CURRENT === true)[0]

  return regionsList.length ?
    (
      <li className="parent nav-region-items-container">
        <span className="flex-center parent-header current-region-item" onClick={props.openRegionsList}>
          <span className="current-region-name">{currentRegion.name}</span>
          <span dangerouslySetInnerHTML={{ __html: templateSettings.textRegion }} />
        </span>
        <div className="top-menu-child-container">
          <ul className="flex-column top-menu-child">
            {
              regionsList.map((region, index) => (
                <li key={index} >
                  <a className={(currentRegion.code === region.code) ? 'region-item active' : 'region-item'}
                    data-code={region.code}
                    data-name={region.name}
                    onClick={changeRegion}
                    href="#"
                  >
                    <span className="region-name">{region.name}</span>
                  </a>
                </li>
              ))
            }
          </ul>
        </div>
      </li>
    ) : null
}

export function getCurrentRegion() {
  return localStorage.getItem('region') || 'RU'
}

export function changeRegion(e) {
  e.preventDefault()
  localStorage.setItem('region', e.currentTarget.dataset.code)
  window.location.reload()
}
