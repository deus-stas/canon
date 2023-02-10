import React                  from 'react'
import { Link }               from 'react-router-dom'

import './style.scss'
import { useTemplateContext } from '@contexts/TemplateContext'

const closeFeedbackFormPopup = () => {
  document.body.style.paddingRight = '0'
  document.body.classList.remove('feedback-form-popup-is-open')
  document.documentElement.scrollTop = 0
}

export const openFeedbackFormPopup = () => {
  document.body.style.paddingRight = (window.innerWidth - document.documentElement.clientWidth) + 'px'
  document.body.classList.add('feedback-form-popup-is-open')
}

export const FeedbackFormPopup = () => {
  const lang = useTemplateContext().lang
  const langPrefix = (lang === 'ru') ? '' : '/' + lang

  return (
    <div className="flex-center feedback-form-popup-container">
      <div onClick={closeFeedbackFormPopup} className="feedback-form-overlay" />

      <div className="container flex-center feedback-form-popup">
        <p className="flex-center feedback-form-popup-title">
          <svg width="17" height="15" viewBox="0 0 17 15" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1 7.25641L5.85549 13L16 1" stroke="black" strokeWidth="2" />
          </svg>
          <span>{lang === 'ru' ? 'Форма отправлена' : 'Form has been sent'}</span>
        </p>

        <div className="flex-center feedback-form-popup-buttons">
          <button onClick={closeFeedbackFormPopup}>{lang === 'ru' ? 'Продолжить' : 'Continue'}</button>
          <Link onClick={closeFeedbackFormPopup}
            to={`${langPrefix}/`}>{lang === 'ru' ? 'На главную' : 'To home page'}</Link>
        </div>
      </div>
    </div>
  )
}
