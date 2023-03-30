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

            <div className="d-none d-md-block" id="banner-area">
                <div className="b-container">
                    <div className="row">
                        <div className="col-sm-12">
                            <span property="s:largeImage">
                                <img className="img-fluid" alt="" src="https://canonmedical.widen.net/content/gn2486n88f/original/Overview_Banner.png?u=cglmil&amp;" />
                            </span>
                            <div className="banner-caption">
                                <div className="row">
                                    <div className="col-lg-6 col-md-6 col-sm-7">

                                        <h1>
                                            <div className="text-red">
                                                <div className="text-white hidden-xs"><span>Online Oncology Days</span><br /><br /><br /><span ><em>Starting April 12</em></span></div><span className="visible-xs">Online Oncology Days
                                                    <br /><em>Starting April 12</em></span>
                                            </div><br />
                                        </h1>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    ) : <NotFoundPage />
}

export default Landing
