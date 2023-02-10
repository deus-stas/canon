import React, {useEffect, useState} from 'react'
import './style.scss'
import FilterSelect from '../FilterSelect'
import {useTemplateContext} from "../../../../contexts/TemplateContext";

const Filter = params => {
    const lang = useTemplateContext().lang
    const [filterCity, setFilterCity] = useState([])
    const [filterCountry, setFilterCountry] = useState([])
    const [values, setValues] = useState([])
    useEffect(() => {

        const itemsCity = params.items.map(item => item.city)
        setFilterCity([...new Set(itemsCity)])

        const itemsCountry = params.items.map(item => item.country)
        setFilterCountry([...new Set(itemsCountry)])

    }, [params.items])

    const handleChangeFilterCountry = event => {
        const tmpValues = values
        if (tmpValues.country && tmpValues.country !== event) {
            delete tmpValues.city
        }
        tmpValues.country = event
        params.change(tmpValues)
        setValues(tmpValues)

        const tmpItems = params.items.filter(item => item.country === event)
        setFilterCity([...new Set(tmpItems.map(item => item.city))])

    }
    const handleChangeFilterCity = event => {
        const tmpValues = values
        const item = params.items.find(item => item.city === event)
        tmpValues.country = item.country
        tmpValues.city = event
        params.change(tmpValues)
        setValues(tmpValues)
    }

    return (
        <div className="filter">
            <FilterSelect title={lang === 'en' ? 'Country' : 'Страна'}
                          defaultValue={lang === 'en' ? 'All countries' : 'Все страны'} value={values.country}
                          list={filterCountry} change={handleChangeFilterCountry}/>
            <FilterSelect title={lang === 'en' ? 'City' : 'Город'}
                          defaultValue={lang === 'en' ? 'All cities' : 'Все города'} value={values.city}
                          list={filterCity}
                          change={handleChangeFilterCity}/>
        </div>
    )
}

export default Filter
