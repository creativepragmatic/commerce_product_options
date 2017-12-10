import React from 'react';
import { BrowserRouter as Router, Route, Link } from 'react-router-dom';
import { BaseForm } from './components/BaseForm';
import { CheckboxForm } from './components/CheckboxForm';
import { SelectForm } from './components/SelectForm';
import { TextForm } from './components/TextForm';
import { OptionSetTable } from './components/OptionSetTable';

export default (
  <div className="option-set-admin">
    <Router>
      <div>
        <ul>
          <li><Link to="/base" activeclassname="selected">Base</Link></li>
          <li><Link to="/checkbox" activeclassname="selected">Checkbox</Link></li>
          <li><Link to="/select" activeclassname="selected">Select box</Link></li>
          <li><Link to="/text" activeclassname="selected">Text field</Link></li>
        </ul>
        <Route path="/base" component={BaseForm}/>
        <Route path="/checkbox" component={CheckboxForm}/>
        <Route path="/select" component={SelectForm}/>
        <Route path="/text" component={TextForm}/>
      </div>
    </Router>
    <OptionSetTable />
  </div>
);
