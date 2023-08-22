import React, { useEffect, useState } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchWarrantyForm } from '@store'
import { inFinalState, inInitialState } from '@store/helpers'
import { useTemplateContext } from '@contexts/TemplateContext'
import { saveFormWarranty } from '@/api'
import { openFeedbackFormPopup } from './FeedbackFormPopup'

import './style.scss'
import classNames from 'classnames'

const FeedbackForm = (props) => {
  const lang = useTemplateContext().lang
  const templateSettings = useTemplateContext().templateSettings
  const dispatch = useDispatch()
  const feedbackFormStoreChunk = useSelector(store => store['feedbackForm'])
  const [values, setValues] = useState({})
  const [showRegion, setShowRegion] = useState(false)

  useEffect(() => {
    if (!feedbackFormStoreChunk || inInitialState(feedbackFormStoreChunk)) {
      dispatch(fetchWarrantyForm(lang))
    }
  }, [dispatch, feedbackFormStoreChunk])

  useEffect(() => {
    if (!props.related) {
      setValues({ form_checkbox_related: lang === 'ru' ? ['752'] : ['870'] })
    }
  }, [])

  if (!inFinalState(feedbackFormStoreChunk)) {
    return null
  }

  const { data: feedbackForm } = feedbackFormStoreChunk
  const feedbackFormInputs = Object.values(feedbackForm.QUESTIONS)

  if (!showRegion) {
    const index = feedbackFormInputs.findIndex(item => item.SID === 'region')
    delete feedbackFormInputs[index]
  }

  const handleCountrySelect = event => {
    // для РФ показываем выбор региона
    const value = event.target.value
    if (!value) return
    const item = feedbackFormInputs.find(item => item.SID === 'country')
    if (!item) return
    const option = item.ANSWERS.find(item => item.ID === value)
    if (!option) return
    setShowRegion(['Россия', 'Russia'].includes(option.MESSAGE))
  }

  const formError = (e, message) => {
    const messageContainer = e.target.querySelector('.message-container ins')
    messageContainer.parentNode.classList.add('not-empty')
    messageContainer.innerText = message
  }

  const handleChange = e => {
    e.target.classList.remove('empty-input')
    if (e.target.name.includes('[]')) {
      // Обработка значений чекбоксов
      const targetName = e.target.name.replace(/\[]/g, '')
      const valuesArr = values[targetName] || []
      if (valuesArr.length && valuesArr.includes(e.target.value)) {
        values[targetName].filter(el => el !== e.target.value)
        setValues({
          ...values,
          [targetName]: values[targetName].filter(el => el !== e.target.value)
        })
      } else {
        setValues({
          ...values,
          [targetName]: valuesArr.length && [...valuesArr, e.target.value] || [e.target.value]
        })
      }
    } else {
      // Прочие поля
      setValues({
        ...values,
        [e.target.name]: e.target.value
      })
    }
  }

  const getEmptyInputs = e => {
    const requiredInputs = Array.from(e.target.querySelectorAll('.required'))
    return requiredInputs.filter(el => {
      if (el.type === 'checkbox')
        return !el.checked
      else
        return !el.value
    })
  }

  const validateForm = e => {
    console.log(values);
    const emptyInputs = getEmptyInputs(e)
    if (emptyInputs.length) {
      emptyInputs.map(el => {
        el.classList.add('empty-input')
      })
      emptyInputs[0].parentElement.scrollIntoView()
      return false
    }
    return true
  }

  const handleSubmit = e => {
    e.preventDefault()

    const buttonSubmit = e.target.querySelector('button[type="submit"]')
    buttonSubmit.disabled = true

    const messageContainer = e.target.querySelector('.message-container')
    messageContainer.classList.remove('not-empty')

    const submitForm = () => {
      const data = {
        'form_id': e.target.dataset.formId,
        action: 'forms.saveFormWarranty',
        values
      }

      saveFormWarranty(data, lang).then(response => {
        if (response.status === 'ok') {
          openFeedbackFormPopup()

          const inputs = Array.from(e.target.querySelectorAll('input, textarea'))
          inputs.map(el => {
            if (el.type === 'checkbox')
              el.checked = false
            else
              el.value = ''
          })

          const selects = Array.from(e.target.querySelectorAll('select'))
          selects.map(el => {
            el.value = ''
          })

        } else {
          formError(e, lang === 'ru' ? 'Что-то пошло не так' : 'Something went wrong')
        }

        buttonSubmit.disabled = false
      })
    }

    const isValid = validateForm(e)
    if (isValid) {
      if (typeof grecaptcha !== 'undefined') {
        grecaptcha.ready(() => {
          grecaptcha.execute(templateSettings.recaptchaKey, { action: 'submit' }).then(token => {
            submitForm()
          })
        })
      } else {
        submitForm()
      }

    } else {
      buttonSubmit.disabled = false
    }
  }

  return feedbackFormInputs.length ? (
    <div className="feedback-form-container">
      <h3>
        {lang === 'ru' ? props.title : 'Feedback'}
      </h3>
      <form data-form-id={feedbackForm.ID} onSubmit={handleSubmit}
        className="flex feedback-form" id="feedback-form" action="/" method="post">
          {
            console.log('ip', feedbackFormInputs)
          }
        {
          feedbackFormInputs.map((input, index) => {
            const inputRequired = input.REQUIRED === 'Y' ? 'required' : ''
            const inputSID = input.SID

            switch (inputSID) {
              case 'agree':
              case 'agree_mail':
                return (
                  <div key={index} className="agreement-container">
                    <AgreementField
                      handleChange={handleChange}
                      title={input.TITLE}
                      sid={inputSID}
                      required={inputRequired}
                      answers={input.ANSWERS} />
                  </div>
                )

              case 'related':
                {
                  if (props.related) {
                    return (
                      <div key={index} className="flex-column feedback-form-row">
                        <CheckboxesField
                          handleChange={handleChange}
                          title={input.TITLE}
                          sid={inputSID}
                          required={inputRequired}
                          answers={input.ANSWERS}
                          values={values['form_checkbox_related']}
                        />
                      </div>
                    )
                  } else {
                    return (
                      <div key={index} className="flex-column feedback-form-row --hidden">
                        <CheckboxesField
                          handleChange={handleChange}
                          title={input.TITLE}
                          sid={inputSID}
                          required={inputRequired}
                          answers={input.ANSWERS}
                          defaultValue={752}
                          values={values['form_checkbox_related']}
                        />
                      </div>
                    )
                  }
                }

              case 'email':
                return (
                  <div key={index} className="flex-column input-container">
                    <EmailField
                      handleChange={handleChange}
                      required={inputRequired}
                      answers={input.ANSWERS}
                    />
                  </div>
                )

              case 'phone':
                return (
                  <div key={index} className="flex-column input-container">
                    <PhoneField
                      handleChange={handleChange}
                      required={inputRequired}
                      answers={input.ANSWERS}
                    />
                  </div>
                )

              case 'request':
                return (
                  <div key={index} className="flex-column feedback-form-row">
                    <TextareaField
                      handleChange={handleChange}
                      required={inputRequired}
                      answers={input.ANSWERS}
                    />
                  </div>
                )

              case 'country':
                return (
                  <div key={index} onChange={handleCountrySelect} className="flex-column input-container">
                    <SelectField
                      handleChange={handleChange}
                      title={input.TITLE}
                      sid={inputSID}
                      required={inputRequired}
                      answers={input.ANSWERS}
                      message={templateSettings.countryMessage.TEXT
                      }
                    />
                  </div>
                )

              case 'region':
                return (
                  <div key={index} className="flex-column input-container">
                    <SelectField
                      handleChange={handleChange}
                      title={input.TITLE}
                      sid={inputSID}
                      required={inputRequired}
                      answers={input.ANSWERS}
                    />
                  </div>
                )

              default:
                return (
                  <div key={index} className="flex-column input-container">
                    <TextField
                      handleChange={handleChange}
                      required={inputRequired}
                      answers={input.ANSWERS}
                      autocomplete={input.AUTOCOMPLETE}
                    />
                  </div>
                )
            }
          })
        }
        <div className="flex-column feedback-form-row">
          <p className="message-container">
            <svg width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M10 1L1 10" stroke="#CC0000" strokeWidth="2" />
              <path d="M1 1L10 10" stroke="#CC0000" strokeWidth="2" />
            </svg>
            {lang === 'ru' ? <ins>Что-то пошло не так</ins> : <ins>Something went wrong</ins>}
          </p>
          <button className="button feedback-form-submit"
            type="submit">{lang === 'ru' ? 'Отправить' : 'Submit'}</button>
        </div>
      </form>
    </div>
  ) : null
}

