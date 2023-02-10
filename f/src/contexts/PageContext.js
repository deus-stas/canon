import React, { createContext, useContext, useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { fetchPage } from '@store'
import { inFinalState, inInitialState } from '@store/helpers'

export const PageContext = createContext({})

export const usePageContext = () => useContext(PageContext)

export const usePageContextValue = (pageCode, lang) => {
  const dispatch = useDispatch()
  const pageStoreChunk = useSelector(store => store['page'][pageCode])

  useEffect(() => {
    if (inInitialState(pageStoreChunk)) {
      dispatch(fetchPage(pageCode, lang))
    }
  }, [dispatch, pageStoreChunk])

  if (!inFinalState(pageStoreChunk)) {
    document.documentElement.classList.add('not-in-final-state')
    return null
  }

  if (pageStoreChunk.data && pageStoreChunk.data.code !== 'not-found') {
    document.documentElement.classList.remove('not-in-final-state', 'not-found-state')
  } else {
    document.documentElement.classList.add('not-found-state')
    document.documentElement.classList.remove('not-in-final-state')
  }

  return pageStoreChunk.data
}
