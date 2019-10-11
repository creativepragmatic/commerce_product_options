import * as types from '../actions/action-types';

const initialState = {
  fields: []
};

const optionReducer = function(state = initialState, action) {

  switch(action.type) {
    case types.GET_FIELDS_INFO_SUCCESS:
    case types.ADD_TEXT_FIELD_SUCCESS:
    case types.ADD_CHECKBOX_SUCCESS:
    case types.ADD_SELECT_SUCCESS:
    case types.MOVE_UP_FIELD_SUCCESS:
    case types.MOVE_DOWN_FIELD_SUCCESS:
    case types.DELETE_FIELD_SUCCESS:
      return Object.assign(...state, {
        fields: action.fields
      });
    default:
      return state;
  }
}

export default optionReducer;
