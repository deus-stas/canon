import React, { useEffect, useState } from 'react'

import './style.scss'

const ClinicalGalleryItem = props => {
  const item = props.item
  const [curPhoto, setCurPhoto] = useState(0)

  return (
    <div className="item row">
        {console.log("ClinicalGalleryItem:", item)}
      <div className="col-name">
        <h3>{item.name}</h3>
        <p dangerouslySetInnerHTML={{ __html: item.photos[curPhoto].name }}/>
      </div>
      <div className="col-img">
        {item.photos[curPhoto].type==='video' && <video muted controls={false} loop autoPlay src={item.photos[curPhoto].path} allowFullScreen="yes" frameBorder="0" scrolling="no"/>}
        {item.photos[curPhoto].type==='image' && <img src={item.photos[curPhoto].path} alt={item.photos[curPhoto].name}/>}
      </div>
      <div className="col-thumbs">
        {item.photos.map((photo, indexPhoto) => (
            <div
                key={indexPhoto}
                className={indexPhoto === curPhoto ? 'active' : ''}
                onClick={e => { setCurPhoto(indexPhoto) }}
            >
                {!!photo.thumb ? (
                    <img src={photo.thumb} alt={photo.name} />
                ) : (
                    <>
                        {photo.type === 'video' ? (
                            <video src={photo.path} muted={photo}/>
                        ) : (
                            <img src={photo.path} alt={photo.name} />
                        )}
                    </>
                )}
            </div>
        ))}
      </div>
    </div>)
}

export default ClinicalGalleryItem
