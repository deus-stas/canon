import React, { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchPage } from '@store'
import { inFinalState, inInitialState } from '@store/helpers'
import { useTemplateContext } from '@contexts/TemplateContext'
import { usePageMeta } from '@hooks'

import './style.scss'
import NotFoundPage from '@components/Page/NotFoundPage'

const pageCode = 'landing'

const Landing = () => {
    usePageMeta({ pageCode })
    const lang = useTemplateContext().lang
    const dispatch = useDispatch()
    const pageStoreChunk = useSelector(store => store['page'][pageCode]);

    useEffect(() => {
        if (!pageStoreChunk || inInitialState(pageStoreChunk)) {
            dispatch(fetchPage(pageCode, lang))
        }
        document.documentElement.scrollTop = 0
    }, [dispatch, pageStoreChunk])

    if (!inFinalState(pageStoreChunk)) {
        return null
    }

    const { data: pageData } = pageStoreChunk

    console.log(pageData);

    return pageData ? (
        <div>
            <div className="container">
                <h1>Имя: {pageData.code}</h1>
                <h2>Дата: {pageData.date}</h2>
                <h3>Id: {pageData.id}</h3>
            </div>
        </div>
    ) : <NotFoundPage />
}

export default Landing
