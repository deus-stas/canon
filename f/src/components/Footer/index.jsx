import React from 'react'
import { NavLink } from 'react-router-dom'
import Social from './Social'
import FooterNav from './FooterNav'
import { useTemplateContext } from '@contexts/TemplateContext'
import { ShareLinksPopup } from './ShareLinks'
import { FeedbackFormPopup } from '@components/Page/Contacts/FeedbackForm/FeedbackFormPopup'
import ContactUs from './ContactUs'
import './style.scss'

const Footer = () => {
  const templateSettings = useTemplateContext().templateSettings

  const lang = useTemplateContext().lang
  const langPrefix = (lang === 'ru') ? '' : '/' + lang

  return (
    <>
      <ContactUs />
      <Social />
      <ShareLinksPopup />
      <FeedbackFormPopup />

      <footer className="container footer">
        <div className="flex wrapper">
          <FooterNav />
          <div className="flex-column footer-info">
            <p className="copyright" dangerouslySetInnerHTML={{ __html: templateSettings.copyright }} />
            <p className="footer-links">
              <NavLink
                to={langPrefix + '/terms-conditions/'}
                dangerouslySetInnerHTML={{ __html: templateSettings.textRegulations }}
                rel="noreferrer"
              />&nbsp;|{' '}
              <a
                target="_blank"
                href={templateSettings.privacyPolicy?.src}
                dangerouslySetInnerHTML={{ __html: templateSettings.textPrivacyPolicy }}
                rel="noreferrer"
              />
            </p>
            <p className="footer-links">
              <a
                target="_blank"
                href={templateSettings.workingConditionsFile?.src}
                dangerouslySetInnerHTML={{ __html: templateSettings.workingConditionsTitle }}
                rel="noreferrer"
              />
            </p>
          </div>
        </div>
      </footer>
    </>
  )
}

export default Footer
