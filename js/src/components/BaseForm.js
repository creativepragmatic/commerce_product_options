import React, { Component } from 'react';
import axios from 'axios';
import store from '../store';
import * as types from '../actions/action-types';

export class BaseForm extends Component {

  constructor(props) {
    super(props);
    this.state = {
      baseSKU: store.getState().baseVariationState.baseSKU,
      basePrice: store.getState().baseVariationState.basePrice,
      skuGeneration: store.getState().baseVariationState.skuGeneration
    };

    store.subscribe(() => this.setState({
      baseSKU: store.getState().baseVariationState.baseSKU,
      basePrice: store.getState().baseVariationState.basePrice,
      skuGeneration: store.getState().baseVariationState.skuGeneration
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
            basePrice: response.data.base_price,
            skuGeneration: response.data.sku_generation
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

  handleInputChange(event) {
    let value = event.target.value;
    let name = event.target.name;

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
      base_price: this.state.basePrice,
      sku_generation: this.state.skuGeneration
    };

    axios.get(Drupal.url('rest/session/token'))
      .then(function (response) {
        return response.data;
      })
      .then(function (csrfToken) {
        axios({
          method: 'PATCH',
          url: Drupal.url('commerce_product_option') + '/' + baseData.product_id + '?_format=json',
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
            basePrice: response.data.base_price,
            skuGeneration: response.data.sku_generation
          };
          store.dispatch(action);
        })
        .catch(function (error) {
          console.log(error.message);
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
        <label>SKU Generation: <span className="required-asterisk">*</span><br/>
          <input
            id="by-option"
            name="skuGeneration"
            type="radio"
            value="byOption"
            onChange={this.handleInputChange}
            checked={this.state.skuGeneration === 'byOption'} />
          <label id="by-option-label" for="by-option">By option</label><br/>
          <input
            id="by-segment"
            name="skuGeneration"
            type="radio"
            value="bySegment"
            onChange={this.handleInputChange}
            checked={this.state.skuGeneration === 'bySegment'} />
          <label id="by-segment-label" for="by-segment">By option segment</label>
        </label>
        <input type="submit" value="Save" />
      </form>
    );
  }
}
