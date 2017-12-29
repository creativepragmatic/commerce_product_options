import React, { Component } from 'react';
import axios from 'axios';
import store from '../store';
import * as types from '../actions/action-types';

export class BaseForm extends Component {

  constructor(props) {
    super(props);
    this.state = {
      baseSKU: store.getState().baseVariationState.baseSKU,
      basePrice: store.getState().baseVariationState.basePrice
    };

    store.subscribe(() => this.setState({
      baseSKU: store.getState().baseVariationState.baseSKU,
      basePrice: store.getState().baseVariationState.basePrice
    }));

    this.handleInputChange = this.handleInputChange.bind(this);
    this.handleSubmit = this.handleSubmit.bind(this);
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
            type: types.GET_BASE_INFO_SUCCESS,
            baseSKU: response.data.base_sku,
            basePrice: response.data.base_price
          };
          store.dispatch(action);
        })
        .catch(function (error) {
console.log(error);
        });
      });
  }

  handleInputChange(event) {
    const target = event.target;
    const value = target.type === 'checkbox' ? target.checked : target.value;
    const name = target.name;

    this.setState({
      [name]: value
    });
  }

  handleSubmit(event) {
    event.preventDefault();

    var baseData = {
      operation: 'UPDATE_BASE_FIELDS',
      product_id: document.getElementById('product-id').value,
      base_sku: this.state.baseSKU,
      base_price: this.state.basePrice
    };

    axios.get(Drupal.url('rest/session/token'))
      .then(function (response) {
        return response.data;
      })
      .then(function (csrfToken) {
        axios({
          method: 'PATCH',
          url: Drupal.url('commerce_product_option') + '/' + baseData.product_id,
          data: JSON.stringify(baseData),
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          }
        })
        .then(function(response) {
          var action = {
            type: types.UPDATE_BASE_INFO_SUCCESS,
            baseSKU: response.data.base_sku,
            basePrice: response.data.base_price
          };
          store.dispatch(action);
        })
        .catch(function (error) {
console.log(error);
        });
      })
      .catch(function (error) {
console.log(error);
      });
  }

  render() {
    return (
      <form id="base-form" onSubmit={this.handleSubmit}>
        <label>Base SKU: <span className="required-asterisk">*</span><br/>
          <input
            name="baseSKU"
            type="text"
            size="15"
            required
            value={this.state.baseSKU}
            onChange={this.handleInputChange} />
        </label>
        <label>Base Price: <span className="required-asterisk">*</span><br/>
          <input
            name="basePrice"
            type="text"
            size="6"
            maxLength="6"
            required
            value={this.state.basePrice}
            onChange={this.handleInputChange} />
        </label>
        <input type="submit" value="Save" />
      </form>
    );
  }
}
