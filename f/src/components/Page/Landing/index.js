import React, { useEffect, useState } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { inFinalState, inInitialState } from '@store/helpers'
import { useTemplateContext } from '@contexts/TemplateContext'
import { getCurrentRegion } from '@/components/Header/Regions'
import { fetchLandings } from '@/store'


import './style.scss'
import NotFoundPage from '@components/Page/NotFoundPage'
import { Link } from 'react-router-dom'

const Landing = () => {
    const lang = useTemplateContext().lang
    const region = getCurrentRegion()
    const dispatch = useDispatch()
    const sectionsStoreChunk = useSelector(store => store['landings'])

    useEffect(() => {
        if (!sectionsStoreChunk || inInitialState(sectionsStoreChunk)) {
            dispatch(fetchLandings(region, lang))
        }
    }, [dispatch, sectionsStoreChunk])

    if (!inFinalState(sectionsStoreChunk)) {
        return null
    }

    const { data } = sectionsStoreChunk

    console.log(data)

    return data ? (
        <div id="primary-content">

            <div id="content-area">

                <div className="featured-section featured-section-noBG fsEH">
                    <div className="b-container">
                        <div className="row">
                            <div className="col-sm-12 ">
                                <h2 property="s:title"  >UPCOMING WEBINARS</h2>
                            </div>
                        </div>
                        <div className="row">
                            {
                                data.items.map(item => (
                                        <div className="col-sm-6 position-relative" key={item.id}>
                                            <div className="well well-link" key={item.id}>
                                                <Link className='extended-modal-image' to={`/landings/${item.code}`}>
                                                    <span property="s:largeImage">
                                                        <img src={item.start_image.src} className="img-fluid center-block" alt="" style={{ maxHeight: '176.719px' }} />
                                                    </span>
                                                    <h4>
                                                        <div style={{ color: 'rgb(0, 0, 0)' }}>
                                                            <p dangerouslySetInnerHTML={{ __html: item.start_text }}></p>
                                                            <p className="btn btn-default center-block">
                                                                <i className="zmdi zmdi-plus-circle-o"></i>
                                                                More Information
                                                            </p>
                                                        </div>
                                                    </h4>
                                                </Link>
                                            </div>
                                        </div>
                                    ))
                            }
                        </div>
                    </div>
                </div>
            </div>

        </div >
    ) : <NotFoundPage />
}

export default Landing
