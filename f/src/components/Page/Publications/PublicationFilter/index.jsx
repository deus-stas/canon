import React, { useEffect, useState } from 'react'
import { useTemplateContext } from '@contexts/TemplateContext'
import './style.scss'
import classNames from 'classnames'

const PublicationFilter = props => {
  const lang = useTemplateContext().lang
  const [sortDirection, setSortDirection] = useState('asc')
  const sortCode = props.sort.split('-')[0]
  const handleKeyPress = event => {
    props.setSearchString(event.target.value)
  }
  const handleSectionSelect = event => {
    props.setSelectedSection(event.target.getAttribute('data-code'))
  }
  const sortList = [
    { code: 'date', desctop: lang === 'en' ? 'Date' : 'Дата', mobileAsc: lang === 'en' ? 'Newest firstly' : 'Сначала новые', mobileDesc: lang === 'en' ? 'Oldest firstly' : 'Сначала старые' },
    { code: 'title', desctop: lang === 'en' ? 'Title' : 'Название', mobileAsc: lang === 'en' ? 'Title A→Z' : 'Название A→Я', mobileDesc: lang === 'en' ? 'Title Z→A' : 'Название Я→A' }]

  const handleSortSelect = event => {
    const curSort = event.target.getAttribute('data-code')
    const curSortDirection = sortDirection === 'asc' ? 'desc' : 'asc'
    setSortDirection(curSortDirection)
    props.setSort(curSort + '-' + sortDirection)
  }
  return (
    <div className="publications-filter">
      <div className="wrapper-inner">
        <div className="publications-filter-line">
          <input type="text" placeholder={lang === 'en' ? 'Search publication' : 'Найти публикацию'} onKeyUp={handleKeyPress}/>
          <div className="btn">{lang === 'en' ? 'Search' : 'Поиск'}</div>
        </div>
        <div className="tabs-sections">
          {props.sections.map((item, index) =>
            <div key={index} onClick={handleSectionSelect}
              data-code={item[0]}
              className={classNames(['tabs-sections-item',
                { 'active': item[0] === props.selectedSection }])}>
              {item[0] === 'All' && lang === 'ru' ? 'Все' : item[0]} &nbsp;
              <span>{item[1]}</span>
            </div>)}
        </div>
        <div className="tabs-sections-mobile">
          <select>
            {props.sections.map((item, index) =>
              <option key={index}>
                {item[0] === 'All' && lang === 'ru' ? 'Все' : item[0]} &nbsp;
                ({item[1]})
              </option>)}
          </select>
        </div>
        <div className="publications-filter-sorts">
          {sortList.map((item, index) =>
            <div key={index} data-code={item.code} onClick={handleSortSelect}
              className={classNames(['publications-filter-sorts-item',
                {
                  'active': item.code === sortCode,
                  'asc': item.code === sortCode && sortDirection === 'asc',
                  'desc': item.code === sortCode && sortDirection === 'desc'
                }])}>
              {item.desctop}
            </div>)}
        </div>
      </div>
    </div>
  )

}

export default PublicationFilter
