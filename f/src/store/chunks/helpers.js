export const generateStoreChunkStatesActionTypes = chunkName =>
  Object.freeze({
    SET_LOADING_STATE: `${chunkName}:set-loading-state`,
    SET_ERROR_STATE: `${chunkName}:set-error-state`,
    SET_LOADED_STATE: `${chunkName}:set-loaded-state`
  })
