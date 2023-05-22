import { fetchSocialLinks as fetchSocialLinksFromServer } from '../../../api'
import { STATES } from '../consts'
import { STORE_CHUNK_STATES_ACTION_TYPES, storeChunkName } from './consts'

const { SET_ERROR_STATE, SET_LOADED_STATE, SET_LOADING_STATE } = STORE_CHUNK_STATES_ACTION_TYPES

const setLoadingState = () => ({
  type: SET_LOADING_STATE
})

const setLoadedState = socialLinks => ({
  type: SET_LOADED_STATE,
  payload: {
    socialLinks
  }
})

const setErrorState = ({ message }) => ({
  type: SET_ERROR_STATE,
  payload: {
    message
  }
})

export const fetchSocialLinks = lang => (dispatch, getState) => {
  const {
    [storeChunkName]: { state }
  } = getState()

  if (state !== STATES.LOADING) {
    dispatch(setLoadingState())
    fetchSocialLinksFromServer(lang)
      .then((...args) => dispatch(setLoadedState.apply(null, [...args])))
      .catch((...args) => dispatch(setErrorState.apply(null, ...args)))
  }
}
