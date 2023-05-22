import { fetchTemplateSettings as fetchTemplateSettingsFromServer } from '@/api'
import { STATES } from '../consts'
import { STORE_CHUNK_STATES_ACTION_TYPES, storeChunkName, SET_LANG } from './consts'

const { SET_ERROR_STATE, SET_LOADED_STATE, SET_LOADING_STATE } = STORE_CHUNK_STATES_ACTION_TYPES

const setLoadingState = () => ({
  type: SET_LOADING_STATE
})

const setLoadedState = templateSettings => ({
  type: SET_LOADED_STATE,
  payload: {
    templateSettings
  }
})

const setErrorState = ({ message }) => ({
  type: SET_ERROR_STATE,
  payload: {
    message
  }
})

export const setLang = lang => ({
  type: SET_LANG,
  payload: {
    lang
  }
})

export const fetchTemplateSettings = lang => (dispatch, getState) => {
  const {
    [storeChunkName]: { state }
  } = getState()
  // const lang = getCurrentLang()
  if (state !== STATES.LOADING) {
    dispatch(setLoadingState())
    fetchTemplateSettingsFromServer(lang)
      .then((...args) => dispatch(setLoadedState.apply(null, [...args])))
      .catch((...args) => dispatch(setErrorState.apply(null, ...args)))
  }
}
