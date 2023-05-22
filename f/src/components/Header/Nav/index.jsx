import React, { useEffect } from 'react'
import classNames from 'classnames'
import { NavLink, useLocation } from 'react-router-dom'
import { useDispatch, useSelector } from 'react-redux'
import { fetchTopMenu } from '@store'
import { inFinalState, inInitialState } from '@store/helpers'
import { useTemplateContext } from '@contexts/TemplateContext'
import Logo from '@components/Header/Logo'
import { NavRegions } from '../Regions'

import './style.scss'

const navClassname = 'nav'

export const Hamburger = () => {
  const openTopMenu = () => {
    document.body.style.paddingRight = (window.innerWidth - document.documentElement.clientWidth) + 'px'
    document.body.classList.add('top-menu-is-open')
  }

  return (
    <button className="flex-center hamburger" onClick={openTopMenu}>
      {/* иначе в текущей реализации не отработают клики в промежутках бургера */}
      &nbsp;
      <span className="ham" />
    </button>
  )
}

export const closeTopMenu = () => {
  document.body.classList.remove('top-menu-is-open')
  document.body.style.paddingRight = '0'
}

const Nav = () => {
  const lang = useTemplateContext().lang
  const langPrefix = (lang === 'ru') ? '' : '/' + lang
  const location = useLocation()
  const dispatch = useDispatch()
  const topMenuStoreChunk = useSelector(store => store['topMenu'])

  useEffect(() => {
    if (!topMenuStoreChunk || inInitialState(topMenuStoreChunk)) {
      dispatch(fetchTopMenu(lang))
    }
  }, [dispatch, topMenuStoreChunk])

  if (!inFinalState(topMenuStoreChunk)) {
    return null
  }

  const { data: topMenu } = topMenuStoreChunk

  const animateChildMenu = ({ timing, draw, duration }) => {
    const start = performance.now()

    requestAnimationFrame(function animate(time) {
      let timeFraction = (time - start) / duration
      if (timeFraction > 1) {
        timeFraction = 1
      }

      const progress = timing(timeFraction)

      draw(progress)

      if (timeFraction < 1) {
        requestAnimationFrame(animate)
      }
    })
  }

  const toggleChildMenu = e => {
    e.preventDefault()
    const eventTarget = e.target
    const menuItem = eventTarget.closest('.parent')
    const childMenuContainer = menuItem.querySelector('.top-menu-child-container')
    const childMenuHeight = childMenuContainer.querySelector('.top-menu-child').offsetHeight

    menuItem.classList.toggle('child-menu-is-open')

    if (menuItem.classList.contains('child-menu-is-open')) {
      animateChildMenu({
        timing: x => Math.sin((x * Math.PI) / 2),
        draw: progress => {
          childMenuContainer.style.height = (progress * childMenuHeight) + 'px'
        },
        duration: 600
      })
    } else {
      animateChildMenu({
        timing: x => Math.sin((x * Math.PI) / 2),
        draw: progress => {
          childMenuContainer.style.height = (childMenuHeight - (progress * childMenuHeight)) + 'px'
        },
        duration: 300
      })
    }
  }

  return topMenu.length ? (
    <nav id="top-nav" className="flex container nav">
      <div className="nav-overlay" onClick={closeTopMenu} />
      <div className="wrapper">
        <div className={classNames(`${navClassname}__logo`, 'flex-center')}>
          <Logo />
          <button className="flex-center close-button" onClick={closeTopMenu} />
        </div>
        <ul className="flex top-menu">
          {
            topMenu.map((el, index) => {
              const childItems = topMenu.filter(childItem => childItem.parent_id === el.id)
              const activeChild = childItems.filter(childItem => window.location.href.includes(childItem.link))
              let path = location.pathname
              path = path.replace('/en/', '')
              path = path.replace(/\//g, '')
              const isCurrent = el.link.replace(/\//g, '') === path

              const activeClass = isCurrent ? 'active current' : 'active'
              if (childItems.length > 0) {
                return (
                  <li key={index} className="parent">
                    <span
                      className={`parent-header${activeChild.length ? ' ' + activeClass : ''}`}
                      onClick={toggleChildMenu}
                      dangerouslySetInnerHTML={{ __html: el.name }} />
                    <button className="toggle-child-menu-button">
                      <svg width="11" height="7" viewBox="0 0 11 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.75 1L5.375 5.16667L1 1" stroke="#5A5A5A" strokeWidth="2" />
                      </svg>
                    </button>
                    <div className="top-menu-child-container">
                      <ul className="flex-column top-menu-child">
                        {
                          childItems.map((childEl, childIndex) => {
                            const activeChild = (
                              (location.hash && childEl.link.includes(location.hash)) ||
                              (!location.hash && !childEl.link.includes('#'))
                            ) ? 'active-child current-child' : 'active-child'

                            return (
                              <li key={childIndex}>
                                <NavLink
                                  exact to={langPrefix + childEl.link}
                                  activeClassName={activeChild}
                                  dangerouslySetInnerHTML={{ __html: childEl.name }} />
                              </li>
                            )
                          })
                        }
                      </ul>
                    </div>
                  </li>
                )
              }

              if (!el.parent_id) {
                return (
                  <li key={index}>
                    <NavLink
                      to={langPrefix + el.link}
                      activeClassName={activeClass}
                      dangerouslySetInnerHTML={{ __html: el.name }} />
                  </li>
                )
              }
            })
          }

          <NavRegions openRegionsList={toggleChildMenu} />
        </ul>
      </div>
    </nav>
  ) : null
}

export default Nav
