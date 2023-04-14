import { combineReducers } from 'redux'

import banners from './banners'
import bottomMenu from './bottomMenu'
import catalog from './catalog'
import catalogSections from './catalogSections'
import catalogItem from './catalogItem'
import favorites from './favorites'
import feedbackForm from './feedbackForm'
import news from './news'
import newsItem from './newsItem'
import publications from './publications'
import events from './events'
import page from './page'
import pages from './pages'
import regions from './regions'
import shareLinks from './share'
import socialLinks from './social'
import templateSettings from './templateSettings'
import topMenu from './topMenu'
import specialties from './specialties'
import landings from './landings'
import landingsItems from './landingsItems'
import landingsDays from './landingsDays'

export * from './banners'
export * from './bottomMenu'
export * from './catalog'
export * from './catalogSections'
export * from './catalogItem'
export * from './favorites'
export * from './feedbackForm'
export * from './news'
export * from './newsItem'
export * from './publications'
export * from './events'
export * from './page'
export * from './pages'
export * from './regions'
export * from './share'
export * from './social'
export * from './templateSettings'
export * from './topMenu'
export * from './specialties'
export * from './landings'
export * from './landingsItems'
export * from './landingsDays'

export default combineReducers({
  banners,
  bottomMenu,
  catalog,
  catalogSections,
  catalogItem,
  favorites,
  feedbackForm,
  news,
  newsItem,
  publications,
  events,
  page,
  pages,
  regions,
  shareLinks,
  socialLinks,
  templateSettings,
  topMenu,
  specialties,
  landings,
  landingsItems,
  landingsDays
})
