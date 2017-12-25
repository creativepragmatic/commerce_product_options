import React, { Component } from 'react'

export class OptionSetRow extends Component {
  render() {
    return (
      <tr>
        <td>{this.props.type}</td>
        <td>{this.props.title}</td>
        <td></td>
        <td></td>
        <td>{this.props.size}</td>
        <td>{this.props.required}</td>
      </tr>
    );
  }
}
