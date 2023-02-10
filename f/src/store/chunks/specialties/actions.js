import { fetchSpecialtiesItem as fetchSpecialtiesItemFromServer } from '@/api'
import { STATES } from '../consts'
import { STORE_CHUNK_STATES_ACTION_TYPES, storeChunkName } from './consts'

const { SET_ERROR_STATE, SET_LOADED_STATE, SET_LOADING_STATE } = STORE_CHUNK_STATES_ACTION_TYPES

const setLoadingState = ItemCode => ({
  type: SET_LOADING_STATE,
  payload: {
    ItemCode
  }
})

const setLoadedState = (ItemCode, catalogItemData) => ({
  type: SET_LOADED_STATE,
  payload: {
    ItemCode,
    catalogItemData
  }
})

const setErrorState = (ItemCode, { message }) => ({
  type: SET_ERROR_STATE,
  payload: {
    ItemCode,
    message
  }
})

export const fetchSpecialtiesItem = (ItemCode, region, lang) => (dispatch, getState) => {
  const {
    [storeChunkName]: { [ItemCode]: { state = STATES.INITIAL } = {} }
  } = getState()

  if (state !== STATES.LOADING) {
    dispatch(setLoadingState())
    fetchSpecialtiesItemFromServer(ItemCode, region, lang)
      .then((...args) => dispatch(setLoadedState.apply(null, [ItemCode, ...args])))
      .catch((...args) => dispatch(setErrorState.apply(null, [ItemCode, ...args])))
  }
}
