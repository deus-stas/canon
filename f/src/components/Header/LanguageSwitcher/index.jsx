import React from 'react'
import { useTemplateContext } from '@/contexts/TemplateContext'

import './style.scss'

const langList = [
  'ru',
  'en'
]

const LanguageSwitcher = () => {
  const lang = useTemplateContext().lang

  return (
    <div className="language-switcher">
      {
        langList.map((buttonLang, index) => <button  className={(lang === buttonLang) ? 'active' : null}  data-lang={buttonLang} key={index} onClick={switchLang}>{buttonLang}</button>)
      }
    </div>
  )
}

export const installCurrentLang = (langAbbr = 'ru') => {
  const regExpTemplate = new RegExp(`^(\/(${langList.join('|')})$|\/(${langList.join('|')})\/)`)
  localStorage.setItem('lang', langAbbr)
  const path = window.location.pathname.replace(regExpTemplate, '/')
  if (langAbbr === 'ru') {
    window.location.pathname = path
  } else {
    window.location.pathname = `/${langAbbr}${path}`
  }
}

export const getCurrentLang = () => (
  window.location.pathname.match(/^(\/en$|\/en\/)/) ? 'en' : 'ru'
)

export const switchLang = e => {
  installCurrentLang(e.target.dataset.lang)
}

export default LanguageSwitcher
