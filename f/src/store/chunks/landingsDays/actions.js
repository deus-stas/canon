import { fetchLandingsDays as fetchLandingsDaysFromServer } from '@/api'
import { STATES } from '../consts'
import { STORE_CHUNK_STATES_ACTION_TYPES, storeChunkName } from './consts'

const { SET_ERROR_STATE, SET_LOADED_STATE, SET_LOADING_STATE } = STORE_CHUNK_STATES_ACTION_TYPES

const setLoadingState = landingsDaysCode => ({
  type: SET_LOADING_STATE,
  payload: {
    landingsDaysCode
  }
})

const setLoadedState = (landingsDaysCode, landingsDaysData) => ({
  type: SET_LOADED_STATE,
  payload: {
    landingsDaysCode,
    landingsDaysData
  }
})

const setErrorState = (landingsDaysCode, { message }) => ({
  type: SET_ERROR_STATE,
  payload: {
    landingsDaysCode,
    message
  }
})

export const fetchLandingsDays = (landingsDaysCode, region, lang) => (dispatch, getState) => {
  const {
    [storeChunkName]: { [landingsDaysCode]: { state = STATES.INITIAL } = {} }
  } = getState()

  if (state !== STATES.LOADING) {
    dispatch(setLoadingState())
    fetchLandingsDaysFromServer(landingsDaysCode, region, lang)
      .then((...args) => dispatch(setLoadedState.apply(null, [landingsDaysCode, ...args])))
      .catch((...args) => dispatch(setErrorState.apply(null, [landingsDaysCode, ...args])))
  }
}
