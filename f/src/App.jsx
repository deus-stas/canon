import React, {useEffect, useState} from 'react'
import { BrowserRouter as Router } from 'react-router-dom'
import { PagesContext, usePagesContextValue } from './contexts/PagesContext'
import { TemplateContext, useTemplateContextValue } from './contexts/TemplateContext'
import Page from './components/Page'
import { getCurrentLang } from './components/Header/LanguageSwitcher'

const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0 ? 'is-mac' : 'not-mac'
document.body.classList.add(isMac)

const App = () => {
  const pagesContextValue = usePagesContextValue()
  const lang = getCurrentLang()
  const templateContextValue = useTemplateContextValue(lang)

  

  if (!pagesContextValue || !templateContextValue) {
    return null
  }

  return (
    <PagesContext.Provider value={pagesContextValue}>
      <TemplateContext.Provider value={templateContextValue}>
        <Router>
          {/* {showModal && <Disclaimer setShowModal={setShowModal} />} */}
          {/* {showModal && <Disclaimer handleAgree={handleAgree} handleDisagree={handleDisagree} />} */}
          <Page />
        </Router>
      </TemplateContext.Provider>
    </PagesContext.Provider>
  )
}

export default App
