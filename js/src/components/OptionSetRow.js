import React, { Component } from 'react'

export class OptionSetRow extends Component {
  render() {
    if (this.props.rowType === 'field-row' && this.props.type === 'textfield') {
      return (
	    <tr className={this.props.rowType}>
	      <td className="field-title" colspan="2">{this.props.title}</td>
	      <td colspan="2">&nbsp;</td>
	      <td>{this.props.size}</td>
	      <td className="field-required">{this.props.required}</td>
	      <td>{this.props.type}</td>
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
          </tr>
        );
      }
      else {
        return (
	      <tr className={this.props.rowType}>
	        <td colspan="5">{this.props.title}</td>
	        <td className="field-required">{this.props.required}</td>
	        <td>{this.props.type}</td>
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
          <td colspan="2">&nbsp;</td>
        </tr>
      );
    }
  }
}
