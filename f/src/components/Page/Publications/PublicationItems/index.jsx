import React, { useEffect } from 'react'
import './style.scss'
import { useTemplateContext } from '../../../../contexts/TemplateContext'

const ItemsClassName = 'publications'

const PublicationItems = props => {
  const lang = useTemplateContext().lang

  const items = props.items
  return items?.length ?
    <div className={`flex-column ${ItemsClassName}`}>
      <div className={'wrapper-inner'}>
        {
          items.map((item, index) =>
            <div className={` ${ItemsClassName}-item`} key={index}>
              <div className={`${ItemsClassName}-item-image`}>
                {item.video &&
                  <a href={item.video}>
                    <img src={item.previewImage?.src} alt={item.name}/>
                  </a>
                }
                {item.link &&
                  <a href={item.link}>
                    <img src={item.previewImage?.src} alt={item.name}/>
                  </a>
                }
                {item.file &&
                  <a href={item.file.src}>
                    <img src={item.previewImage?.src} alt={item.name}/>
                  </a>
                }
                {!item.video && !item.link && !item.file &&
                    <img src={item.previewImage?.src} alt={item.name}/>
                }

              </div>

              <div className={`${ItemsClassName}-item-data`}>
                <div  className={`${ItemsClassName}-item-data-name`} dangerouslySetInnerHTML={{ __html: item.name }}/>
                <div className={`${ItemsClassName}-item-data-date`}>
                  <div dangerouslySetInnerHTML={{ __html: item.date }}/>
                  <div dangerouslySetInnerHTML={{ __html: item.sectionName }}/>
                </div>
                <div className={`${ItemsClassName}-item-data-link`}>
                  {item.video && <a href={item.video} target="_blank" rel="noreferrer">{lang === 'en' ? 'Watch video' : 'Смотреть'}</a>}
                  {item.link && <a href={item.link} target="_blank" rel="noreferrer">{lang === 'en' ? 'Read' : 'Читать'}</a>}
                  {item.file && <a href={item.file.src} target="_blank" rel="noreferrer">{lang === 'en' ? 'Download file' : 'Скачать'}</a>}
                </div>
              </div>
            </div>
          )
        }
      </div>
    </div> : null
}

export default PublicationItems
