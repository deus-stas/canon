import React, { useEffect, useState } from 'react'

import './style.scss'
import ClinicalGalleryItem from './ClinicalGalleryItem'

const ClinicalGallery = props => {
  const items = props.items
  const [curTab, setCurTab] = useState(0)

  return (
    <div className="ClinicalGallery">
      <div className="tabs-wrap">
        <div className="wrapper">
          <div className="tabs">
            {items.map((tab, index) => (
              <div key={index}
                onClick={e => { setCurTab(index) }}
                className={'tab ' + (curTab === index ? 'active' : '')}>
                {tab.name}
              </div>
            ))}
          </div>
        </div>
      </div>
      <div className="items-wrap">
        <div className="wrapper">
          <div className="items">
            {items[curTab] && items[curTab].items && items[curTab].items.map((item, index) => (
              (item.photos && <ClinicalGalleryItem key={index} item={item}/>)))}
          </div>
        </div>
      </div>

    </div>
  )
}

export default ClinicalGallery
