import React from 'react'
import { Link, useLocation } from 'react-router-dom'
import { useSelector } from 'react-redux'
import { inFinalState } from '@store/helpers'
import { useTemplateContext } from '@contexts/TemplateContext'

import './style.scss'

const Breadcrumbs = () => {
  const { pathname } = useLocation()
  let pathNames = pathname.split('/').filter(name => name)
  const topMenuStoreChunk = useSelector(store => store['topMenu'])
  const { data: menuItems } = topMenuStoreChunk
  const lang = useTemplateContext().lang
  const langPrefix = (lang === 'ru') ? '' : '/' + lang
  const catalogItemsStoreChunk = useSelector(store => store['catalogItem'])
  const specialtiesItemsStoreChunk = useSelector(store => store['specialties'])
  const landingsItemsStoreChunk = useSelector(store => store['landingsItems'])
  const landingsDaysStoreChunk = useSelector(store => store['landingsDays'])
  const pageStoreChunk = useSelector(store => store['page'])

  if (!inFinalState(topMenuStoreChunk)) {
    return null
  }

  if (pathNames.length < 1) {
    return null
  }

  pathNames = pathNames.filter(item => item !== lang)

  return (
    <ul className="breadcrumbs">
      <li className="breadcrumb">
        <Link to={`${langPrefix}/`} className="breadcrumb-link">
          <svg width="12" height="10" viewBox="0 0 12 10" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M2 10H4.66667V6H7.33333V10H10V5.33333H12L6 0L0 5.33333H2V10Z" />
          </svg>
        </Link>
      </li>
      {
        pathNames.map((breadcrumb, index) => {
          const routTo = `${langPrefix}/${pathNames.slice(0, index + 1).join('/')}`
          const isLast = (index === (pathNames.length - 1))

          let menuItem = menuItems.filter(item => {
            const linkArr = item.link.split('/')
            const link = linkArr[linkArr.length - 2]
            return link.replace(/\//g, '') === breadcrumb
          }
          )[0]

          console.log('menuItem', menuItem);

          if (!menuItem) {
            if (inFinalState(catalogItemsStoreChunk) && pathname) {
              let key = pathname.replace('/en/products/', '')
              key = key.replace('/products/', '');
              if (key.endsWith('/')) {
                key = key.slice(0, -1)
              }

              const itm = catalogItemsStoreChunk[key]?.data
              console.log(key);
              if (itm) {
                if (itm.code === breadcrumb) {
                  menuItem = itm
                } else if (itm.parentSectionName) {
                  if (itm.depth > 4 && index == 2) {
                    menuItem = { name: pathNames[2] }
                  } else if(itm.depth > 4 && index == 1) {
                    menuItem = { name: pathNames[1] }
                  } else {
                    menuItem = { name: itm.parentSectionName }
                  }
                }
              }
            }

            if (inFinalState(specialtiesItemsStoreChunk)) {
              let key = pathname.replace('/en/specialties/', '')
              key = key.replace('/specialties/', '')
              key = key.slice(0, -1)
              menuItem = specialtiesItemsStoreChunk[key]?.data
            }

            if (inFinalState(pageStoreChunk[breadcrumb])) {
              const data = pageStoreChunk[breadcrumb]?.data
              if (data) {
                menuItem = { name: data.name }
              }
            }

            if (inFinalState(landingsItemsStoreChunk)) {
              let key = pathname.replace('/en/events/', '')
              key = key.replace('/events/', '');
              key = key.split('/')
              let itm;
              itm = landingsItemsStoreChunk[key[0]]?.data
              if (itm) {
                if (itm.code === breadcrumb) {
                  menuItem = itm
                } else {
                  if (index == 2) {
                    let day = landingsDaysStoreChunk[key[0] + '/' + key[1]].data.name
                    menuItem = { name: day }
                  }
                }
              }

            }

          }
          return !isLast ?
            (
              <li className="breadcrumb" key={index}>
                <svg width="5" height="7" viewBox="0 0 5 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M1 1L3.5 3.5L1 6" stroke="#808080" />
                </svg>
                {(() => {
                  if (
                    menuItem?.name === '200G' ||
                    menuItem?.name === '100G' ||
                    menuItem?.name === 'a450' ||
                    menuItem?.name === 'a550' ||
                    menuItem?.name === 'Aplio a'
                  ) {
                    return <Link to={routTo + '/imaging'} dangerouslySetInnerHTML={{ __html: menuItem?.name }} />;
                  } else if (menuItem?.code === 'about-us') {
                    return <Link to={routTo + '/company/'} dangerouslySetInnerHTML={{ __html: menuItem?.name }} />;
                  } else if (menuItem?.code === 'service-support') {
                    return <Link to={routTo + '/service/'} dangerouslySetInnerHTML={{ __html: menuItem?.name }} />;
                  } else if (menuItem?.code === 'events') {
                    return <Link to={routTo + '/ochnye/'} dangerouslySetInnerHTML={{ __html: menuItem?.name }} />;
                  } else {
                    return <Link to={routTo} dangerouslySetInnerHTML={{ __html: menuItem?.name }} />;
                  }
                })()}
              </li>
            ) :
            (
              <li className="breadcrumb" key={index}>
                <svg width="5" height="7" viewBox="0 0 5 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M1 1L3.5 3.5L1 6" stroke="#808080" />
                </svg>
                <span dangerouslySetInnerHTML={{ __html: menuItem?.name }} />
              </li>
            )
        })
      }
    </ul>
  )
}

export default Breadcrumbs