import React, { Component } from 'react'
import { OptionSetRow } from './OptionSetRow'

export class OptionSetTable extends React.Component {
  render() {
    return (
      <table className="option-set-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Default</th>
            <th>Option Title</th>
            <th>SKU Segment</th>
            <th>Price Modifier</th>
            <th>Required</th>
          </tr>
        </thead>
        <tbody>
          <OptionSetRow />
          <OptionSetRow />
        </tbody>
      </table>
    );
  }
}
