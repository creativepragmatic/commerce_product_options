import * as types from '../actions/action-types';

const initialState = {
  baseSKU: '',
  basePrice: 0
};

const baseVariationReducer = function(state = initialState, action) {

  switch(action.type) {

    case types.GET_BASE_INFO_SUCCESS:
      return Object.assign(...state, {
        baseSKU: action.baseSKU,
        basePrice: action.basePrice
      });
    case types.UPDATE_BASE_INFO_SUCCESS:
      return Object.assign(...state, {
        baseSKU: action.baseSKU,
        basePrice: action.basePrice
      });
    default:
      return state
  }
}

export default baseVariationReducer;
