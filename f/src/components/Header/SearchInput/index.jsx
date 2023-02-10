import React from 'react'
import { useTemplateContext } from '@contexts/TemplateContext'

import './style.scss'
import { useTemplateContextValue } from '../../../contexts/TemplateContext'

const SearchInput = () => {
  const lang = useTemplateContext().lang
  const templateContextValue = useTemplateContextValue(lang)

  const submit = event => {
    const val = document.querySelector('.SearchInput input').value
    if (!val) {
      return
    }
    let query = lang === 'en' ? '/en/' : '/'
    query += 'search?q='
    query += encodeURI(val)
    location.href = query
  }

  const inputHandler = event => {
    if (['Enter', 'NumpadEnter'].includes(event.code)) {
      submit(event)
    }
  }

  const toggleSearch = event => {
    document.querySelector('.header .SearchInput').classList.toggle('open')
  }

  return (
    <div>
      <div className="SearchInput">
        <input type="text" placeholder={templateContextValue.templateSettings.textSearch} onKeyUp={inputHandler}/>
        <div className="magnifier" onClick={submit}/>
      </div>
      <div className="SearchInput-xs" onClick={toggleSearch}/>
    </div>

  )
}

export default SearchInput
