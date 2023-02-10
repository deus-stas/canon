import React, { createContext, useContext, useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { inInitialState } from '@/store/helpers'
import { fetchPages } from '@/store'

export const PagesContext = createContext({})

export const usePagesContext = () => useContext(PagesContext)

export const usePagesContextValue = () => {
  const dispatch = useDispatch()
  const pagesStoreChunk = useSelector(store => store.pages)

  useEffect(() => {
    if (inInitialState(pagesStoreChunk)) {
      dispatch(fetchPages())
    }
  }, [dispatch, pagesStoreChunk])

  return pagesStoreChunk.data
}
