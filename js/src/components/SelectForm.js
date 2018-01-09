import React, { Component } from 'react'
import axios from 'axios';
import store from '../store';
import * as types from '../actions/action-types';

export class SelectForm extends Component {

  constructor(props) {
    super(props);
    this.state = {
      title: '',
      options: [],
      isRequired: false,
      newOption: {
        optionTitle: '',
        isDefault: false,
        skuSegment: '',
        priceModifier: ''
      }
	};

    this.handleInputChange = this.handleInputChange.bind(this);
    this.handleAddOptionChange = this.handleAddOptionChange.bind(this);
    this.handleAddOption = this.handleAddOption.bind(this);
    this.handleClearOption = this.handleClearOption.bind(this);
    this.handleSubmit = this.handleSubmit.bind(this);
    this.handleClearSelectAll = this.handleClearSelectAll.bind(this);
  }

  handleInputChange(event) {
    const target = event.target;
    const value = target.type === 'checkbox' ? target.checked : target.value;
    const name = target.name;

    this.setState({
      [name]: value
    });
  }

  handleAddOptionChange(event) {
    const target = event.target;
    const value = target.type === 'checkbox' ? target.checked : target.value;
    const name = target.name;
    const changedOption = this.state.newOption;
    changedOption[name] = value;
    this.setState({
      newOption: changedOption
	});
  }

  handleAddOption(event) {
    event.preventDefault();

    var changedOptions = this.state.options;
    changedOptions.push(this.state.newOption);
    this.setState({
      options: changedOptions
    });
    this.clearOption();
  }

  clearOption() {
    this.setState({
      newOption: {
        optionTitle: '',
        isDefault: false,
        skuSegment: '',
        priceModifier: ''
      }
	});
  }

  handleClearOption(event) {
    event.preventDefault();
    this.clearOption();
  }

  handleSubmit(event) {
    var _this = this;
    event.preventDefault();

    var selectData = {
      operation: 'ADD_SELECT',
      product_id: document.getElementById('product-id').value,
      type: 'select',
      title: this.state.title,
      options: this.state.options,
      required: this.state.isRequired
    };

    axios.get(Drupal.url('rest/session/token'))
      .then(function (response) {
        return response.data;
      })
      .then(function (csrfToken) {
        axios({
          method: 'PATCH',
          url: Drupal.url('commerce_product_option') + '/' + selectData.product_id,
          data: JSON.stringify(selectData),
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          }
        })
        .then(function(response) {
          var action = {
            type: types.ADD_SELECT_SUCCESS,
            fields: response.data
          };
          store.dispatch(action);
          _this.clearAll();
        })
        .catch(function (error) {
console.log(error);
        });
      })
      .catch(function (error) {
console.log(error);
      });
  }

  clearAll() {
    this.setState({
      title: '',
      options: [],
      newOption: {
        optionTitle: '',
        isDefault: false,
        skuSegment: '',
        priceModifier: ''
      },
      isRequired: false
	});
  }

  handleClearSelectAll(event) {
    event.preventDefault();
    this.clearAll();
  }

  render() {
    return (
      <form onSubmit={this.handleSubmit}>
        <label>Select box title: <span className="required-asterisk">*</span><br/>
          <input
            name="title"
            type="text"
            required
            value={this.state.title}
            onChange={this.handleInputChange} />
        </label>
        <fieldset>
          <legend>Options</legend>
          <div id="select-option-container">
            <div id="select-option-fields">
              <label>Title: <span className="required-asterisk">*</span><br/>
                <input
                  name="optionTitle"
                  type="text"
                  value={this.state.newOption.optionTitle}
                  onChange={this.handleAddOptionChange} />
              </label>
              <div>
                <input
                  name="isDefault"
                  type="checkbox"
                  value={this.state.newOption.isDefault}
                  onChange={this.handleAddOptionChange} />
                <span>&nbsp;Default</span>
              </div>
              <label>SKU segment:<br/>
                <input
                  name="skuSegment"
                  type="text"
                  size="8"
                  value={this.state.newOption.skuSegment}
                  onChange={this.handleAddOptionChange} />
              </label>
              <label>Price modifier:<br/>
                <input
                  name="priceModifier"
                  type="text"
                  size="6"
                  maxLength="6"
                  value={this.state.newOption.priceModifier}
                  onChange={this.handleAddOptionChange} />
              </label>
            </div>
            <div>
              <label>Option list:<br/>
                <ul>
                  {this.state.options.map((option, index) =>
                    <li key={index}>
                      {option.optionTitle}
                    </li>
                  )}
                </ul>
              </label>
            </div>
          </div>
          <button onClick={this.handleAddOption}>Add</button>
          <button onClick={this.handleClearOption}>Clear</button>
        </fieldset>
        <div>
          <input
            name="isRequired"
            type="checkbox"
            checked={this.state.isRequired}
            onChange={this.handleInputChange} />
          <span>&nbsp;Required</span>
        </div>
        <input type="submit" value="Save" />
        <button onClick={this.handleClearSelectAll}>Clear All</button>
      </form>
    );
  }
}
