/* eslint-disable max-len */
import React, { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import './style.scss'

// eslint-disable-next-line react/prop-types
const Disclaimer = ({ handleAgree, handleDisagree }) => {
  const [showModal, setShowModal] = useState(false)

  const handleAgreeClick = () => {
    setShowModal(false)
    handleAgree()
  }

  const handleDisagreeClick = () => {
    setShowModal(false)
    handleDisagree()
    window.location.replace('https://blocked.rpcanon.de-us.ru/')
  }

  return (
    showModal && (
      <div className="disclaimer-modal">
        <div className="disclaimer-content">

            <p>Согласно действующему законодательству Российской Федерации, доступ к данному сайту предоставляется исключительно медицинским и фармацевтическим работникам.</p>
            <p>Материалы, размещённые на данном сайте, не являются рекламными, a представляют собой информацию o реализуемом OOO «АрПи Канон Медикал Системз» ассортименте оборудования и eго технических параметрах.</p>
            <p>Никакая информация, представленная на настоящем сайте, не должна толковаться как гарантия положительного результата действия оборудования, его безопасности и эффективности.</p>
            <p>Получая доступ к данному сайту, Вы подтверждаете, что являетесь медицинским или фармацевтическим работником и принимаете на себя ответственность за несоблюдение указанного ограничения.</p>
            <p>Если Вы согласны c вышеизложенной информацией, нажмите кнопку «Подтверждаю»</p>

          <div className="disclaimer-actions">
            <button className="disagree" onClick={handleDisagreeClick}>Не подтверждаю</button>
            <button className="agree" onClick={handleAgreeClick}>Подтверждаю </button>
          </div>
        </div>
      </div>
    )
  )
}

export default Disclaimer
