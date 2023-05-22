import React from 'react'
import classNames from 'classnames'
import { Link, useLocation } from 'react-router-dom'
import { useTemplateContext } from '@contexts/TemplateContext'

import './style.scss'

const logoClassname = 'logo'

const Logo = () => {
  const { pathname } = useLocation()
  const templateSettings = useTemplateContext().templateSettings
  const lang = useTemplateContext().lang
  const langPrefix = (lang === 'ru') ? '' : '/' + lang

  const logos = (
    <>
      <span className={classNames(`${logoClassname}__image`)}>
        <img src={templateSettings.logo?.src} alt={templateSettings.name} />
      </span>
      <span className={classNames(`${logoClassname}__mobile-image`)}>
        <img src={templateSettings.mobileLogo?.src} alt={templateSettings.name} />
      </span>
      {templateSettings.logoText?.src ? <span className={classNames(`${logoClassname}__text`)}>
        <img src={templateSettings.logoText.src} alt={templateSettings.name} />
      </span> : null}
    </>)

  return (
    <div className={classNames(`${logoClassname}`)}>
      {
        pathname === langPrefix + '/' ?
          (<div className={classNames(`${logoClassname}__container`, 'flex-center')}>{logos}</div>) :
          (
            <Link className={classNames(`${logoClassname}__container`, 'flex-center')} to={`${langPrefix}/`}>
              {logos}
            </Link>
          )
      }
    </div>
  )
}

export default Logo
