import { combineReducers } from 'redux';

// Reducers
import baseVariationReducer from './base-variation-reducer';
import optionReducer from './option-reducer';

// Combine Reducers
var reducers = combineReducers({
    baseVariationState: baseVariationReducer,
    optionState: optionReducer
});

export default reducers;
