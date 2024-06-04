import React, { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchPage } from '@store'
import { inFinalState, inInitialState } from '@store/helpers'
import classNames from 'classnames'
import { pageClassName } from '../constants'
import { useTemplateContext } from '@contexts/TemplateContext'
import { usePageMeta } from '@hooks'
import './style.scss'
import NotFoundPage from '@components/Page/NotFoundPage'
import videoModals from '../../../hooks/videoModals'

const getPathCode = () => {
    let path = location.pathname
    path = path.substr(1)
    if (path.substr(0, 3) === 'en/' || path.substr(0, 3) === 'ru/') {
        path = path.substr(3)
    }
    path = path.slice(0, -1)
    return path
}

const PartneryPage = () => {
    const pageCode = getPathCode()
    usePageMeta({ pageCode })
    const lang = useTemplateContext().lang
    const dispatch = useDispatch()
    const pageStoreChunk = useSelector(store => store['page'][pageCode])
    videoModals(pageCode)

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

    return pageData ? (
        <div className={classNames(`${pageClassName} default-${pageClassName}`)}>
            <div className="container ">
                <div className="wrapper" >
                    {pageData.detailImage && <img src={pageData.detailImage?.src} width={pageData.detailImage?.w}  height={pageData.detailImage?.h} alt={pageData.name} className="innerImage"/>}
                    <div dangerouslySetInnerHTML={{ __html: pageData.detailText }}/>
                </div>
            </div>
        </div>
    ) : <NotFoundPage/>
}

export default PartneryPage
