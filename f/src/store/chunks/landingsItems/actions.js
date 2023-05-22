import { fetchLandingsItems as fetchLandingsItemsFromServer } from '@/api'
import { STATES } from '../consts'
import { STORE_CHUNK_STATES_ACTION_TYPES, storeChunkName } from './consts'

const { SET_ERROR_STATE, SET_LOADED_STATE, SET_LOADING_STATE } = STORE_CHUNK_STATES_ACTION_TYPES

const setLoadingState = landingsItemsCode => ({
  type: SET_LOADING_STATE,
  payload: {
    landingsItemsCode
  }
})

const setLoadedState = (landingsItemsCode, landingsItemsData) => ({
  type: SET_LOADED_STATE,
  payload: {
    landingsItemsCode,
    landingsItemsData
  }
})

const setErrorState = (landingsItemsCode, { message }) => ({
  type: SET_ERROR_STATE,
  payload: {
    landingsItemsCode,
    message
  }
})

export const fetchLandingsItems = (landingsItemsCode, region, lang) => (dispatch, getState) => {
  const {
    [storeChunkName]: { [landingsItemsCode]: { state = STATES.INITIAL } = {} }
  } = getState()

  if (state !== STATES.LOADING) {
    dispatch(setLoadingState())
    fetchLandingsItemsFromServer(landingsItemsCode, region, lang)
      .then((...args) => dispatch(setLoadedState.apply(null, [landingsItemsCode, ...args])))
      .catch((...args) => dispatch(setErrorState.apply(null, [landingsItemsCode, ...args])))
  }
}
