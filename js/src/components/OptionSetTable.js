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

  buildTable() {
    var rowBuffer = []

    this.state.fields.forEach(function(field, fieldIndex) {
      rowBuffer.push(<OptionSetRow
        key={fieldIndex}
        rowType="field-row"
        title={field.title}
        size={field.size}
        required={field.required ? 'YES' : 'NO'}
        type={field.type} />);
      
      if (field.hasOwnProperty('options') && field.options.length > 0) {
        field.options.forEach(function(option, optionIndex) {
          rowBuffer.push(<OptionSetRow
            key={optionIndex}
            rowType="option-row"
            title={'\xa0\xa0\xa0\xa0\xa0\xa0' + option.optionTitle}
            sku={option.skuSegment}
            modifier={option.priceModifier}
            isDefault={option.isDefault ? 'YES' : 'NO'} />);
        });
      }
    });

    return (
      <tbody>
        {rowBuffer}
      </tbody>
    );
  }

  render() {
    return (
      <table id="option-set-table">
        <thead>
          <tr>
            <th>Title</th>
            <th>SKU Segment</th>
            <th>Price Modifier</th>
            <th>Default</th>
            <th>Size</th>
            <th>Required</th>
            <th>Type</th>
          </tr>
        </thead>
        {this.buildTable()}
      </table>
    );
  }
}
