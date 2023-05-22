import React, { useEffect, useState } from 'react'
import { useTemplateContext } from '@contexts/TemplateContext'
import classNames from 'classnames'

import catagoryImg from '../webinar-general.png'

const WebinarsList = params => {
  const lang = useTemplateContext().lang

  const webinars = params.items;
  const old = params.old;

  const [langType, setLangType] = useState('');

  useEffect(() => {
    lang === 'ru' ? setLangType(' мск') : setLangType('+3');
  }, [])

  const handleClickLink = link => {
    if (link) {
      window.open(link, '_blank')
    }
  }  

  return (
    <div className="">

      <table className="webinar-table">
        <tbody>
          {webinars && webinars.map((show, index) => (
            <tr key={index} data-code={show.theme} className={show.externalLink ? 'hasExternalLInk' : ''}>
              <td className="webinar__category-img"><img src={show.sectionImage} alt="show.theme" /></td>
              <td dangerouslySetInnerHTML={{ __html: show.name }}
                onClick={() => { handleClickLink(show.externalLink) }} />
              <td dangerouslySetInnerHTML={{ __html: old ? show.date.slice(0, 11) : show.date + langType }}
                onClick={() => { handleClickLink(show.externalLink) }} style={{ whiteSpace: 'nowrap' }} />
              <td onClick={() => { handleClickLink(show.externalLink) }}>{show.theme}</td>
              <td onClick={() => { handleClickLink(show.externalLink) }}>{show.language}</td>
              <td onClick={() => { handleClickLink(show.videoCode) }} style={{ whiteSpace: 'nowrap' }}>
                {show.videoCode && <a>{lang === 'en' ? 'Watch' : 'Смотреть запись'}</a>}
              </td>
              <td>
                {show.externalLink &&
                  <a className="external" href={show.externalLink} target="_blank" rel="noreferrer" />
                }
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      <div className="webinar-table-xs">
        {webinars && webinars.map((show, index) => (
          <div key={index} className={show.externalLink ? 'hasExternalLInk item' : ' item'}>
            <div className="line1" onClick={() => { handleClickLink(show.externalLink) }}>
              <div className="line1-name">
                <b dangerouslySetInnerHTML={{ __html: show.name }} />
                {show.externalLink && <a className="external" href={show.externalLink} target="_blank" rel="noreferrer" />}
              </div>
              <div dangerouslySetInnerHTML={{ __html: old ? show.date.slice(0, 11) : show.date + langType }} />
            </div>
            <div className="line2">{show.theme}</div>
            <div className="line2">{show.language}</div>
            <div className="line3" onClick={() => { handleClickLink(show.videoCode) }}>
              {show.videoCode && <a>{lang === 'en' ? 'Watch' : 'Смотреть запись'}</a>}
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}

export default WebinarsList
