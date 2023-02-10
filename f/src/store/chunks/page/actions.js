import { fetchPage as fetchPageFromServer } from '../../../api'
import { STATES } from '../consts'
import { STORE_CHUNK_STATES_ACTION_TYPES, storeChunkName } from './consts'

const { SET_ERROR_STATE, SET_LOADED_STATE, SET_LOADING_STATE } = STORE_CHUNK_STATES_ACTION_TYPES

const setLoadingState = pageCode => ({
  type: SET_LOADING_STATE,
  payload: {
    pageCode
  }
})

const setLoadedState = (pageCode, pageData) => ({
  type: SET_LOADED_STATE,
  payload: {
    pageCode,
    pageData
  }
})

const setErrorState = (pageCode, { message }) => ({
  type: SET_ERROR_STATE,
  payload: {
    pageCode,
    message
  }
})

export const fetchPage = (pageCode, lang) => (dispatch, getState) => {
  const {
    [storeChunkName]: { [pageCode]: { state = STATES.INITIAL } = {} }
  } = getState()

  if (state !== STATES.LOADING) {
    dispatch(setLoadingState(pageCode))
    fetchPageFromServer(pageCode, lang)
      .then((...args) => dispatch(setLoadedState.apply(null, [pageCode, ...args])))
      .catch((...args) => dispatch(setErrorState.apply(null, [pageCode, ...args])))
  }
}
