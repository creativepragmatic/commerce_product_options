import React, { Component } from 'react'

export class OptionSetRow extends Component {
  render() {
    return (
      <tr className={this.props.rowType}>
        <td>{this.props.title}</td>
        <td>{this.props.sku}</td>
        <td>{this.props.modifier}</td>
        <td>{this.props.isDefault}</td>
        <td>{this.props.size}</td>
        <td>{this.props.required}</td>
        <td>{this.props.type}</td>
      </tr>
    );
  }
}
