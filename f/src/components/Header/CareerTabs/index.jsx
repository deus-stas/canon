import React, { useEffect, useState } from 'react'
import { useTemplateContext } from '@contexts/TemplateContext'

const CareerTabs = () => {
  const lang = useTemplateContext().lang

  let items = []

  switch (lang) {
    case 'ru':
      items = [
        { name: 'Карьера', link: '/about-us/vacancy/' },
        { name: 'Преимущества', link: '/about-us/benefits/' },
        { name: 'Жизнь в компании', link: '/about-us/life-at-canon/' }
      ]
      break
    default:
      items = [
        { name: 'Careers', link: '/en/about-us/vacancy/' },
        { name: 'Benefits', link: '/en/about-us/benefits/' },
        { name: 'Life at Canon', link: '/en/about-us/life-at-canon/' }
      ]
  }

  // location.pathname

  const curTab = items.findIndex(item => item.link === location.pathname)

  useEffect(() => {

  }, [])

  return  (
    <div className="tabs">
      {items.map((item, index) => (
        <a href={item.link} key={index} className={'tab ntd ' + (curTab === index ? 'active' : '')}>
          {item.name}
        </a>
      ))}

    </div>
  )
}

export default CareerTabs
