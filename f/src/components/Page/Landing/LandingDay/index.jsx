import React, { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { inFinalState, inInitialState } from '@store/helpers'
import { useTemplateContext } from '@contexts/TemplateContext'
import { getCurrentRegion } from '@/components/Header/Regions'
import { fetchLandingsDays } from '@store'
import { updatePageMeta } from '../../../../hooks/usePageMeta'
import { useParams } from "react-router-dom";

// import '../style.scss'
import NotFoundPage from '@components/Page/NotFoundPage'
import Banner from '../LandingBanner/'

const LandingDay = (props) => {
    
    const lang = useTemplateContext().lang;
    let landingsItemsCode = props.match.params.eventType
    let landingsDaysCode = landingsItemsCode + '/' + props.match.params.path1

    console.log(landingsItemsCode, landingsDaysCode);
    const region = getCurrentRegion()
    const dispatch = useDispatch()
    const landingsDaysStoreChunk = useSelector(store => store['landingsDays'][landingsDaysCode])

    useEffect(() => {
        if (!landingsDaysStoreChunk || inInitialState(landingsDaysStoreChunk)) {
            dispatch(fetchLandingsDays(landingsDaysCode, region, lang))
        }
        document.documentElement.scrollTop = 0
    }, [dispatch, landingsDaysStoreChunk])

    if (!inFinalState(landingsDaysStoreChunk)) {
        return null
    }
    const { data } = landingsDaysStoreChunk;

    console.log(data);

    if (data) {
        updatePageMeta(data.seo);
        return (

            <div id="primary-content">
                <Banner code={landingsItemsCode} id={data.id} />

                <div id="content-area">
                    <div className="b-container">
                        <div className="section-20">
                            <div className="ProductDetailContent ContentArea">
                                <div className="row">
                                    <div className="col-sm-12">
                                        <h2 dangerouslySetInnerHTML={{__html: data.detail_theme_day}}></h2>
                                    </div>
                                    <div className="col-sm-12">
                                        <h1 dangerouslySetInnerHTML={{__html: data.full_name}}></h1>

                                        <div dangerouslySetInnerHTML={{ __html: data.detailText }}></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="b-container">
                        <div className="section-20">
                            <div className="ProductDetailContent ContentArea">
                                <div className="row">
                                    <h1>
                                        <span style={{ color: 'rgb(204, 0, 0)' }} dangerouslySetInnerHTML={{__html: data.name_block_spikers}}></span>
                                    </h1>
                                </div>

                                {
                                    data.spikers ? data.spikers.map((spiker) => {
                                        return (
                                            <div className="row" key={spiker.id}>
                                                <div className="col-sm-12">
                                                </div>
                                                <div className="col-sm-12">

                                                    <div className="primary-contentLikeP">
                                                        <div className="row">
                                                            <div className="col-lg-3 col-md-3 col-sm-3">
                                                               {spiker.icon ? <img src={spiker.icon.src} className="img-thumbnail m-b-15" alt="sou" width="100%" /> : null} 
                                                            </div>
                                                            <div className="col-lg-9 col-md-9 col-sm-9"><strong dangerouslySetInnerHTML={{ __html: spiker.name }}></strong><br />
                                                                <span dangerouslySetInnerHTML={{ __html: spiker.position }}></span>
                                                                <br />
                                                                <p dangerouslySetInnerHTML={{ __html: spiker.previewText }}></p>
                                                            </div>
                                                        </div>
                                                        <hr />

                                                    </div>
                                                </div>
                                                <div className="col-sm-12">
                                                </div>
                                            </div>
                                        )
                                    }) : null
                                }

                                {
                                    data.sources.TEXT ? <span dangerouslySetInnerHTML={{__html: data.sources.TEXT}}></span> : null
                                }

                            </div>
                        </div>
                    </div>
                </div >

            </div >

        )
    }

    return <NotFoundPage />
}

export default LandingDay
