import React from 'react'
import Nav, { Hamburger } from './Nav'
import Breadcrumbs from './Breadcrumbs'
import LanguageSwitcher from './LanguageSwitcher'
import Logo from './Logo'
import { Regions } from './Regions'
import { useTemplateContext } from '@contexts/TemplateContext'
import SearchInput from './SearchInput'

import './style.scss'

const Header = () => {
  const templateSettings = useTemplateContext().templateSettings
  document.title = templateSettings.name.replace(/&laquo;/, '«').replace(/&raquo;/, '»')

  return (
    <>
      <header className="container header">
        <div className="flex-center wrapper">
          <Logo />
          <SearchInput/>
          <Hamburger/>
        </div>
      </header>

      <Nav/>

      <div className="container top-container">
        <div className="flex-center wrapper">
          <Breadcrumbs/>
          <Regions />
          <LanguageSwitcher/>
        </div>
      </div>
    </>
  )
}

export default Header
