import React, { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchBottomMenu } from '@/store'
import { inFinalState, inInitialState } from '@/store/helpers'
import { NavLink } from 'react-router-dom'
import { useTemplateContext } from '@/contexts/TemplateContext'

import './style.scss'

const FooterNav = () => {
  const lang = useTemplateContext().lang
  const langPrefix = (lang === 'ru') ? '' : '/' + lang
  const dispatch = useDispatch()
  const bottomMenuStoreChunk = useSelector(store => store['bottomMenu'])

  useEffect(() => {
    if (!bottomMenuStoreChunk || inInitialState(bottomMenuStoreChunk)) {
      dispatch(fetchBottomMenu(lang))
    }
  }, [dispatch, bottomMenuStoreChunk])

  if (!inFinalState(bottomMenuStoreChunk)) {
    return null
  }

  const { data: bottomMenu } = bottomMenuStoreChunk

  const columns = [[], []]

  let k = 0
  bottomMenu.forEach((el, index) => {
    const childItems = bottomMenu.filter(childItem => childItem.parent_id === el.id)
    if (childItems.length > 0) {
      el.isParent = true
      columns[(k++) % columns.length].push(el)
    }
  })
  if (!columns[0].length) {
    columns[0].push(columns[1][0])
    delete columns[1][0]
  }

  return bottomMenu.length ? (
    <div className="flex footer-menu">
      <div className="footer-menu-withChild">
        {
          columns.map((column, indexColumn) => (
            <ul key={indexColumn} className="flex-column footer-menu-items">
              {column.map((el, index) => {
                const elName = el.name.replace(/&nbsp;/, '\u00A0')
                const childItems = bottomMenu.filter(childItem => childItem.parent_id === el.id)
                return (
                  <li key={index} className="parent">
                    <span className="footer-menu-header"><span>{elName}</span></span>
                    <div className="footer-menu-child-container">
                      <ul className="flex-column footer-menu-child">
                        {
                          childItems.map((childEl, childIndex) => {
                            const activeChild = (
                              (location.hash && childEl.link.includes(location.hash)) ||
                                          (!location.hash && !childEl.link.includes('#'))
                            ) ? 'active-child current-child' : 'active-child'

                            return (
                              <li key={childIndex}>
                                <NavLink exact activeClassName={activeChild} to={langPrefix + childEl.link}>
                                  <span dangerouslySetInnerHTML={{ __html: childEl.name }}/>
                                </NavLink>
                              </li>
                            )
                          })
                        }
                      </ul>
                    </div>
                  </li>
                )
              })}
            </ul>
          ))

        }

      </div>
      <ul className="flex-column footer-menu-items">
        {
          bottomMenu.map((el, index) => {
            if (!el.parent_id && !el.isParent) {
              const elName = el.name.replace(/&nbsp;/, '\u00A0')

              let to = langPrefix + el.link
              if (el.link === '/education/' && location.pathname === langPrefix + '/education/about/') {
                to = null
              }

              return <li key={index}>
                {!!to && <NavLink className="footer-menu-header" exact
                  to={to}><span>{elName}</span></NavLink>}

                {!to && <div className="footer-menu-header"><span>{elName}</span></div>}
              </li>
            }
          })
        }
      </ul>
    </div>
  ) : null
}

export default FooterNav
