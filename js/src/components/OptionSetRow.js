import React, { Component } from 'react'

export class OptionSetRow extends Component {
  render() {
    return (
      <tr className={this.props.rowType}>
        {this.props.rowType === 'field-row' && this.props.type !== 'checkbox' &&
           <td className="field-title" colspan="4">{this.props.title}</td>
        }
        {this.props.rowType === 'field-row' && this.props.type === 'checkbox' &&
           <td colspan="4">{this.props.title}</td>
        }
        {this.props.rowType === 'option-row' &&
           <td>{this.props.title}</td>
        }
        {this.props.rowType === 'option-row' &&
           <td className="option-sku">{this.props.sku}</td>
        }
        {this.props.rowType === 'option-row' &&
           <td className="option-price">{this.props.modifier}</td>
        }
        {this.props.rowType === 'option-row' &&
           <td className="option-default">{this.props.isDefault}</td>
        }
        <td>{this.props.size}</td>
        <td className="field-required">{this.props.required}</td>
        <td>{this.props.type}</td>
      </tr>
    );
  }
}
