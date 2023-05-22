import { fetchCatalogItem as fetchCatalogItemFromServer } from '@/api'
import { STATES } from '../consts'
import { STORE_CHUNK_STATES_ACTION_TYPES, storeChunkName } from './consts'

const { SET_ERROR_STATE, SET_LOADED_STATE, SET_LOADING_STATE } = STORE_CHUNK_STATES_ACTION_TYPES

const setLoadingState = catalogItemCode => ({
  type: SET_LOADING_STATE,
  payload: {
    catalogItemCode
  }
})

const setLoadedState = (catalogItemCode, catalogItemData) => ({
  type: SET_LOADED_STATE,
  payload: {
    catalogItemCode,
    catalogItemData
  }
})

const setErrorState = (catalogItemCode, { message }) => ({
  type: SET_ERROR_STATE,
  payload: {
    catalogItemCode,
    message
  }
})

export const fetchCatalogItem = (catalogItemCode, region, lang) => (dispatch, getState) => {
  const {
    [storeChunkName]: { [catalogItemCode]: { state = STATES.INITIAL } = {} }
  } = getState()

  if (state !== STATES.LOADING) {
    dispatch(setLoadingState())
    fetchCatalogItemFromServer(catalogItemCode, region, lang)
      .then((...args) => dispatch(setLoadedState.apply(null, [catalogItemCode, ...args])))
      .catch((...args) => dispatch(setErrorState.apply(null, [catalogItemCode, ...args])))
  }
}
