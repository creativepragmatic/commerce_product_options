import React, { Component } from 'react';
import { BaseForm } from './BaseForm';
import { VariationsTable } from './VariationsTable';

export class BaseVariationsContainer extends Component {

  render() {
    return (
      <div id="base-variations-container">
        <BaseForm />
        <VariationsTable />
      </div>
    );
  }
}
