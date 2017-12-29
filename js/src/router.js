import React from 'react';
import { BrowserRouter as Router, Route, Link } from 'react-router-dom';
import { BaseVariationsContainer } from './components/BaseVariationsContainer';
import { OptionsContainer } from './components/OptionsContainer';

export default (
  <div className="option-set-admin">
    <Router>
      <div id="router">
        <nav class="tabs">
          <nav class="is-horizontal position-container is-horizontal-enabled">
            <ul class="tabs secondary clearfix">
              <li class="tabs__tab"><Link to="/base-variations" activeclassname="selected">Base & Variations</Link></li>
              <li class="tabs__tab"><Link to="/options" activeclassname="selected">Options</Link></li>
            </ul>
          </nav>
        </nav>
        <Route path="/base-variations" component={BaseVariationsContainer}/>
        <Route path="/options" component={OptionsContainer}/>
      </div>
    </Router>
  </div>
);
