import React, { useEffect, useState } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { inFinalState, inInitialState } from '@store/helpers'
import { useTemplateContext } from '@contexts/TemplateContext'
import { getCurrentRegion } from '@/components/Header/Regions'
import { fetchLandingsItems } from '@store'

// import '../style.scss';


const LandingBanner = (props) => {
    const [navbarOffset, setNavbarOffset] = useState(0)

    const lang = useTemplateContext().lang;
    const langPrefix = (lang === 'ru') ? '' : '/' + lang;
    let landingsItemsCode = props.code
    const region = getCurrentRegion()
    const dispatch = useDispatch()
    const landingsItemsStoreChunk = useSelector(store => store['landingsItems'][landingsItemsCode])

    useEffect(() => {
        if (!landingsItemsStoreChunk || inInitialState(landingsItemsStoreChunk)) {
            dispatch(fetchLandingsItems(landingsItemsCode, region, lang))
        }
        document.documentElement.scrollTop = 0
    }, [dispatch, landingsItemsStoreChunk])

    useEffect(() => {
        window.addEventListener('scroll', isSticky);
        return () => {
            window.removeEventListener('scroll', isSticky);
        };
    });

    if (!inFinalState(landingsItemsStoreChunk)) {
        return null
    }
    const { data } = landingsItemsStoreChunk;
    console.log(data);
    const isSticky = (e) => {
        const navbar = document.querySelector('#secondary-nav');
        const scrollTop = window.scrollY;
        if (!navbarOffset) {
            setNavbarOffset(navbar.getBoundingClientRect().y + window.pageYOffset)
        }
        scrollTop >= navbarOffset && navbarOffset !== 0 ? navbar.classList.add('fixed', 'remove-fixed') : navbar.classList.remove('fixed', 'remove-fixed');
    };

    let index, name, date, image, bannerDescription;

    if (props.id) {
        data.days.map((day, i) => {
            if (day.id === props.id) {
                index = i;
                name = day.name;
                bannerDescription = day.banner_description.TEXT;
                date = day.date;
                image = day.detailImage.src;
            }
        })
    }

    return (
        <>
            <div className="d-none d-md-block" id="banner-area">
                <div className="b-container">
                    <div className="row">
                        <div className="col-sm-12">
                            <span property="s:largeImage">
                                <img className="img-fluid" alt="" src={props.id ? image : data.image.src} />
                            </span>
                            <div className="banner-caption">
                                <div className="row">
                                    <div className="col-lg-6 col-md-6 col-sm-7">

                                        <h1>
                                            <div className="banner-caption-text hidden-xs">
                                                <span dangerouslySetInnerHTML={{ __html: props.id ? bannerDescription : data.banner_description }}></span>
                                            </div>
                                            <span className="visible-xs" style={{ fontSize: '70%' }} >
                                                <span dangerouslySetInnerHTML={{ __html: props.id ? bannerDescription : data.banner_description }}></span>
                                            </span>
                                            <br />
                                        </h1>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div >

            <div id="secondary-nav" className="">
                <nav className="navbar">
                    <div className="b-container flexible">

                        <div className="row d-block d-sm-none">
                            <div className="col-12">
                                <h2 className="text-center">
                                </h2>
                            </div>
                        </div>

                        <div className="product-navbar" typeof="Region" resource="SecondaryNavML">
                            <ul className="d-md-flex">
                                <li className={`nav-item text-center ${index !== undefined ? ' ' : 'act'}`}>
                                    <a href={`${langPrefix}/events/${data.code}`}>
                                        <img src={data.min_image.src} className="img-fluid center-block" alt="/" />
                                        <span dangerouslySetInnerHTML={{ __html: data.name }}></span>
                                    </a>
                                </li>
                                {
                                    data.days.map((day, i) => {
                                        return (
                                            <li className={`nav-item text-center ${index === i ? 'act' : ' '}`} key={day.id}>
                                                <a href={`${langPrefix}/events/${data.code}/${day.code}`}>
                                                    {day.icon ? <img src={day.icon.src} className="img-fluid center-block" alt="/" /> : null}
                                                    <span dangerouslySetInnerHTML={{ __html: day.name }}></span>
                                                </a>
                                            </li>
                                        )
                                    })
                                }
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>

            <div typeof="Region" resource="Hero">
                <span property="s:thumbnailImage">
                    <img src="https://canonmedical.widen.net/content/shkbhx1tai/original/Overview_Banner_mobile.png?u=cglmil&amp;" className="img-fluid d-block d-md-none border-bottom" alt="" />
                </span>
                <div className="b-container d-block d-md-none border-bottom">
                    <div className="section-60">
                        <div className="row">
                            <div className="col-12 d-block d-sm-none top-image">
                                <h1>
                                    <span className="text-red">
                                        <div style={{ paddingLeft: '20px' }} className="text-white hidden-xs">
                                            <span style={{ fontSize: '120%', fontWeight: 'bold' }}>{props.id ? name : data.name}</span>
                                            <br /><br /><br /><span >
                                                <em dangerouslySetInnerHTML={{ __html: props.id ? date : data.date }}></em>
                                            </span>
                                        </div>
                                        <span className="visible-xs">
                                            <span dangerouslySetInnerHTML={{ __html: props.id ? name : data.name }}></span>
                                            <br /><em dangerouslySetInnerHTML={{ __html: props.id ? date : data.date }}></em>
                                        </span>
                                    </span><br />
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </>
    )
}

export default LandingBanner
