import React, { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchBanners } from '@/store'
import { inFinalState, inInitialState } from '@/store/helpers'
import { Link } from 'react-router-dom'
import SwiperCore, { Autoplay, Pagination } from 'swiper'
import { Swiper, SwiperSlide } from 'swiper/react'
import { useTemplateContext } from '@/contexts/TemplateContext'

import 'swiper/scss'
import './style.scss'

SwiperCore.use([Pagination, Autoplay])

const Banners = () => {
  const lang = useTemplateContext().lang
  const dispatch = useDispatch()
  const bannersStoreChunk = useSelector(store => store['banners'])

  useEffect(() => {
    if (!bannersStoreChunk || inInitialState(bannersStoreChunk)) {
      dispatch(fetchBanners(lang))
    }
  }, [dispatch, bannersStoreChunk])

  if (!inFinalState(bannersStoreChunk)) {
    return null
  }

  const { data: banners } = bannersStoreChunk

  return banners.length ?
    <Swiper
      spaceBetween={0}
      slidesPerView={1}
      threshold={25}
      pagination={{ clickable: true }}
      autoplay={{ delay: 5000 }}
    >
      {
        banners.map((banner, index) => (
          <SwiperSlide key={index}>
            <Link className=" main-banner" to={banner.link}>
              {banner.previewImage ? <div className="main-banner__image">
                <img alt={banner.caption} src={banner.previewImage.src} />
              </div> : null}

              <div className="button-wrap">
                <div className="button 1"
                  dangerouslySetInnerHTML={{ __html: banner.caption }} />
              </div>
            </Link>
          </SwiperSlide>
        ))
      }
    </Swiper> : null

}

export default Banners