const TextField = props => (
  Object.values(props.answers).map((inputAnswer, answerIndex) => (
    <React.Fragment key={answerIndex}>
      <input name={inputAnswer.HTML_NAME}
        id={`${inputAnswer.HTML_NAME}_input`}
        type="text"
        autoComplete={props.autocomplete}
        placeholder={inputAnswer.MESSAGE}
        onChange={props.handleChange}
        className={props.required} />
      <label htmlFor={`${inputAnswer.HTML_NAME}_input`}>{inputAnswer.MESSAGE}</label>
    </React.Fragment>
  ))
)

const SelectField = props => (
  <>
    <select
      className={props.required}
      onChange={props.handleChange}
      name={'form_dropdown_' + props.sid}
      id={'form_dropdown_' + props.sid}
    >
      {
        Object.values(props.answers).map((inputAnswer, answerIndex) => (
          <React.Fragment key={answerIndex}>
            <option value={answerIndex ? inputAnswer.ID : ''}>{inputAnswer.MESSAGE}</option>
          </React.Fragment>
        ))
      }
    </select>
    <label htmlFor={'form_dropdown_country'}>{props.title}</label>
    {props.message && <p className="country-tip" dangerouslySetInnerHTML={{ __html: props.message }} />}
  </>
)

const EmailField = props => (
  Object.values(props.answers).map((inputAnswer, answerIndex) => (
    <React.Fragment key={answerIndex}>
      <input name={inputAnswer.HTML_NAME}
        id={`${inputAnswer.HTML_NAME}_input`}
        type="email"
        autoComplete="email"
        placeholder={inputAnswer.MESSAGE}
        onChange={props.handleChange}
        className={props.required} />
      <label htmlFor={`${inputAnswer.HTML_NAME}_input`}>{inputAnswer.MESSAGE}</label>
    </React.Fragment>
  ))
)

