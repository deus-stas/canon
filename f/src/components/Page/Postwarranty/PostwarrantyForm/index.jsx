/* eslint-disable max-len */
import React, { useEffect, useState } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchWarranty } from '@store'
import { fetchEquipment } from '@store'
import { inFinalState, inInitialState } from '@store/helpers'
import { useTemplateContext } from '@contexts/TemplateContext'
import { saveWarranty } from '@/api'
import { openFeedbackFormPopup } from '../../Contacts/FeedbackForm/FeedbackFormPopup/'
import { PartySuggestions, AddressSuggestions } from 'react-dadata'

// import 'react-dadata/dist/react-dadata.css';
import './style.scss'
import classNames from 'classnames'

const DADATA_TOKEN = '1c25fa00567b62062c02ffe1bf78823d47918a80'

const Warranty = props => {
    const lang = useTemplateContext().lang
    const templateSettings = useTemplateContext().templateSettings
    const dispatch = useDispatch()
    const warrantyStoreChunk = useSelector(store => store['warranty'])
    const equipmentStoreChunk = useSelector(store => store['equipment'])
    const [values, setValues] = useState({})
    const [showRegion, setShowRegion] = useState(false)
    const [idsValues, setIdsValues] = useState([])

    useEffect(() => {
        if (!warrantyStoreChunk || inInitialState(warrantyStoreChunk)) {
            dispatch(fetchWarranty(lang))
        }
    }, [dispatch, warrantyStoreChunk])

    useEffect(() => {
        if (!equipmentStoreChunk || inInitialState(equipmentStoreChunk)) {
            dispatch(fetchEquipment(lang))
        }
    }, [dispatch, equipmentStoreChunk])

    if (!inFinalState(warrantyStoreChunk) || !inFinalState(equipmentStoreChunk)) {
        return null
    }
    console.log(warrantyStoreChunk.data)
    console.log(equipmentStoreChunk.data)
    const { data: feedbackForm } = warrantyStoreChunk
    console.log("chunnk", warrantyStoreChunk)
    if (feedbackForm.QUESTIONS) {
        const feedbackFormInputs = Object.values(feedbackForm.QUESTIONS).sort((a, b) => a.SORT - b.SORT)
    


        if (!showRegion) {
            const index = feedbackFormInputs.findIndex(item => item.SID === 'region')
            const indexInn = feedbackFormInputs.findIndex(item => item.SID === 'inn')
            const indexAddress = feedbackFormInputs.findIndex(item => item.SID === 'address')
            const indexCity = feedbackFormInputs.findIndex(item => item.SID === 'city')
            const indexEndUser = feedbackFormInputs.findIndex(item => item.SID === 'inn_end_user')
            delete feedbackFormInputs[index]
            delete feedbackFormInputs[indexInn]
            delete feedbackFormInputs[indexAddress]
            delete feedbackFormInputs[indexCity]
            delete feedbackFormInputs[indexEndUser]
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

        const handleChange = (e, type) => {
            if (e.target) {
                e.target.classList.remove('empty-input')
                if (e.target.name.includes('[]')) {
                    // Обработка значений чекбоксов
                    const targetName = e.target.name.replace(/\[]/g, '')
                    const valuesArr = values[targetName] || []
                    // Обновляем значение values
                    if (valuesArr.length && valuesArr.includes(e.target.value)) {
                        setValues(prevState => {
                            const newValues = {
                                ...prevState,
                                [targetName]: prevState[targetName].filter(el => el !== e.target.value)
                            }
                            if (e.target.name !== 'form_checkbox_agree[]' && e.target.name !== 'form_checkbox_agree_mail[]') { setIdsValues(newValues[targetName] || []) }
                            return newValues
                        })
                    } else {
                        setValues(prevState => {
                            const newValues = {
                                ...prevState,
                                [targetName]: prevState[targetName]?.length ? [...prevState[targetName], e.target.value] : [e.target.value]
                            }
                            if (e.target.name !== 'form_checkbox_agree[]' && e.target.name !== 'form_checkbox_agree_mail[]') { setIdsValues(newValues[targetName] || [e.target.value]) }    
                            return newValues
                        })
                    }
                } else if (e.target.files) {
                    // файл
                    setValues({
                        ...values,
                        [e.target.name]: e.target.files[0]
                    })
                } else {
                    // Прочие поля
                    setValues({
                        ...values,
                        [e.target.name]: e.target.value
                    })
                }

            } else if (type === 'company') {
                const addressName = feedbackFormInputs.find(input => input.SID === 'address')?.ANSWERS[0]?.HTML_NAME
                const cityName = feedbackFormInputs.find(input => input.SID === 'city')?.ANSWERS[0]?.HTML_NAME
                const innName = feedbackFormInputs.find(input => input.SID === 'inn')?.ANSWERS[0]?.HTML_NAME
                const name = feedbackFormInputs.find(input => input.SID === 'company')?.ANSWERS[0]?.HTML_NAME
                document.querySelector(`#${name}_input`).classList.remove('empty-input')
                setValues({
                    ...values,
                    [addressName]: e.data.address.unrestricted_value,
                    [cityName]: e.data.address.data.city,
                    [innName]: e.data.inn,
                    [name]: e.data.name.short_with_opf
                })
            } else if (type === 'end_user') {
                const innEndUser = feedbackFormInputs.find(input => input.SID === 'inn_end_user')?.ANSWERS[0]?.HTML_NAME
                const name = feedbackFormInputs.find(input => input.SID === 'end_user')?.ANSWERS[0]?.HTML_NAME
                document.querySelector(`#${name}_input`).classList.remove('empty-input')

                setValues({
                    ...values,
                    [innEndUser]: e.data.inn,
                    [name]: e.data.name.short_with_opf
                })
            } else if (type === 'install_address') {
                // console.log(e)
                console.log("test", e)
                const name = feedbackFormInputs.find(input => input.SID === 'install_address')?.ANSWERS[0]?.HTML_NAME
                document.querySelector(`#${name}_input`).classList.remove('empty-input')

                setValues({
                    ...values,
                    [name]: e.value
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
            // console.log(values)
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
                    action: 'forms.saveFormPostWarranty',
                    values
                }
                console.log("warranty data:", data)
                saveWarranty(data, lang).then(response => {
                    if (response.status === 'ok') {
                        openFeedbackFormPopup()
                        console.log(data)

                        const inputs = Array.from(e.target.querySelectorAll('input'))
                        inputs.map(el => {
                            if (el.type === 'checkbox')
                                el.checked = false
                            else
                                console.log("name", el.name, el.value)
                                el.value = ''
                        })

                        const textareas = Array.from(e.target.querySelectorAll('textarea'))
                        textareas.map(el => {
                                console.log("name", el.name, el.value)
                                el.value = ''
                        })

                        const selects = Array.from(e.target.querySelectorAll('select'))
                        selects.map(el => {
                            console.log("name", el.name, el.value)
                            el.value = ''
                        })

                    } else {
                        formError(e, lang === 'ru' ? 'Что-то пошло не так' : 'Something went wrong')
                    }

                    buttonSubmit.disabled = false
                })
            }
            // submitForm()
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
            <div className="feedback-form-container --warranty">
                <h3>
                    {lang === 'ru' ? 'Оставить заявку на постгарантийный сервис' : 'Post-warranty service'}
                </h3>
                <form data-form-id={feedbackForm.ID} onSubmit={handleSubmit}
                    className="flex feedback-form" id="feedback-form" action="/" method="post">
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

                                    }

                                case 'SUB_QUESTIONS': {
                                    const subQuestions = input.fields.filter(item => item.ANSWERS[0].PARENT_ID.split(',').some(id => idsValues.includes(id)))
                                    return Object.values(subQuestions).map((item, idx) => {
                                        const inputItemSID = item.SID
                                        const inputRequired = item.REQUIRED === 'Y' ? 'required' : ''
                                        const key = `${index}-${inputItemSID}-${idx}`
                                        switch (inputItemSID) {
                                            case 'files': {
                                                return (
                                                    <div key={key} className="flex-column input-container" data-id={item.ANSWERS[0].HTML_NAME}>
                                                        <input name={item.ANSWERS[0].HTML_NAME}
                                                            id={`${item.ANSWERS[0].HTML_NAME}_input`}
                                                            type="file"
                                                            autoComplete={item.AUTOCOMPLETE}
                                                            placeholder={item.ANSWERS[0].MESSAGE}
                                                            onChange={handleChange}
                                                            className={inputRequired}
                                                            defaultValue={values[item.ANSWERS[0].HTML_NAME]} />

                                                        <label htmlFor={`${item.ANSWERS[0].HTML_NAME}_input`}>{item.TITLE}</label>
                                                        {item.ANSWERS[0].MESSAGE && <p className="country-tip" dangerouslySetInnerHTML={{ __html: item.ANSWERS[0].MESSAGE }} />}
                                                    </div>
                                                )
                                            }
                                            default: {
                                                return (
                                                    <div key={key} className="flex-column input-container" data-id={item.ANSWERS[0].HTML_NAME}>
                                                        <input name={item.ANSWERS[0].HTML_NAME}
                                                            id={`${item.ANSWERS[0].HTML_NAME}_input`}
                                                            type="text"
                                                            autoComplete={item.autocomplete}
                                                            placeholder={item.ANSWERS[0].MESSAGE}
                                                            onChange={handleChange}
                                                            className={inputRequired} />

                                                        <label htmlFor={`${item.ANSWERS[0].HTML_NAME}_input`}>{item.TITLE}</label>
                                                    </div>
                                                )
                                            }
                                        }
                                    })
                                }

                                case 'email':
                                    return (
                                        <div key={index} className="flex-column input-container">
                                            {/* <EmailField
                                                handleChange={handleChange}
                                                required={inputRequired}
                                                answers={input.ANSWERS}
                                            /> */}
                                            <input name={input.ANSWERS[0].HTML_NAME}
                                                id={`${input.ANSWERS[0].HTML_NAME}_input`}
                                                type="email"
                                                autoComplete={input.autocomplete}
                                                placeholder={input.ANSWERS[0].MESSAGE}
                                                onChange={handleChange}
                                                className={inputRequired} />

                                            <label htmlFor={`${input.ANSWERS[0].HTML_NAME}_input`}>{input.TITLE}</label>
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
                                            <label htmlFor={`${input.ANSWERS[0].HTML_NAME}_input`}>{input.TITLE}</label>
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

                                case 'company':
                                    if (showRegion) {
                                        return (
                                            <div key={index} className="flex-column input-container">
                                                <PartySuggestions required={inputRequired} key={index} name={input.ANSWERS[0].HTML_NAME} token={DADATA_TOKEN} inputProps={{ 'placeholder': input.ANSWERS[0].MESSAGE, 'id': input.ANSWERS[0].HTML_NAME + '_input', name: input.ANSWERS[0].HTML_NAME, className: 'required', type: 'text' }} onChange={e => handleChange(e, 'company')} />
                                                <label htmlFor={`${input.ANSWERS[0].HTML_NAME}_input`} className={`${inputRequired}_label`}>{input.TITLE}</label>
                                            </div>
                                        )
                                    } else {
                                        return (
                                            <div key={index} className="flex-column input-container">
                                                <input name={input.ANSWERS[0].HTML_NAME}
                                                    id={`${input.ANSWERS[0].HTML_NAME}_input`}
                                                    type="text"
                                                    autoComplete={input.AUTOCOMPLETE}
                                                    placeholder={input.ANSWERS[0].MESSAGE}
                                                    onChange={handleChange}
                                                    className={inputRequired}
                                                    defaultValue={values[input.ANSWERS[0].HTML_NAME]} />

                                                <label htmlFor={`${input.ANSWERS[0].HTML_NAME}_input`}>{input.TITLE}</label>

                                            </div>
                                        )
                                    }

                                case 'end_user':
                                    if (showRegion) {
                                        return (
                                            <div key={index} className="flex-column input-container">
                                                <PartySuggestions key={index} name={input.ANSWERS[0].HTML_NAME} token={DADATA_TOKEN} inputProps={{ 'placeholder': input.ANSWERS[0].MESSAGE, 'id': input.ANSWERS[0].HTML_NAME + '_input', name: input.ANSWERS[0].HTML_NAME, className: 'required', type: 'text' }} onChange={e => handleChange(e, 'end_user')} />
                                                <label htmlFor={`${input.ANSWERS[0].HTML_NAME}_input`} className={`${inputRequired}_label`}>{input.TITLE}</label>
                                            </div>
                                        )
                                    } else {
                                        return (
                                            <div key={index} className="flex-column input-container">
                                                <input name={input.ANSWERS[0].HTML_NAME}
                                                    id={`${input.ANSWERS[0].HTML_NAME}_input`}
                                                    type="text"
                                                    autoComplete={input.AUTOCOMPLETE}
                                                    placeholder={input.ANSWERS[0].MESSAGE}
                                                    onChange={handleChange}
                                                    className={input.REQUIRED}
                                                    defaultValue={values[input.ANSWERS[0].HTML_NAME]} />

                                                <label htmlFor={`${input.ANSWERS[0].HTML_NAME}_input`}>{input.TITLE}</label>

                                            </div>
                                        )
                                    }

                                case 'inn_end_user':
                                    return (
                                        <div key={index} className="flex-column input-container dep-inn">
                                            <input name={input.ANSWERS[0].HTML_NAME}
                                                id={`${input.ANSWERS[0].HTML_NAME}_input`}
                                                type="text"
                                                autoComplete={input.AUTOCOMPLETE}
                                                placeholder={input.ANSWERS[0].MESSAGE}
                                                onChange={handleChange}
                                                className={input.REQUIRED}
                                                defaultValue={values[input.ANSWERS[0].HTML_NAME]} />

                                            <label htmlFor={`${input.ANSWERS[0].HTML_NAME}_input`}>{input.TITLE}</label>

                                        </div>
                                    )

                                case 'install_address':
                                    if (showRegion) {
                                        return (
                                            <div key={index} className="flex-column input-container">
                                                <AddressSuggestions autoComplete={input.AUTOCOMPLETE} key={index} name={input.ANSWERS[0].HTML_NAME} token={DADATA_TOKEN} inputProps={{ 'placeholder': input.ANSWERS[0].MESSAGE, 'id': input.ANSWERS[0].HTML_NAME + '_input', name: input.ANSWERS[0].HTML_NAME, className: 'required', type: 'text', onInput: handleChange}} setInputValue onChange={e => handleChange(e, 'install_address')} />
                                                <label htmlFor={`${input.ANSWERS[0].HTML_NAME}_input`} className={`${inputRequired}_label`}>{input.TITLE}</label>
                                            </div>
                                        )
                                    } else {
                                        return (
                                            <div key={index} className="flex-column input-container">
                                                <input name={input.ANSWERS[0].HTML_NAME}
                                                    id={`${input.ANSWERS[0].HTML_NAME}_input`}
                                                    type="text"
                                                    autoComplete={input.AUTOCOMPLETE}
                                                    placeholder={input.ANSWERS[0].MESSAGE}
                                                    onChange={handleChange}
                                                    className={inputRequired}
                                                    defaultValue={values[input.ANSWERS[0].HTML_NAME]} />

                                                <label htmlFor={`${input.ANSWERS[0].HTML_NAME}_input`}>{input.TITLE}</label>

                                            </div>
                                        )
                                    }

                                case 'address':
                                case 'city':
                                case 'inn':
                                    return (
                                        <div key={index} className="flex-column input-container dep-year">
                                            <input name={input.ANSWERS[0].HTML_NAME}
                                                id={`${input.ANSWERS[0].HTML_NAME}_input`}
                                                type="text"
                                                autoComplete={input.AUTOCOMPLETE}
                                                placeholder={input.ANSWERS[0].MESSAGE}
                                                onChange={handleChange}
                                                className={inputRequired}
                                                defaultValue={values[input.ANSWERS[0].HTML_NAME]} />

                                            <label htmlFor={`${input.ANSWERS[0].HTML_NAME}_input`}>{input.TITLE}</label>

                                        </div>
                                    )
                                case 'model':
                                    return (
                                        <div key={index} className="flex-column input-container">
                                            <select
                                                className={inputRequired}
                                                onChange={handleChange}
                                                name={input.ANSWERS[0].HTML_NAME}
                                                id={`${input.ANSWERS[0].HTML_NAME}_input`}
                                            >
                                                {
                                                    Object.values(equipmentStoreChunk.data).map((item, index) => (
                                                        !item.category ?
                                                            <React.Fragment key={index}>
                                                                <option value={''}>{item.name}</option>
                                                            </React.Fragment> : null
                                                    ))
                                                }
                                                {
                                                    Object.values(equipmentStoreChunk.data).map((item, index) => (
                                                        item.category ?
                                                            <React.Fragment key={index}>
                                                                <option value={item.category + ', ' + item.name}>{item.category}, {item.name}</option>
                                                            </React.Fragment> : null
                                                    ))
                                                }
                                            </select>
                                            <label htmlFor={'form_dropdown_country'}>{input.TITLE}</label>
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
                                                title={input.TITLE}
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
    } else {
        return (
            <div>test</div>
        )
    }
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

            <label htmlFor={`${inputAnswer.HTML_NAME}_input`}>{props.title}</label>
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
        {/* {props.message && <p className="country-tip" dangerouslySetInnerHTML={{ __html: props.message }} />} */}
    </>
)

const EmailField = props => (
    console.log("prps", props),
    Object.values(props.answers).map((inputAnswer, answerIndex) => (
        console.log("test", inputAnswer),
        <React.Fragment key={answerIndex}>
            <input name={inputAnswer.HTML_NAME}
                id={`${inputAnswer.HTML_NAME}_input`}
                type="email"
                autoComplete="email"
                placeholder={inputAnswer.MESSAGE}
                onChange={props.handleChange}
                className={props.required} />
            <label htmlFor={`${inputAnswer.HTML_NAME}_input`}>{inputAnswer.TITLE }</label>
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
        <textarea
            name={inputAnswer.HTML_NAME}
            id={`${inputAnswer.HTML_NAME}_input`}
            placeholder={inputAnswer.MESSAGE}
            onChange={props.handleChange}
            className={props.required}
            key={answerIndex} >
        </textarea>
    ))
)

const CheckboxesField = props => (
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
                                    defaultChecked={props.defaultValue == inputAnswer.ID}
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

export default Warranty
