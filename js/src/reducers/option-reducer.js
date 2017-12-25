import * as types from '../actions/action-types';

const initialState = {
  fields: []
};

const optionReducer = function(state = initialState, action) {

  switch(action.type) {

    case types.GET_FIELDS_INFO_SUCCESS:
      return Object.assign(...state, {
        fields: action.fields
      });
    case types.ADD_TEXT_FIELD_SUCCESS:
      return Object.assign(...state, {
        fields: action.fields
      });
    default:
      return state;
  }
}

export default optionReducer;