const PhoneField = props => (
  Object.values(props.answers).map((inputAnswer, answerIndex) => (
    <React.Fragment key={answerIndex}>
      <input name={inputAnswer.HTML_NAME}
        id={`${inputAnswer.HTML_NAME}_input`}
        type="tel"
        autoComplete="tel"
        placeholder={inputAnswer.MESSAGE}
        onChange={props.handleChange}
        className={props.required} />
      <label htmlFor={`${inputAnswer.HTML_NAME}_input`}>{inputAnswer.MESSAGE}</label>
    </React.Fragment>
  ))
)

const TextareaField = props => (
  Object.values(props.answers).map((inputAnswer, answerIndex) => (
    <React.Fragment key={answerIndex}>
      <textarea
        name={inputAnswer.HTML_NAME}
        id={`${inputAnswer.HTML_NAME}_input`}
        placeholder={inputAnswer.MESSAGE}
        onChange={props.handleChange}
        className={props.required} >
      </textarea>
      <label htmlFor={`${inputAnswer.HTML_NAME}_input`}>{inputAnswer.MESSAGE}</label>
    </React.Fragment>
  ))
)

const CheckboxesField = props => {

  return (
    <>
      <p className="label">{props.title}</p>
      <div className="flex-column feedback-checkboxes">
        {
          Object.values(props.answers).map((inputAnswer, answerIndex) => (
            <React.Fragment key={answerIndex}>
              <label
                className={classNames([
                  'flex',
                  'feedback-checkbox',
                  {
                    'is-sub-checkbox': inputAnswer.PARENT_ID,
                    'is-hidden': inputAnswer.PARENT_ID &&
                      (props.values &&
                        props.values.indexOf(inputAnswer.PARENT_ID) === -1 || !props.values)
                  }
                ])}
                htmlFor={`${props.sid}_${answerIndex}_input`}>
                <input id={`${props.sid}_${answerIndex}_input`}
                  type="checkbox"
                  name={inputAnswer.HTML_NAME}
                  value={inputAnswer.ID}
                  onChange={props.handleChange}
                  className={props.required}
                  defaultChecked={props.defaultValue == inputAnswer.ID ? true : false}
                />
                <i className="custom-checkbox">
                  <svg width="9" height="9" viewBox="0 0 9 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 5L3 7L7.5 1" stroke="white" strokeWidth="1.5" />
                  </svg>
                </i>
                <span>{inputAnswer.MESSAGE}</span>
              </label>
            </React.Fragment>
          ))
        }
      </div>
    </>
  )
}

const AgreementField = props => Object.values(props.answers).map((inputAnswer, answerIndex) => (
  <label key={answerIndex} className="flex agreement-label" htmlFor={`${props.sid}_${answerIndex}_input`}>
    <input type="checkbox"
      id={`${props.sid}_${answerIndex}_input`}
      name={inputAnswer.HTML_NAME}
      value={inputAnswer.ID}
      onChange={props.handleChange}
      className={props.required} />
    <i className="custom-checkbox">
      <svg width="9" height="9" viewBox="0 0 9 9" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M1 5L3 7L7.5 1" stroke="white" strokeWidth="1.5" />
      </svg>
    </i>
    <div className="agreement-message" dangerouslySetInnerHTML={{ __html: props.title }} />
  </label>
))

export default FeedbackForm
