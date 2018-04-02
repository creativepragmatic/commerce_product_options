import React, { Component } from 'react';
import axios from 'axios';
import store from '../store';
import * as types from '../actions/action-types';

export class CheckboxForm extends Component {

  constructor(props) {
    super(props);
    this.state = {
      checkboxTitle: '',
      skuSegment: '',
      priceModifier: '',
      skuGeneration: false,
      mandatoryOption: false,
      isRequired: false
    };

    this.handleInputChange = this.handleInputChange.bind(this);
    this.handleSubmit = this.handleSubmit.bind(this);
    this.handleClear = this.handleClear.bind(this);
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
    var _this = this;
    event.preventDefault();

    var checkboxData = {
      operation: 'ADD_CHECKBOX',
      product_id: document.getElementById('product-id').value,
      type: 'checkbox',
      title: this.state.checkboxTitle,
      skuSegment: this.state.skuSegment,
      priceModifier: this.state.priceModifier,
      skuGeneration: this.state.skuGeneration,
      mandatory: this.state.mandatoryOption,
      required: this.state.isRequired
    };

    axios.get(Drupal.url('rest/session/token'))
      .then(function (response) {
        return response.data;
      })
      .then(function (csrfToken) {
        axios({
          method: 'PATCH',
          url: Drupal.url('commerce_product_option') + '/' + checkboxData.product_id + '?_format=json',
          data: JSON.stringify(checkboxData),
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          }
        })
        .then(function(response) {
          var action = {
            type: types.ADD_CHECKBOX_SUCCESS,
            fields: response.data
          };
          store.dispatch(action);
          _this.clear();
        })
        .catch(function (error) {
console.log(error);
        });
      })
      .catch(function (error) {
console.log(error);
      });
  }

  clear() {
    this.setState({
      checkboxTitle: '',
      skuSegment: '',
      priceModifier: '',
      skuGeneration: false,
      mandatoryOption: false,
      isRequired: false
    });
  }

  handleClear(event) {
    event.preventDefault();
    this.clear();
  }

  render() {
    return (
      <form onSubmit={this.handleSubmit}>
        <label>Checkbox text: <span className="required-asterisk">*</span><br/>
          <textarea
            name="checkboxTitle"
            rows="4"
            cols="20"
            minLength="1"
            maxLength="250"
            required
            value={this.state.checkboxTitle}
            onChange={this.handleInputChange} />
        </label>
        <label>SKU segment:<br/>
          <input
            name="skuSegment"
            type="text"
            size="8"
            value={this.state.skuSegment}
            onChange={this.handleInputChange} />
        </label>
        <label>Price modifier:<br/>
          <input
            name="priceModifier"
            type="text"
            size="6"
            maxLength="6"
            value={this.state.priceModifier}
            onChange={this.handleInputChange} />
        </label>
        <input
          name="skuGeneration"
          type="checkbox"
          checked={this.state.skuGeneration}
          onChange={this.handleInputChange} />
        <span>&nbsp;Use for SKU generation?</span><br/>
        <input
          name="mandatoryOption"
          type="checkbox"
          checked={this.state.mandatoryOption}
          onChange={this.handleInputChange} />
        <span>&nbsp;Mandatory option?</span><br/>
        <input
          name="isRequired"
          type="checkbox"
          checked={this.state.isRequired}
          onChange={this.handleInputChange} />
        <span>&nbsp;Required</span><br/>
        <input type="submit" value="Save" />
        <button onClick={this.handleClear}>Clear</button>
      </form>
    )
  }
}
