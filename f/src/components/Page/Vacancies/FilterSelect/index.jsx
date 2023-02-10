import React, { useEffect, useState } from 'react'
import './style.scss'
import classNames from 'classnames'

const FilterSelect = params => {

  const [curValue, setCurValue] = useState(params.defaultValue)
  const [open, setOpen] = useState(false)

  const handleChange = parameter => event => {
    params.change(parameter)
    setCurValue(parameter)
    setOpen(false)
  }
  useEffect(() => {
    if (params.value) {
      setCurValue(params.value)
    } else {
      setCurValue(params.defaultValue)
    }

  }, [params.value])
  const handleOpen = () => {
    setOpen(!open)
  }

  return  (
    <div className="select">
      <label>{params.title}</label>
      <div className={classNames('overlay ' + (open ? 'open' : ''))} onClick={handleOpen}/>
      <div className={classNames('pseudoSelect ' + (open ? 'open' : ''))} onClick={handleOpen}>
        {curValue}
      </div>
      <div className={classNames('list ' + (open ? 'open' : ''))}>
        { params.list && params.list.map((item, index) => (
          <div key={index} onClick={handleChange(item)}>{item}</div>
        ))}
      </div>
    </div>
  )
}

export default FilterSelect
