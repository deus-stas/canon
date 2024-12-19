import React, { useEffect, useState } from 'react'
import { Route, Switch, withRouter, useLocation } from 'react-router-dom'
import ContactsPage from './Contacts'
import Footer from '../Footer'
import Header from '../Header'
import HomePage from './Home'
import NewsPage from './News'
import CatalogPage from './Catalog'
import CatalogItemPage from './Catalog/CatalogItem'
import NewsItemPage from './News/NewsItemPage'
import NotFoundPage from './NotFoundPage'
import { useTemplateContext } from '@/contexts/TemplateContext'
import { closeTopMenu } from '@/components/Header/Nav'
import PublicationsPage from './Publications'
import DefaultPage from './DefaultPage'
import EventsPage from './Events'
import SearchPage from './SearchPage'
import SpecialtiesPage from './Specialties'
import WebinarPage from './Webinars'
import VacanciesPage from './Vacancies'
import CareersPage from './Careers'
import FeedbackPage from './FeedbackPage'
import Postwarranty from './Postwarranty'
import MrtModernization from './MrtModernization'
import Landing from './Landing'
import LandingItem from './Landing/LandingItem'
import LandingDay from './Landing/LandingDay'
import Disclaimer from '../Header/Disclaimer'
import PartneryPage from './Partnery'

const Page = () => {
  const location = useLocation()
  const lang       = useTemplateContext().lang
  const langPrefix = (lang === 'ru') ? '' : '/' + lang

  const [showModal, setShowModal] = useState(false)
  const [disclaimerAgreed, setDisclaimerAgreed] = useState(sessionStorage.getItem('disclaimerAgreed') === 'true')

  useEffect(() => {
    const agreed = sessionStorage.getItem('disclaimerAgreed')
    if (agreed !== 'true') {
      setShowModal(true)
    }
  }, [])

  const handleAgree = () => {
    sessionStorage.setItem('disclaimerAgreed', 'true')
    setShowModal(false)
  }

  const handleDisagree = () => {
    sessionStorage.setItem('disclaimerAgreed', 'false')
    setShowModal(false)
    window.location.replace('https://blocked.rpcanon.de-us.ru/')
  }

  closeTopMenu()

  const redirectList = {
    '/about-us': '/about-us/company/',
    '/education': '/education/webinars/',
    '/service-support': '/service-support/service/',
    '/specialties': '/specialties/collaborative-imaging/',
    '/events': '/events/ochnye/'
  }
  let path = location.pathname.replace(/^\/|\/$/g, '')
  if (langPrefix.length > 1) {
    path = path.substr(langPrefix.length - 1)
  } else {
    path = '/' + path
  }
  if (redirectList[path]) {
    location.href = langPrefix + redirectList[path]
  }

  return (
    <>
    {showModal && <Disclaimer handleAgree={handleAgree} handleDisagree={handleDisagree} />}
      <Header />
      <Switch>
        <Route exact path={`${langPrefix}/`} component={HomePage} />
        <Route exact path={`${langPrefix}/about-us/company/`} component={DefaultPage} />
        {/*<Route exact path={`${langPrefix}/about-us/partnery/`} component={DefaultPage} />*/}
        <Route exact path={`${langPrefix}/about-us/management-message/`} component={DefaultPage} />
        <Route exact path={`${langPrefix}/about-us/vacancy/`} component={VacanciesPage} />
        <Route exact path={`${langPrefix}/about-us/benefits/`} component={CareersPage} />
        <Route exact path={`${langPrefix}/about-us/life-at-canon/`} component={CareersPage} />
        <Route exact path={`${langPrefix}/about-us/partnery/`} component={PartneryPage} />
        <Route exact path={`${langPrefix}/contacts/`} component={ContactsPage} />
        <Route exact path={`${langPrefix}/news/`} component={NewsPage} />
        <Route exact path={`${langPrefix}/news/:newsCode/`} component={NewsItemPage} />
        <Route exact path={`${langPrefix}/publications/`} component={PublicationsPage} />
        <Route exact path={`${langPrefix}/events/:eventType`} component={EventsPage} />
        {/* <Route exact path={`${langPrefix}/events/ochnye/:path1`} component={LandingItem} /> */}
        <Route exact path={`${langPrefix}/events/:eventType/:path1`} component={LandingDay} />
        <Route exact path={`${langPrefix}/terms-conditions/`} component={DefaultPage} />
        <Route exact path={`${langPrefix}/cookies-list/`} component={DefaultPage} />
        <Route exact path={`${langPrefix}/service-support/service/`} component={DefaultPage} />
        <Route exact path={`${langPrefix}/service-support/support/`} component={DefaultPage} />
        <Route exact path={`${langPrefix}/specialties/collaborative-imaging/`} component={DefaultPage} />
        <Route exact path={`${langPrefix}/specialties/:path1`} component={SpecialtiesPage} />
        <Route exact path={`${langPrefix}/specialties/:path1/:path2`} component={SpecialtiesPage} />
        <Route exact path={`${langPrefix}/education/about/`} component={DefaultPage} />
        {/* <Route exact path={`${langPrefix}/events/webinars/`} component={WebinarPage} /> */}
        <Route exact path={`${langPrefix}/products/`} component={CatalogPage} />
        <Route exact path={`${langPrefix}/products/:path1`} component={CatalogItemPage} />
        <Route exact path={`${langPrefix}/products/:path1/:path2`} component={CatalogItemPage} />
        <Route exact path={`${langPrefix}/products/:path1/:path2/:path3`} component={CatalogItemPage} />
        <Route exact path={`${langPrefix}/products/:path1/:path2/:path3/:path4`} component={CatalogItemPage} />
        <Route exact path={`${langPrefix}/search/`} component={SearchPage} />
        <Route exact path={`${langPrefix}/service-support/warranty/`} component={FeedbackPage} />
        <Route exact path={`${langPrefix}/service-support/post-warranty/`} component={Postwarranty} />
        <Route exact path={`${langPrefix}/service-support/mrt-modernization/`} component={MrtModernization} />
        <Route exact path={`${langPrefix}/landings`} component={Landing} />
        <Route exact path={`${langPrefix}/landings/:path1`} component={LandingItem} />
        <Route exact path={`${langPrefix}/landings/:path1/:path2`} component={LandingDay} />
        <Route component={NotFoundPage} />
      </Switch>
      <Footer />
    </>
  )
}

export default withRouter(Page)
