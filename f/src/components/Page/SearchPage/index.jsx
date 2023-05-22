import React, { useEffect, useState } from 'react'
import './style.scss'
import { useTemplateContext, useTemplateContextValue } from '../../../contexts/TemplateContext'
import { useDispatch, useSelector } from 'react-redux'
import { fetchPage } from '../../../store'
import { inFinalState, inInitialState } from '../../../store/helpers'
import { fetchSearch } from '../../../api'
import NotFoundPage from '../NotFoundPage'

function getGetParam(name) {
  const param = (new RegExp('[?&]' + encodeURIComponent(name) + '=([^&]*)')).exec(location.search)
  if (param) {
    return decodeURIComponent(param[1])
  }
}

const SearchPage = () => {
  const lang = useTemplateContext().lang
  const templateContextValue = useTemplateContextValue(lang)
  const [itemsFull, setItemsFull] = useState([])
  const [itemsCount, setItemsCount] = useState(0)
  const [query, setQuery] = useState(getGetParam('q'))
  const pageCode = 'search'
  const dispatch = useDispatch()
  const pageStoreChunk = useSelector(store => store['page'][pageCode])
  const maxCount = 30
  const [showAll, setShowAll] = useState(false)

  const declOfNum = (number, titles) => {
    const cases = [2, 0, 1, 1, 1, 2]
    return titles[ (number % 100 > 4 && number % 100 < 20) ? 2 : cases[(number % 10 < 5) ? number % 10 : 5] ]
  }

  useEffect(() => {
    if (!pageStoreChunk || inInitialState(pageStoreChunk)) {
      dispatch(fetchPage(pageCode, lang))
    }
    document.documentElement.scrollTop = 0
  }, [dispatch, pageStoreChunk])

  const submit = event => {
    if (!query) {
      return
    }
    fetchSearch(query, lang).then(resp => {
      setItemsFull(resp.full)
      setItemsCount(resp.cnt)

      const queryParams = new URLSearchParams(window.location.search)
      queryParams.set('q', query)
      history.replaceState(null, null, window.location.pathname + '?' + queryParams.toString())
    })
    document.documentElement.scrollTop = 0
  }

  useEffect(() => {
    if (query) {
      submit()
    }
  }, [])

  const inputHandler = event => {
    if (['Enter', 'NumpadEnter'].includes(event.code)) {
      submit()
    }
  }
  const inputChangeHandler = event => {
    setQuery(event.target.value)
  }
  const howMoreHandler = event => {
    setShowAll(true)
  }
  if (!inFinalState(pageStoreChunk)) {
    return null
  }

  const { data: pageData } = pageStoreChunk

  return pageData ? (
    <div className="container page-search">
      <div className="wrapper">
        <div className="SearchInputPage">
          <input value={query} type="text" placeholder={templateContextValue.templateSettings.textSearch}
            onKeyUp={inputHandler} onChange={inputChangeHandler}/>
          <div className="submit" onClick={submit}>{lang === 'en' ? 'Search' : 'Поиск'}</div>
        </div>

        {lang === 'en' && <div className="items-count"> {itemsCount} result{declOfNum(itemsCount, ['', 's', 's'])} found </div>}
        {lang !== 'en' && <div className="items-count"> Найден{declOfNum(itemsCount, ['', 'о', 'о'])}  {itemsCount} результат{declOfNum(itemsCount, ['', 'а', 'ов'])} </div>}

        {itemsFull.length > 0 &&
        <div className="search-items">
          {
            itemsFull.map((item, index) => (showAll || index < maxCount ?
              <div className="item" key={index}>
                <div className="item-title" dangerouslySetInnerHTML={{ __html: item.name }}/>
                <div className="item-descr" dangerouslySetInnerHTML={{ __html: item.descr }}/>
                <a className="item-link" href={item.link}>{item.link}</a>
                {item.excluded && <div className="item-excluded">{item.excluded}</div>}
              </div> : null)
            )
          }
        </div>}

        {itemsCount > 0 && itemsCount > maxCount && !showAll &&
          <div className="show-more" onClick={howMoreHandler}>Show all {itemsCount}</div>
        }

      </div>
    </div>
  ) : <NotFoundPage/>
}

export default SearchPage
