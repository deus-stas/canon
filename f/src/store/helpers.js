import { STATES } from './chunks/consts'

export const inInitialState = storeChunk => STATES.INITIAL === storeChunk?.state
export const inErrorState = storeChunk => STATES.ERROR === storeChunk?.state
export const inLoadedState = storeChunk => STATES.LOADED === storeChunk?.state
export const inFinalState = storeChunk => inLoadedState(storeChunk) || inErrorState(storeChunk)
