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
      const { landingsDaysCode } = actionPayload

      return {
        ...storeChunk,
        [landingsDaysCode]: {
          state: STATES.LOADING
        }
      }
    case SET_LOADED_STATE: {
      const { landingsDaysCode, landingsDaysData } = actionPayload

      return {
        state: STATES.LOADED,
        [landingsDaysCode]: {
          state: STATES.LOADED,
          data: landingsDaysData
        }
      }
    }
    case SET_ERROR_STATE: {
      const { landingsDaysCode, message } = actionPayload

      return {
        ...storeChunk,
        [landingsDaysCode]: {
          state: STATES.ERROR,
          errorMessage: message
        }
      }
    }
    default:
      return storeChunk
  }
}
