import { fetchNewsItem as fetchNewsItemFromServer } from '@/api'
import { STATES } from '../consts'
import { STORE_CHUNK_STATES_ACTION_TYPES, storeChunkName } from './consts'

const { SET_ERROR_STATE, SET_LOADED_STATE, SET_LOADING_STATE } = STORE_CHUNK_STATES_ACTION_TYPES

const setLoadingState = newsItemCode => ({
  type: SET_LOADING_STATE,
  payload: {
    newsItemCode
  }
})

const setLoadedState = (newsItemCode, newsItemData) => ({
  type: SET_LOADED_STATE,
  payload: {
    newsItemCode,
    newsItemData
  }
})

const setErrorState = (newsItemCode, { message }) => ({
  type: SET_ERROR_STATE,
  payload: {
    newsItemCode,
    message
  }
})

export const fetchNewsItem = (newsItemCode, lang) => (dispatch, getState) => {
  const {
    [storeChunkName]: { [newsItemCode]: { state = STATES.INITIAL } = {} }
  } = getState()

  if (state !== STATES.LOADING) {
    dispatch(setLoadingState())
    fetchNewsItemFromServer(newsItemCode, lang)
      .then((...args) => dispatch(setLoadedState.apply(null, [newsItemCode, ...args])))
      .catch((...args) => dispatch(setErrorState.apply(null, [newsItemCode, ...args])))
  }
}
