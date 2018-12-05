import React, { Component } from 'react';
import { render } from 'react-dom';
import { Provider } from 'react-redux';
import store from './store';
import router from './router';
import './css/admin.css';

render(
  <Provider store={store}>{router}</Provider>,
  document.getElementById('product-options-admin')
);
