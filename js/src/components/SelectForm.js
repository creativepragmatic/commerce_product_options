import React, { Component } from 'react'

export class SelectForm extends Component {

  constructor(props) {
    super(props);
    this.state = {
      title: '',
      options: [],
      newOption: {
        optionTitle: '',
        isDefault: false,
        skuSegment: '',
        priceModifier: 0
      }
	};

    this.handleInputChange = this.handleInputChange.bind(this);
    this.handleAddOptionChange = this.handleAddOptionChange.bind(this);
    this.handleAddOption = this.handleAddOption.bind(this);
    this.handleClearOption = this.handleClearOption.bind(this);
    this.handleSubmit = this.handleSubmit.bind(this);
    this.handleClearForm = this.handleClearForm.bind(this);
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
    const changedOptions = this.state.options;
    changedOptions.push(this.state.newOption);
    this.setState({
      options: changedOptions
    });
    this.setState({
      newOption: {}
	});
console.log(this.state.options);
  }

  handleClearOption(event) {
    event.preventDefault();
    this.setState({
      newOption: {
        optionTitle: '',
        isDefault: false,
        skuSegment: '',
        priceModifier: 0
      }
	});
  }

  handleSubmit(event) {
    event.preventDefault();
console.log(this.state.options);
  }

  handleClearForm(event) {
    event.preventDefault();
    this.setState({
      title: ''
	});
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
        <label>Options:<br/>
          <ul>
            {this.state.options.map((option, index) =>
              <li key={index}>
                {option.optionTitle}
              </li>
            )}
          </ul>
        </label>
        <div>
          <label>Option title: <span className="required-asterisk">*</span><br/>
            <input
              name="optionTitle"
              type="text"
              required
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
          <button onClick={this.handleAddOption}>Add Option</button>
          <button onClick={this.handleClearOption}>Clear Option</button>
        </div>
        <input type="submit" value="Save" />
        <button onClick={this.handleClearForm}>Clear Form</button>
      </form>
    );
  }
}
