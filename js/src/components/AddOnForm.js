import React, { Component } from 'react';
import axios from 'axios';
import store from '../store';
import * as types from '../actions/action-types';

export class AddOnForm extends Component {

  constructor(props) {
    super(props);

    this.state = {
      products: [],
      allRoles: [],
      addOnId: 0,
      addOnTitle: '',
      requiredRoles: [],
      helpText: '',
      isRequired: false
    };

    this.handleInputChange = this.handleInputChange.bind(this);
    this.handleSubmit = this.handleSubmit.bind(this);
    this.handleClearAll = this.handleClearAll.bind(this);
  }

  componentDidMount() {
    this.getProducts();
    this.getAllRoles();
  }

  getProducts() {

    var self = this;
    axios.get(Drupal.url('rest/session/token'))
      .then(function (response) {
        return response.data;
      })
      .then(function (csrfToken) {
        axios({
          method: 'GET',
          url: Drupal.url('commerce_product_option') + '/published-products' + '?_format=json',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          }
        })
        .then(function(response) {

          var arrProducts = [];
          for (let [id, name] of Object.entries(response.data)) {
            arrProducts.push({id:id, name:name});
          }

          arrProducts.sort((a,b) => (a.name > b.name) ? 1 : ((b.name > a.name) ? -1 : 0));

          let productOptions = arrProducts.map((data) =>
            <option key={data.id} value={data.id}>{data.name}</option>
          );

          self.setState({
            products: productOptions
          });
        })
        .catch(function (error) {
console.log(error);
        });
      })
      .catch(function (error) {
console.log(error);
      });
  }

  getAllRoles() {

    var self = this;
    axios.get(Drupal.url('rest/session/token'))
      .then(function (response) {
        return response.data;
      })
      .then(function (csrfToken) {
        axios({
          method: 'GET',
          url: Drupal.url('commerce_product_option') + '/roles' + '?_format=json',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          }
        })
        .then(function(response) {

          var arrRoles = [];
          for (let [id, name] of Object.entries(response.data)) {
            arrRoles.push({id:id, name:name});
          }

          arrRoles.sort((a,b) => (a.name > b.name) ? 1 : ((b.name > a.name) ? -1 : 0));

          let userRoles = arrRoles.map((data) =>
            <option key={data.id} value={data.id}>{data.name}</option>
          );

          self.setState({
            allRoles: userRoles
          });
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
      addOnId: 0,
      addOnTitle: '',
      requiredRoles: [],
      helpText: '',
      isRequired: false,
	});
  }

  handleInputChange(event) {

    var value;
    const target = event.target;
    const name = target.name;

    if (target.type === 'select-multiple') {

      value = [];
      const options = target.options;

      for (var i = 0; i < options.length; i++) {
        if (options[i].selected) {
          value.push(options[i].value);
        }
      }
    }
    else if (target.type === 'checkbox') {
      value = target.checked;
    }
    else {
      value = target.value
    }

    this.setState({
      [name]: value
    });

    if (name === 'addOnId') {
      this.setState({
        ['addOnTitle']: target.options[target.selectedIndex].text
      });
    }
  }

  handleClearAll(event) {
    event.preventDefault();
    this.clearAll();
  }

  handleSubmit(event) {
    var _this = this;
    event.preventDefault();

    var addOnData = {
      operation: 'ADD_ADD_ON',
      product_id: document.getElementById('product-id').value,
      type: 'add-on',
      addOnId: this.state.addOnId,
      addOnTitle: this.state.addOnTitle,
      requiredRoles: this.state.requiredRoles,
      helpText: this.state.helpText,
      required: this.state.isRequired
    };

    axios.get(Drupal.url('rest/session/token'))
      .then(function (response) {
        return response.data;
      })
      .then(function (csrfToken) {
        axios({
          method: 'PATCH',
          url: Drupal.url('commerce_product_option') + '/' + addOnData.product_id + '?_format=json',
          data: JSON.stringify(addOnData),
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          }
        })
        .then(function(response) {
          var action = {
            type: types.ADD_ADD_ON_SUCCESS,
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

  render() {
    return (
      <form onSubmit={this.handleSubmit}>
        <label>Select Add-on product: <span className="required-asterisk">*</span><br/>
          <select
            name="addOnId"
            value={this.state.addOnId}
            onChange={this.handleInputChange}>
            {this.state.products}
          </select>
        </label>
        <label>Required role(s): <span className="required-asterisk">*</span><br/>
          <select
            multiple
            name="requiredRoles"
            size={this.state.allRoles.length}
            value={this.state.requiredRoles}
            onChange={this.handleInputChange}>
            {this.state.allRoles}
          </select>
        </label>
        <label>Help text:<br/>
          <textarea
            name="helpText"
            rows="2"
            cols="20"
            value={this.state.helpText}
            onChange={this.handleInputChange} />
        </label>
        <input
          name="isRequired"
          type="checkbox"
          checked={this.state.isRequired}
          onChange={this.handleInputChange} />
        <span>&nbsp;Required</span>
        <br/>
        <input type="submit" value="Save" />
        <button onClick={this.handleClearAll}>Clear All</button>
      </form>
    );
  }
}
