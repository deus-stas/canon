import React, { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchFavorites } from '@/store'
import { inFinalState, inInitialState } from '@/store/helpers'
import { Link } from 'react-router-dom'
import { useTemplateContext } from '@/contexts/TemplateContext'

import './style.scss'

const Favorites = () => {
  const lang = useTemplateContext().lang
  const dispatch = useDispatch()
  const favoritesStoreChunk = useSelector(store => store['favorites'])

  useEffect(() => {
    if (!favoritesStoreChunk || inInitialState(favoritesStoreChunk)) {
      dispatch(fetchFavorites(lang))
    }
  }, [dispatch, favoritesStoreChunk])

  if (!inFinalState(favoritesStoreChunk)) {
    return null
  }

  const { data: favorites } = favoritesStoreChunk

  return favorites.length ?
    <div className="container favorites-container">
      <div className="flex wrapper">
        {
          favorites.map((favorite, index) => (
            <Link className="flex-column favorite-item ntd" key={index} to={favorite.link}>
              <div className="favorite-item__image">
                {
                  favorite.image ?
                    <img alt={favorite.name} src={favorite.image.src} /> :
                    null
                }
              </div>
              <h2 className="favorite-item__title" dangerouslySetInnerHTML={{ __html: favorite.name }} />
              <div className="favorite-item__text" dangerouslySetInnerHTML={{ __html: favorite.description }} />
            </Link>
          ))
        }
      </div>
    </div> : null

}

export default Favorites
