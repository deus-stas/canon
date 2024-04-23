import React from 'react'
import { motion } from "framer-motion"

const LandingItemAnalogSection = ({day, translateFromLeft, langPrefix, code}) => { 
    const previewText = day.previewText.replace('\&nbsp;', ' ');

    const translation = translateFromLeft ? -582 : 582

    return (
        <div className="b-container">
            <div className="section-20">
                <div className="ProductDetailContent ContentArea">
                    <div className={`row ${translateFromLeft ? '--reverse' : ' '}`}>
                        <motion.div
                            className="col-12-org col-sm-6 slide"
                            whileInView={{
                                opacity: 1,
                                translateX: 0
                            }}
                            initial={{
                                opacity: 0,
                                translateX: translation
                            }}
                            transition={{
                                type: 'spring',
                                mass: 1,
                                stiffness: 50,
                                delay: 0.5,
                            }}
                        >
                            
                            <p style={{...previewText ? {marginBottom: '70px'} : {}}} dangerouslySetInnerHTML={{ __html: previewText }}></p>

                            <a href={`${langPrefix}/events/${code}/${day.code}`} dangerouslySetInnerHTML={{ __html: day.name_url }}>
                            </a>
                        </motion.div>
                        <motion.div
                            whileInView={{
                                opacity: 1,
                                scale: 1
                            }}
                            initial={{
                                opacity: 0,
                                scale: 0.75
                            }}
                            transition={{
                                type: 'spring',
                                stiffness: 50,
                                mass: 2,
                                delay: 0.25,
                            }}
                            className="col-12-org col-sm-6"
                        >
                            <h1 dangerouslySetInnerHTML={{ __html: day.name }}></h1>
                            <div className="d-none d-md-block">
                                <span>
                                    {day.previewImage ? <img src={day.previewImage.src} className="img-fluid hideme" /> : null}


                                    <small></small>

                                </span>
                            </div>
                            <div>
                                <span>
                                    {day.previewImage ? <img src={day.previewImage.src} className="img-fluid d-block d-sm-none hideme" /> : null}
                                    <small className="d-block d-sm-none"></small>
                                </span>
                            </div>
                        </motion.div>

                    </div>
                </div>
            </div>
        </div>
    )
}

export default LandingItemAnalogSection