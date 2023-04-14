import React from 'react'
import { Route, Switch, withRouter } from 'react-router-dom'
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
import Landing from './Landing'
import LandingItem from './Landing/LandingItem'
import LandingDay from './Landing/LandingDay'

const Page = () => {
  const lang       = useTemplateContext().lang;
  const langPrefix = (lang === 'ru') ? '' : '/' + lang;

  closeTopMenu()

  const redirectList = {
    '/about-us': '/about-us/company/',
    '/education': '/education/webinars/',
    '/service-support': '/service-support/service/',
    '/specialties': '/specialties/collaborative-imaging/'
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
      <Header />
      <Switch>
        <Route exact path={`${langPrefix}/`} component={HomePage} />
        <Route exact path={`${langPrefix}/about-us/company/`} component={DefaultPage} />
        <Route exact path={`${langPrefix}/about-us/management-message/`} component={DefaultPage} />
        <Route exact path={`${langPrefix}/about-us/vacancy/`} component={VacanciesPage} />
        <Route exact path={`${langPrefix}/about-us/benefits/`} component={CareersPage} />
        <Route exact path={`${langPrefix}/about-us/life-at-canon/`} component={CareersPage} />
        <Route exact path={`${langPrefix}/contacts/`} component={ContactsPage} />
        <Route exact path={`${langPrefix}/news/`} component={NewsPage} />
        <Route exact path={`${langPrefix}/news/:newsCode/`} component={NewsItemPage} />
        <Route exact path={`${langPrefix}/publications/`} component={PublicationsPage} />
        <Route exact path={`${langPrefix}/events/`} component={EventsPage} />
        <Route exact path={`${langPrefix}/events/:path1`} component={LandingItem} />
        <Route exact path={`${langPrefix}/events/:path1/:path2`} component={LandingDay} />
        <Route exact path={`${langPrefix}/terms-conditions/`} component={DefaultPage} />
        <Route exact path={`${langPrefix}/cookies-list/`} component={DefaultPage} />
        <Route exact path={`${langPrefix}/service-support/service/`} component={DefaultPage} />
        <Route exact path={`${langPrefix}/service-support/support/`} component={DefaultPage} />
        <Route exact path={`${langPrefix}/specialties/collaborative-imaging/`} component={DefaultPage} />
        <Route exact path={`${langPrefix}/specialties/:path1`} component={SpecialtiesPage} />
        <Route exact path={`${langPrefix}/specialties/:path1/:path2`} component={SpecialtiesPage} />
        <Route exact path={`${langPrefix}/education/about/`} component={DefaultPage} />
        <Route exact path={`${langPrefix}/education/webinars/`} component={WebinarPage} />
        <Route exact path={`${langPrefix}/products/`} component={CatalogPage} />
        <Route exact path={`${langPrefix}/products/:path1`} component={CatalogItemPage} />
        <Route exact path={`${langPrefix}/products/:path1/:path2`} component={CatalogItemPage} />
        <Route exact path={`${langPrefix}/products/:path1/:path2/:path3`} component={CatalogItemPage} />
        <Route exact path={`${langPrefix}/products/:path1/:path2/:path3/:path4`} component={CatalogItemPage} />
        <Route exact path={`${langPrefix}/search/`} component={SearchPage} />
        <Route exact path={`${langPrefix}/service-support/feedback/`} component={FeedbackPage} />
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
