import React  from 'react'
import Nav, { Hamburger } from './Nav'
import Breadcrumbs from './Breadcrumbs'
import LanguageSwitcher from './LanguageSwitcher'
import Logo from './Logo'
import { Regions } from './Regions'
import { useTemplateContext } from '@contexts/TemplateContext'
import SearchInput from './SearchInput'

import './style.scss'

// const [showModal, setShowModal] = useState(false)

const Header = () => {
  // const [showModal, setShowModal] = useState(false)

  // useEffect(() => {
  //   setTimeout(() => {
  //     setShowModal(true)
  //   }, 3000);
  // })

  // const openModal = () => {
  //   setShowModal(true)
  // }
  const templateSettings = useTemplateContext().templateSettings
  document.title = templateSettings.name.replace(/&laquo;/, '«').replace(/&raquo;/, '»')

  return (
    <>
      <header className="container header">
        <div className="flex-center wrapper">
          <Logo />
          <SearchInput/>
          <Hamburger/>
          {/* <div className="App">
            <button onClick={openModal}>Open Modal</button>
            {showModal ? <Disclaimer setShowModal={setShowModal} /> : null}
          </div> */}
        </div>
      </header>

      <Nav/>

      {/* {showModal && <Disclaimer setShowModal={setShowModal} />} */}

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
