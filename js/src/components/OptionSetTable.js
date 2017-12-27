import React, { Component } from 'react'
import axios from 'axios';
import { OptionSetRow } from './OptionSetRow';
import store from '../store';
import * as types from '../actions/action-types';

export class OptionSetTable extends Component {

  constructor(props) {
    super(props);
    this.state = {
      fields: store.getState().optionState.fields
    };

    store.subscribe(() => this.setState({
      fields: store.getState().optionState.fields
    }));
  }

  componentDidMount() {
    axios.get(Drupal.url('rest/session/token'))
      .then(function (response) {
        return response.data;
      })
      .then(function (csrfToken) {
        axios({
          method: 'GET',
          url: Drupal.url('commerce_product_option') + '/' + document.getElementById('product-id').value + '?_format=json',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          }
        })
        .then(function(response) {
          var action = {
            type: types.GET_FIELDS_INFO_SUCCESS,
            fields: response.data.fields
          };
          store.dispatch(action);
        })
        .catch(function (error) {
console.log(error);
        });
      });
  }

  render() {
    return (
      <table className="option-set-table">
        <thead>
          <tr>
            <th>Type</th>
            <th>Title</th>
            <th>SKU Segment</th>
            <th>Price Modifier</th>
            <th>Size</th>
            <th>Required</th>
          </tr>
        </thead>
        <tbody>
          {this.state.fields.map((option, index) => (
            <OptionSetRow
              key={index}
              type={option.type} 
              title={option.title}
              size={option.size}
              required={option.required ? 'YES' : 'NO'} />
          ))}

        </tbody>
      </table>
    );
  }
}
