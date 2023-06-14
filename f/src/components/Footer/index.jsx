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
        <div className="footer__disclaimer wrapper">
          {lang === 'ru' ? 'Обращаем внимание: по причине различия регуляторных требований к медицинским изделиям в разных странах, продукты, функции и принадлежности, представленные на этой веб-странице ООО «АрПи Канон Медикал Системз», доступны не во всех странах и регионах. Для получения подробных и актуальных сведений о доступности продуктов, функций и принадлежностей, обратитесь к представителю ООО «АрПи Канон Медикал Системз» в вашем регионе.'
            :
            'Disclaimer: due to medical device regulatory reasons, not all products, functions and accessories displayed on this RP Canon Medical Systems, LLC webpage are available in all countries and regions. For information about future availability of the products, functions and accessories please contact your local RP Canon Medical Systems, LLC representative for further details.'
          }
        </div>
      </footer>
    </>
  )
}

export default Footer
