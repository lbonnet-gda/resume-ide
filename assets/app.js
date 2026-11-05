import { config } from '@hotwired/turbo';
import './stimulus_bootstrap.js';
import './styles/app.css';

// Turbo ignores links ending in .json, but composer.json is a page of this site like any other file.
config.drive.unvisitableExtensions.delete('.json');
