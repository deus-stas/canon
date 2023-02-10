import { generateStoreChunkStatesActionTypes } from '../helpers'

export const storeChunkName = 'catalog'

export const STORE_CHUNK_STATES_ACTION_TYPES = Object.freeze(generateStoreChunkStatesActionTypes(storeChunkName))
