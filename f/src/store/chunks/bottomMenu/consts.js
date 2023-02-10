import { generateStoreChunkStatesActionTypes } from '../helpers'

export const storeChunkName = 'bottomMenu'

export const STORE_CHUNK_STATES_ACTION_TYPES = Object.freeze(generateStoreChunkStatesActionTypes(storeChunkName))
