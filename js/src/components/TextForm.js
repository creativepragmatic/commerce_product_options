import React, { Component } from 'react';
import axios from 'axios';
import store from '../store';
import * as types from '../actions/action-types';

export class TextForm extends Component {

  constructor(props) {
    super(props);
    this.state = {
      fieldText: '',
      helpText: '',
      size: 30,
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

    var textFieldData = {
      operation: 'ADD_TEXT_FIELD',
      product_id: document.getElementById('product-id').value,
      type: 'textfield',
      title: this.state.fieldText,
      helpText: this.state.helpText,
      size: this.state.size,
      required: this.state.isRequired
    };

    axios.get(Drupal.url('rest/session/token'))
      .then(function (response) {
        return response.data;
      })
      .then(function (csrfToken) {
        axios({
          method: 'PATCH',
          url: Drupal.url('commerce_product_option') + '/' + textFieldData.product_id + '?_format=json',
          data: JSON.stringify(textFieldData),
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          }
        })
        .then(function(response) {
          var action = {
            type: types.ADD_TEXT_FIELD_SUCCESS,
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
      fieldText: '',
      helpText: '',
      size: 30,
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
        <label>Text field label: <span className="required-asterisk">*</span><br/>
          <input
            name="fieldText"
            type="text"
            required
            value={this.state.fieldText}
            onChange={this.handleInputChange} />
        </label>
        <label>Help text:<br/>
          <textarea
            name="helpText"
            rows="2"
            cols="20"
            value={this.state.helpText}
            onChange={this.handleInputChange} />
        </label>
        <label>Size:<br/>
          <input
            name="size"
            type="text"
            size="3"
            value={this.state.size}
            onChange={this.handleInputChange} />
        </label>
        <div>
          <input
            name="isRequired"
            type="checkbox"
            checked={this.state.isRequired}
            onChange={this.handleInputChange} />
          <span>&nbsp;Required</span>
        </div>
        <input type="submit" value="Save" />
        <button onClick={this.handleClear}>Clear</button>
      </form>
    );
  }
}
