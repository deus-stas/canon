import React, { useEffect, useRef } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { inFinalState, inInitialState } from '@store/helpers'
import { useTemplateContext } from '@contexts/TemplateContext'
import { getCurrentRegion } from '@/components/Header/Regions'
import { fetchLandingsItems } from '@store'
import { useParams } from "react-router-dom";

//import '../style.scss'
import NotFoundPage from '@components/Page/NotFoundPage'
import Banner from '../LandingBanner'
import { updatePageMeta } from '../../../../hooks/usePageMeta'
import { motion } from "framer-motion"
import LandingItemAnalogSection from './LandingItemAnalogSection'

const LandingItemAnalog = (props) => {
    const { eventType } = useParams();
    const lang = useTemplateContext().lang;
    const langPrefix = (lang === 'ru') ? '' : '/' + lang;

    let landingsItemsCode = props.match.params.path1;

    const region = getCurrentRegion()
    const dispatch = useDispatch()
    const landingsItemsStoreChunk = useSelector(store => store['landingsItems'][landingsItemsCode])

    useEffect(() => {
        if (!landingsItemsStoreChunk || inInitialState(landingsItemsStoreChunk)) {
            dispatch(fetchLandingsItems(landingsItemsCode, region, lang))
        }
        document.documentElement.scrollTop = 0
            
    }, [dispatch, landingsItemsStoreChunk])

    if (!inFinalState(landingsItemsStoreChunk)) {
        return null
    }
    const { data } = landingsItemsStoreChunk;

    console.log(data);

    if (data.image) {

        // updatePageMeta(data.seo);

        return (

            <div id="primary-content">

                <Banner code={landingsItemsCode} />
                {
                    data.landing_code !== 'radiologiya' ?
                        <>
                            <div id="content-area">
                                <div className="b-container section-60-bottom section-40-top border-bottom">
                                    <div className="row">
                                        <div className="col-md-12">
                                            <h1></h1>
                                            <h2 dangerouslySetInnerHTML={{ __html: data.theme }}></h2>
                                            <h1 dangerouslySetInnerHTML={{ __html: data.full_name }}></h1>
                                        </div>
                                        <div className="col-sm-7">
                                            <p className="margin-bottom-30"
                                                dangerouslySetInnerHTML={{ __html: data.description }}
                                            >
                                            </p>
                                        </div>
                                        <div className="col-sm-5 border-left">
                                            <div className="side-navigation">
                                                <p className="side-navigation-header" dangerouslySetInnerHTML={{ __html: data.name_block_days }}></p>
                                                <ul className="nav">
                                                    {
                                                        data.days.map((day) => {
                                                            return (
                                                                <li key={day.id}>
                                                                    <a href={`${langPrefix}/events/${data.code}/${day.code}`}>
                                                                        <h2>
                                                                            <strong dangerouslySetInnerHTML={{ __html: day.date }}></strong>
                                                                        </h2>
                                                                        <strong dangerouslySetInnerHTML={{ __html: day.name }}></strong><br />
                                                                        <span dangerouslySetInnerHTML={{ __html: day.theme_day }}></span>
                                                                    </a>
                                                                </li>
                                                            )
                                                        })
                                                    }
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="b-container">
                                <div className="section-20">
                                    <div className="ProductDetailContent ContentArea">
                                        <div className="row">
                                            <div className="col-sm-12">
                                            </div>
                                            <div className="col-sm-12">
                                                <h1 dangerouslySetInnerHTML={{ __html: data.name }}>
                                                </h1>
                                            </div>
                                            <div className="col-sm-12">

                                                <div className="mm-player-custom html5 ratio ratio-16x9 player-loaded" dangerouslySetInnerHTML={{ __html: data.iframe_video }}>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {
                                data.days.map((day, i) => {
                                    const translateFromLeft = i % 2 == 0
                                    return <LandingItemAnalogSection
                                        key={day.id}
                                        translateFromLeft={translateFromLeft}
                                        day={day}
                                        langPrefix={langPrefix}
                                        code={data.code}
                                    />
                                })
                            }

                            <div className="b-container">
                                <div className="section-20">
                                    <div className="ProductDetailContent ContentArea">
                                        <div className="row">
                                            <div className="col-sm-12">
                                            </div>
                                            <div className="col-sm-12">
                                                <p dangerouslySetInnerHTML={{ __html: data.full_description }}>

                                                </p>
                                            </div>
                                            <div className="col-sm-12">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </>
                        :
                        <div id="radio">
                            <div id="content-area">
                                <div className="b-container section-30-bottom section-40-top border-bottom">
                                    <h1 dangerouslySetInnerHTML={{ __html: data.full_name }}></h1>
                                    <h2 dangerouslySetInnerHTML={{ __html: data.date_place }}></h2>
                                </div>
                            </div>
                            <div className="b-container">
                                <div className="section-20">
                                    <div className="ProductDetailContent ContentArea">
                                        <div dangerouslySetInnerHTML={{ __html: data.desc_block1 }}></div>
                                    </div>
                                </div>
                            </div>

                            <div className="b-container">
                                <div className="section-20">
                                    <div className="ProductDetailContent ContentArea">
                                        <div dangerouslySetInnerHTML={{ __html: data.desc_block2 }}></div>
                                    </div>
                                </div>
                            </div>

                            <div className="b-container">
                                <div className="section-20">
                                    <div className="ProductDetailContent ContentArea">
                                        <div dangerouslySetInnerHTML={{ __html: data.desc_block3 }}></div>
                                    </div>
                                </div>
                            </div>

                            <div className="b-container">
                                <div className="section-20">
                                    <div className="ProductDetailContent ContentArea">
                                        <div dangerouslySetInnerHTML={{ __html: data.desc_block4 }}></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                }

            </div >
        )

    }
    return <NotFoundPage />
}


export default LandingItemAnalog;
