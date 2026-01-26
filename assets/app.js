/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your front.html.twig.
 */
import './styles/app.scss';
import './styles/app.generated.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';

console.log('AssetMapper + Sass OK');
import './stimulus_bootstrap.js';
console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

