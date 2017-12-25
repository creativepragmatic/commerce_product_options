import React, { Component } from 'react';

export class CheckboxForm extends Component {

  constructor(props) {
    super(props);
    this.state = {
      checkboxText: '',
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
    event.preventDefault();
console.log(this.state.checkboxText);
console.log(this.state.isRequired);
  }

  handleClear(event) {
    event.preventDefault();
    this.setState({
      checkboxText: '',
      isRequired: false
    });
  }

  render() {
    return (
      <form onSubmit={this.handleSubmit}>
        <label>Checkbox text: <span className="required-asterisk">*</span><br/>
          <textarea
            name="checkboxText"
            rows="4"
            cols="20"
            minLength="1"
            maxLength="250"
            required
            value={this.state.checkboxText}
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
    )
  }
}
