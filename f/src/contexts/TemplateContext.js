import React, { createContext, useContext, useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { inInitialState } from '@/store/helpers'
import { fetchTemplateSettings } from '@/store'

export const TemplateContext = createContext({})

export const useTemplateContext = () => useContext(TemplateContext)

export const useTemplateContextValue = lang => {
  const dispatch = useDispatch()
  const templateSettingsStoreChunk = useSelector(store => store.templateSettings)

  useEffect(() => {
    if (!templateSettingsStoreChunk || inInitialState(templateSettingsStoreChunk)) {
      dispatch(fetchTemplateSettings(lang))
    }
  }, [dispatch, templateSettingsStoreChunk])

  return templateSettingsStoreChunk.data
}
