import React, { Component } from 'react'
import axios from 'axios';
import store from '../store';
import * as types from '../actions/action-types';

export class OptionSetRow extends Component {

  constructor(props) {
    super(props);

    this.state = {
      index: props.fieldIndex
    };

    this.handleMoveUp = this.handleMoveUp.bind(this);
    this.handleMoveDown = this.handleMoveDown.bind(this);
    this.handleDelete = this.handleDelete.bind(this);
  }

  handleMoveUp(event) {
    event.preventDefault();

    var baseData = {
      operation: 'MOVE_UP_FIELD',
      product_id: document.getElementById('product-id').value,
      index: this.state.index
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
            type: types.MOVE_UP_FIELD_SUCCESS,
            fields: response.data
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

  handleMoveDown(event) {
    event.preventDefault();

    var baseData = {
      operation: 'MOVE_DOWN_FIELD',
      product_id: document.getElementById('product-id').value,
      index: this.state.index
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
            type: types.MOVE_DOWN_FIELD_SUCCESS,
            fields: response.data
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

  handleDelete(event) {
    event.preventDefault();

    if (confirm("Are you sure you want to delete this field?"))
    {
      var baseData = {
        operation: 'DELETE_FIELD',
        product_id: document.getElementById('product-id').value,
        index: this.state.index
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
              type: types.DELETE_FIELD_SUCCESS,
              fields: response.data
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
  }

  render() {
    if (this.props.rowType === 'field-row' && this.props.type === 'textfield') {
      return (
        <tr className={this.props.rowType}>
          <td className="field-title" colspan="2">{this.props.title}</td>
          <td colspan="2">&nbsp;</td>
          <td>{this.props.size}</td>
          <td className="field-required">{this.props.required}</td>
          <td>{this.props.type}</td>
          <td className="center"><button onClick={this.handleMoveUp}>UP</button></td>
          <td className="center"><button onClick={this.handleMoveDown}>DOWN</button></td>
          <td className="center"><button onClick={this.handleDelete}>DELETE</button></td>
        </tr>
      );
    }
    else if (this.props.rowType === 'field-row' && this.props.type === 'checkbox') {
      if (this.props.skuGeneration) {
        return (
          <tr className={this.props.rowType}>
            <td colspan="2">{this.props.title}</td>
            <td>{this.props.skuSegment}</td>
            <td>{this.props.priceModifier}</td>
            <td>&nbsp;</td>
            <td className="field-required">{this.props.required}</td>
            <td>{this.props.type}</td>
            <td className="center"><button onClick={this.handleMoveUp}>UP</button></td>
            <td className="center"><button onClick={this.handleMoveDown}>DOWN</button></td>
            <td className="center"><button onClick={this.handleDelete}>DELETE</button></td>
          </tr>
        );
      }
      else {
        return (
        <tr className={this.props.rowType}>
          <td colspan="5">{this.props.title}</td>
          <td className="field-required">{this.props.required}</td>
          <td>{this.props.type}</td>
          <td className="center"><button onClick={this.handleMoveUp}>UP</button></td>
          <td className="center"><button onClick={this.handleMoveDown}>DOWN</button></td>
          <td className="center"><button onClick={this.handleDelete}>DELETE</button></td>
          </tr>
        );
      }
    }
    else if (this.props.rowType === 'field-row' && this.props.type === 'select') {
      return (
        <tr className={this.props.rowType}>
          <td className="field-title" colspan="5">{this.props.title}</td>
          <td className="field-required">{this.props.required}</td>
          <td>{this.props.type}</td>
          <td className="center"><button onClick={this.handleMoveUp}>UP</button></td>
          <td className="center"><button onClick={this.handleMoveDown}>DOWN</button></td>
          <td className="center"><button onClick={this.handleDelete}>DELETE</button></td>
        </tr>
      );
    }
    else if (this.props.rowType === 'field-row' && this.props.type === 'add-on') {
      return (
        <tr className={this.props.rowType}>
          <td colspan="5">{this.props.title}</td>
          <td className="field-required">{this.props.required}</td>
          <td>{this.props.type}</td>
          <td className="center"><button onClick={this.handleMoveUp}>UP</button></td>
          <td className="center"><button onClick={this.handleMoveDown}>DOWN</button></td>
          <td className="center"><button onClick={this.handleDelete}>DELETE</button></td>
        </tr>
      );
    }
    else if (this.props.rowType === 'option-row') {
      return (
        <tr>
          <td>{this.props.title}</td>
          <td className="option-default">{this.props.isDefault}</td>
          <td className="option-sku">{this.props.sku}</td>
          <td className="option-price">{this.props.modifier}</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
      );
    }
  }
}
