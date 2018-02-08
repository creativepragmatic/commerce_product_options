import React, { Component } from 'react';
import { BrowserRouter as Router, Route, Link } from 'react-router-dom';
import { CheckboxForm } from './CheckboxForm';
import { SelectForm } from './SelectForm';
import { TextForm } from './TextForm';
import { OptionSetTable } from './OptionSetTable';

export class OptionsContainer extends Component {

  render() {
    return (
      <div id="options-container">
        <OptionSetTable />
        <Router>
          <div id="option-form-fields">
            <label>Add:&nbsp;</label>
            <ul id="ul-add-fields">
              <li><Link to="/checkbox" activeclassname="selected">Checkbox</Link></li>
              <li><Link to="/select" activeclassname="selected">Select box</Link></li>
              <li><Link to="/text" activeclassname="selected">Text field</Link></li>
            </ul>
            <Route path="/checkbox" component={CheckboxForm}/>
            <Route path="/select" component={SelectForm}/>
            <Route path="/text" component={TextForm}/>
          </div>
        </Router>
      </div>
    );
  }
}
