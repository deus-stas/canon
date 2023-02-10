import { applyMiddleware, createStore } from 'redux'
import thunk from 'redux-thunk'
import rootReducer from './chunks'

export * from './chunks'
export default createStore(rootReducer, applyMiddleware(thunk))
