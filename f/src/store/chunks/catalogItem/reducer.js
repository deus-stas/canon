import { STATES } from '../consts'
import { STORE_CHUNK_STATES_ACTION_TYPES } from './consts'

const { SET_ERROR_STATE, SET_LOADED_STATE, SET_LOADING_STATE } = STORE_CHUNK_STATES_ACTION_TYPES

export const reducer = (
  storeChunk = {
    state: STATES.INITIAL
  },
  { type: actionType, payload: actionPayload }
) => {
  switch (actionType) {
    case SET_LOADING_STATE:
      const { catalogItemCode } = actionPayload

      return {
        ...storeChunk,
        [catalogItemCode]: {
          state: STATES.LOADING
        }
      }
    case SET_LOADED_STATE: {
      const { catalogItemCode, catalogItemData } = actionPayload

      return {
        state: STATES.LOADED,
        [catalogItemCode]: {
          state: STATES.LOADED,
          data: catalogItemData
        }
      }
    }
    case SET_ERROR_STATE: {
      const { catalogItemCode, message } = actionPayload

      return {
        ...storeChunk,
        [catalogItemCode]: {
          state: STATES.ERROR,
          errorMessage: message
        }
      }
    }
    default:
      return storeChunk
  }
}
