const getJsonFromResponse = response => response.json()
const handleApiError = response => {
  if (!response.ok) {
    throw new Error(response.statusText)
  }

  return response
}

/**
 * Получаем параметры шаблона
 *
 * @param lang
 * @returns {Promise<Response>}
 */
export function fetchTemplateSettings(lang = 'ru') {
  const url = `/local/api/?action=template.getSettings&lang=${lang}`
  return fetch(url, { mode: 'no-cors' }).then(handleApiError).then(getJsonFromResponse)
}

/**
 * Получаем пункты верхнего меню
 *
 * @param lang
 * @returns {Promise<Response>}
 */
export function fetchTopMenu(lang = 'ru') {
  const url = `/local/api/?action=menus.getTopMenu&lang=${lang}`
  return fetch(url, { mode: 'no-cors' }).then(handleApiError).then(getJsonFromResponse)
}

/**
 * Получаем пункты нижнего меню
 *
 * @param lang
 * @returns {Promise<Response>}
 */
export function fetchBottomMenu(lang = 'ru') {
  const url = `/local/api/?action=menus.getBottomMenu&lang=${lang}`
  return fetch(url, { mode: 'no-cors' }).then(handleApiError).then(getJsonFromResponse)
}

/**
 * Получаем список регионов
 *
 * @param lang
 * @returns {Promise<Response>}
 */
export function fetchRegions(lang = 'ru') {
  const url = `/local/api/?action=tools.getRegions&lang=${lang}`
  return fetch(url, { mode: 'no-cors' }).then(handleApiError).then(getJsonFromResponse)
}

/**
 * Получаем параметры и контент страницы сайта
 *
 * @param pageCode
 * @param lang
 * @returns {Promise<Response>}
 */
export function fetchPage(pageCode = 'home', lang = 'ru') {
  const url = `/local/api/?action=pages.getItem&code=${pageCode}&lang=${lang}`
  return fetch(url)
    .then(handleApiError)
    .then(getJsonFromResponse)
}

/**
 * Получаем список страниц
 *
 * @returns {Promise<Response>}
 */
export function fetchPages() {
  return fetch('/local/api/?action=pages.getList').then(handleApiError).then(getJsonFromResponse)
}

/**
 * Получаем товары каталога
 *
 * @returns {Promise<Response>}
 */
export function fetchCatalog(region = 'RU', lang = 'ru') {
  return fetch(`/local/api/?action=catalog.getList&region=${region}&lang=${lang}`)
    .then(handleApiError)
    .then(getJsonFromResponse)
}

/**
 * Получаем товар каталога
 *
 * @returns {Promise<Response>}
 */
export function fetchCatalogItem(code, region = 'RU', lang = 'ru') {
  return fetch(`/local/api/?action=catalog.getItem&code=${code}&region=${region}&lang=${lang}`)
    .then(handleApiError)
    .then(getJsonFromResponse)
}

/**
 * Получаем разделы каталога
 *
 * @returns {Promise<Response>}
 */
export function fetchSections(region = 'RU', lang = 'ru') {
  const url = `/local/api/?action=catalog.getSections&region=${region}&lang=${lang}`
  return fetch(url, { mode: 'no-cors' })
    .then(handleApiError)
    .then(getJsonFromResponse)
}

/**
 * Получаем specialties
 *
 * @returns {Promise<Response>}
 */
export function fetchSpecialtiesItem(code, region = 'RU', lang = 'ru') {
  return fetch(`/local/api/?action=specialties.getItem&code=${code}&region=${region}&lang=${lang}`)
    .then(handleApiError)
    .then(getJsonFromResponse)
}

/**
 * Получаем баннеры для карусели на главной странице
 *
 * @param lang
 * @returns {Promise<Response>}
 */
export function fetchBanners(lang = 'ru') {
  const url = `/local/api/?action=slider.getSlider&lang=${lang}`
  return fetch(url, { mode: 'no-cors' }).then(handleApiError).then(getJsonFromResponse)
}

/**
 * Получаем список избранных страниц для блока на главной
 *
 * @returns {Promise<Response>}
 */
export function fetchFavorites(lang = 'ru') {
  return fetch(`/local/api/?action=advanced.getMainpageBlocks&lang=${lang}`)
    .then(handleApiError)
    .then(getJsonFromResponse)
}

/**
 * Получаем ссылки на социальные сети
 *
 * @returns {Promise<Response>}
 */
export function fetchSocialLinks(lang = 'ru') {
  return fetch(`/local/api/?action=socials.getList&lang=${lang}`)
    .then(handleApiError)
    .then(getJsonFromResponse)
}

/**
 * Получаем ссылки для шеринга в соц-сетях
 *
 * @returns {Promise<Response>}
 */
export function fetchShareLinks(lang = 'ru') {
  return fetch(`/local/api/?action=socials.getShareList&lang=${lang}`)
    .then(handleApiError)
    .then(getJsonFromResponse)
}

/**
 * Получаем список новостей
 *
 * @returns {Promise<Response>}
 */
export function fetchNewsItems(region = 'RU', lang = 'ru') {
  const url = `/local/api/?action=news.getList&region=${region}&lang=${lang}`
  return fetch(url, { mode: 'no-cors' })
    .then(handleApiError)
    .then(getJsonFromResponse)
}

/**
 * Получаем страницу новости
 *
 * @returns {Promise<Response>}
 */
export function fetchNewsItem(code, lang = 'ru') {
  const url = `/local/api/?action=news.getItem&code=${code}&lang=${lang}`
  return fetch(url, { mode: 'no-cors' })
    .then(handleApiError)
    .then(getJsonFromResponse)
}

/**
 * Получаем публикации
 *
 * @returns {Promise<Response>}
 */
export function fetchPublications(region = 'RU', lang = 'ru') {
  const url = `/local/api/?action=publications.getList&region=${region}&lang=${lang}`
  return fetch(url, { mode: 'no-cors' })
    .then(handleApiError)
    .then(getJsonFromResponse)
}

/**
 * Получаем вебинары и shows
 *
 * @returns {Promise<Response>}
 */
export function fetchEvents(region = 'RU', lang = 'ru', old = false) {
  let oldTail = ''
  if (old) {
    oldTail = '&old=Y'
  }
  const url = `/local/api/?action=events.getList&region=${region}&lang=${lang}${oldTail}`
  return fetch(url, { mode: 'no-cors' })
    .then(handleApiError)
    .then(getJsonFromResponse)
}

/**
 * Получаем форму обратной связи
 *
 * @returns {Promise<Response>}
 */
export function fetchFeedbackForm(lang = 'ru') {
  return fetch(`/local/api/?action=forms.getFeedback&lang=${lang}`)
    .then(handleApiError)
    .then(getJsonFromResponse)
}

/**
 * Получаем результаты поиска
 *
 * @returns {Promise<Response>}
 */
export function fetchSearch(query, lang = 'ru') {
  return fetch(`/local/api/?action=search.getList&q=${query}&lang=${lang}`)
    .then(handleApiError)
    .then(getJsonFromResponse)
}

/**
 * Сохраняем форму обратной связи
 *
 * @returns {Promise<Response>}
 */
export function saveFeedbackForm(data, lang = 'ru') {
  return fetch(`/local/api/?action=forms.saveForm&lang=${lang}`, {
    method: 'POST',
    body: JSON.stringify(data)
  })
    .then(handleApiError)
    .then(getJsonFromResponse)
}
