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
      const { landingsItemsCode } = actionPayload

      return {
        ...storeChunk,
        [landingsItemsCode]: {
          state: STATES.LOADING
        }
      }
    case SET_LOADED_STATE: {
      const { landingsItemsCode, landingsItemsData } = actionPayload

      return {
        state: STATES.LOADED,
        [landingsItemsCode]: {
          state: STATES.LOADED,
          data: landingsItemsData
        }
      }
    }
    case SET_ERROR_STATE: {
      const { landingsItemsCode, message } = actionPayload

      return {
        ...storeChunk,
        [landingsItemsCode]: {
          state: STATES.ERROR,
          errorMessage: message
        }
      }
    }
    default:
      return storeChunk
  }
}
