import React, { Component } from 'react';
import axios from 'axios';
import store from '../store';
import * as types from '../actions/action-types';

export class VariationsTable extends Component {

  constructor(props) {
    super(props);
    this.state = {
      variations: []
    };

    this.handleGenerate = this.handleGenerate.bind(this);
  }

  componentDidMount() {
    var _this = this;
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
          _this.generateVariations();
        })
        .catch(function (error) {
console.log(error);
        });
      })
      .catch(function (error) {
console.log(error);
      });   
  }

  generateVariations() {
    var allFields = [];
    var variations = [];
    store.getState().optionState.fields.forEach(function(field, index) {
      if (field.type === 'select') {
        allFields.push(field);
      }
    });

    var total = this.getTotalVariations(allFields);
    var treeMap = Array.apply(null, Array(allFields.length - 1)).map(Number.prototype.valueOf, 0);
    this.buildVariations(0, treeMap, allFields, total, variations);
    this.setState({
      variations: variations
    });
  }

  buildVariations(depth, map, allFields, total, variations = []) {

    if (depth === 0) {
      var variation = {
        title: '',
        SKU: store.getState().baseVariationState.baseSKU,
        price: parseFloat(store.getState().baseVariationState.basePrice)
      };
      variations.push(variation);
    }

    if (depth < allFields.length) {
      var lastVariationIndex = variations.length - 1;
      var fieldOptions = allFields[depth].options[map[depth]];

      if (depth === allFields.length - 1) {
        var partialVariation = variations.pop();
        for (var i = 0; i < allFields[depth].options.length; i++) {
          var fullVariation = {
            SKU: partialVariation.SKU + '-' + allFields[depth].options[i].skuSegment,
            price: partialVariation.price + parseFloat(allFields[depth].options[i].priceModifier)
          };
          variations.push(fullVariation);
        }

        map[map.length - 1] = map[map.length - 1] + 1;

        var updatedMap = this.updateMap(depth, map, allFields);
        if (updatedMap) {
          if (variations.length !== total) {
            this.buildVariations(0, updatedMap.newMap, allFields, total, variations);
          }
        } else {
          this.buildVariations(0, map, allFields, total, variations);
        }
      } else {
        variations[lastVariationIndex].SKU = variations[lastVariationIndex].SKU + '-' + fieldOptions.skuSegment;
        variations[lastVariationIndex].price = variations[lastVariationIndex].price + parseFloat(fieldOptions.priceModifier);
        depth++;
        this.buildVariations(depth, map, allFields, total, variations);
      }
    }
  }

  updateMap(depth, map, allFields) {
    if (map[depth - 1] === allFields[depth - 1].options.length) {
      var newMap = map.slice();
      newMap[depth - 1] = 0;
      if (depth !== 0) {
        depth--;
      }
      newMap[depth - 1] = newMap[depth - 1] + 1;
      return { newDepth: depth, newMap: newMap };
    } else {
      return false;
	}
  }

  getTotalVariations(allFields) {
    var total = 1;
    allFields.forEach(function(field, index) {
      total = total * field.options.length;
    });
    return total;
  }

  handleGenerate(event) {
    event.preventDefault();

    var variationData = {
      operation: 'UPDATE_PRODUCT_VARIATIONS',
      product_id: document.getElementById('product-id').value,
      variations: this.state.variations
    };

    axios.get(Drupal.url('rest/session/token'))
      .then(function (response) {
        return response.data;
      })
      .then(function (csrfToken) {
        axios({
          method: 'PATCH',
          url: Drupal.url('commerce_product_option') + '/' + variationData.product_id,
          data: JSON.stringify(variationData),
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          }
        })
        .then(function(response) {
console.log(response);
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
      <div>
        <table id="variations-table">
          <thead>
            <tr>
              <th>Title</th>
              <th>SKU</th>
              <th>Price </th>
            </tr>
           </thead>
          <tbody>
            {this.state.variations.map((variation, index) =>
              <tr>
                <td></td>
                <td>{variation.SKU}</td>
                <td>{variation.price}</td>
              </tr>
            )}
          </tbody>
        </table>
        <button onClick={this.handleGenerate}>Generate</button>
      </div>
    );
  }
}
