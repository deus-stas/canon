import React, { useEffect } from 'react'
import './style.scss'
import { Link } from 'react-router-dom'
import { useTemplateContext } from '../../../contexts/TemplateContext'

const ContactUs = () => {
  const lang = useTemplateContext().lang
  const langPrefix = lang === 'en' ? '/en' : ''
  const btnText = lang === 'en' ? 'Contact Us' : 'Написать нам'

  if (location.pathname === langPrefix + '/contacts/') {
    return null
  }
  return (<Link className="btn-contact" to={langPrefix + '/contacts/'}> {btnText} </Link>)
}

export default ContactUs
