import React, { useEffect, useState } from 'react'

import './style.scss'

const ClinicalGalleryItem = props => {
  const item = props.item
  const [curPhoto, setCurPhoto] = useState(0)

  return (
    <div className="item row">

      <div className="col-name">
        <h3>{item.name}</h3>
        <p dangerouslySetInnerHTML={{ __html: item.photos[curPhoto].name }}/>
      </div>
      <div className="col-img">
        {item.photos[curPhoto].videoUrl && <iframe src={item.photos[curPhoto].videoUrl} allowFullScreen="yes" frameBorder="0" scrolling="no"/>}
        {!item.photos[curPhoto].videoUrl && <img src={item.photos[curPhoto].path} alt={item.photos[curPhoto].name}/>}
      </div>
      <div className="col-thumbs">
        {item.photos.map((photo, indexPhoto) => (
          <div key={indexPhoto}
            className={indexPhoto === curPhoto ? 'active' : ''}
            onClick={e => {  setCurPhoto(indexPhoto) }}>
            <img src={photo.thumb} alt={photo.name}/>
          </div>
        ))}
      </div>
    </div>)
}

export default ClinicalGalleryItem
